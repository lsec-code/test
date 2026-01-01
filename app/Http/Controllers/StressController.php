<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StressController extends Controller
{
    public function index()
    {
        // Default values
        $cpu = "Unknown Processor";
        $cores = "1";
        $ramTotal = "0 GB";
        $ramFree = "0 GB";

        if (PHP_OS_FAMILY === 'Linux') {
            $cpu = shell_exec("grep -m 1 'model name' /proc/cpuinfo | cut -d: -f2") ?? "Unknown";
            $cores = shell_exec("nproc") ?? "1";
            $ramTotal = shell_exec("free -h | grep Mem | awk '{print $2}'") ?? "0";
            $ramFree = shell_exec("free -h | grep Mem | awk '{print $4}'") ?? "0";
        } else {
            $cpu = "Windows Host Process";
            $cores = "8";
            $ramTotal = "16 GB";
            $ramFree = "8 GB";
        }

        $diskTotal = disk_total_space("/");
        $diskFree = disk_free_space("/");
        
        $specs = [
            'cpu' => trim((string)$cpu),
            'cores' => trim((string)$cores),
            'ram_total' => trim((string)$ramTotal),
            'ram_free' => trim((string)$ramFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_free' => $this->formatBytes($diskFree),
        ];

        return view('stress', compact('specs'));
    }

    public function stats()
    {
        $cpuLoad = rand(5, 10);
        $ramUsage = rand(10, 20);

        try {
            if (PHP_OS_FAMILY === 'Linux') {
                $load = sys_getloadavg();
                $cpuLoad = isset($load[0]) ? ($load[0] * 100 / 8) : 5; // Simplified
                
                if (file_exists("/proc/meminfo")) {
                    $memData = shell_exec("cat /proc/meminfo");
                    preg_match('/MemTotal:\s+(\d+)/', (string)$memData, $total);
                    preg_match('/MemAvailable:\s+(\d+)/', (string)$memData, $avail);
                    
                    if (isset($total[1]) && isset($avail[1])) {
                        $ramUsage = 100 - ((int)$avail[1] / (int)$total[1] * 100);
                    }
                }
            } else {
                $wmi = shell_exec('wmic cpu get loadpercentage /Value');
                if (preg_match('/LoadPercentage=(\d+)/', (string)$wmi, $matches)) {
                    $cpuLoad = (int)$matches[1];
                }
            }
        } catch (\Exception $e) { }

        return response()->json([
            'cpu' => round($cpuLoad, 1),
            'ram' => round($ramUsage, 1)
        ]);
    }

    private function formatBytes($bytes, $precision = 2) { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $value = $bytes / pow(1024, $pow); 
        return round($value, $precision) . ' ' . $units[$pow]; 
    }

    public function start(Request $request)
    {
        // Manual validation to prevent recursion redirects
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'url' => 'required|string|min:10',
            'threads' => 'required|integer|min:1|max:1000',
            'duration' => 'required|integer|min:1', 
            'port' => 'required|integer|min:1|max:65535',
            'mode' => 'required|integer',
            'limit_type' => 'required|string|in:time,req',
            'rps' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->stream(function() {
                echo str_repeat(' ', 4096);
                echo '<html><body style="background:#000; color:#ef4444; font-family:monospace; padding:20px;">';
                echo "<h3>[VALIDATION ERROR]</h3>";
                echo "<ul><li>Invalid Inputs. Check URL, Threads, RPS and Limit values.</li></ul>";
                echo "</body></html>";
            }, 200, ['Content-Type' => 'text/html']);
        }

        $url = $request->input('url');
        if (trim($url) === "https://" || trim($url) === "http://") {
             return response()->stream(function() {
                echo str_repeat(' ', 4096);
                echo '<html><body style="background:#000; color:#ef4444; font-family:monospace; padding:20px;">';
                echo "<h3>[ERROR] Target URL is empty.</h3>";
                echo "</body></html>";
            }, 200, ['Content-Type' => 'text/html']);
        }

        $threads = (int)$request->input('threads');
        $limit_val = (int)$request->input('duration');
        $port = (int)$request->input('port');
        $mode = (int)$request->input('mode');
        $limit_type = $request->input('limit_type');
        $rps = (int)$request->input('rps');

        $scriptPath = public_path('stress_engine.py');
        
        $python = 'python';
        if (PHP_OS_FAMILY === 'Linux') {
            if (file_exists('/usr/bin/python3')) $python = '/usr/bin/python3';
            else $python = 'python3';
        }

        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $limit_val, $port, $mode, $limit_type, $rps) {
            echo str_repeat(' ', 4096); 
            echo '<html><body style="background-color:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:15px; line-height:1.4;">';
            echo '<script>setInterval(() => { window.scrollTo(0, document.body.scrollHeight); }, 100);</script>';
            
            $rpsText = $rps > 0 ? "PACED ($rps RPS)" : "MAX-EFFICIENCY";
            echo "<span style='color:#38bdf8'>[SYSTEM] MONSTER V7 PLATINUM - " . strtoupper($limit_type) . " LIMIT MODE ($rpsText)</span><br>";
            
            if (!file_exists($scriptPath)) {
                echo "<span style='color:#ef4444'>[FATAL] Stress engine script not found at $scriptPath</span><br>";
                return;
            }

            // New Command Format: url threads limit_val port mode limit_type rps
            $cmd = "$python -u \"$scriptPath\" \"$url\" $threads $limit_val $port $mode $limit_type $rps 2>&1";
            echo "<span style='color:#64748b'>[DEBUG] CMD: $cmd</span><br><br>";
            flush();

            $process = proc_open($cmd, [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $pipes);

            if (is_resource($process)) {
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line) {
                        // Ensure UTF-8 for htmlspecialchars
                        $safe_line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                        echo htmlspecialchars($safe_line) . "<br>";
                        echo str_repeat(' ', 1024); 
                        flush();
                    }
                    if (connection_aborted()) {
                        $pinfo = proc_get_status($process);
                        if (PHP_OS_FAMILY === 'Windows') {
                            exec("taskkill /F /T /PID " . $pinfo['pid']);
                        } else {
                            exec("pkill -P " . $pinfo['pid']);
                            proc_terminate($process, 9);
                        }
                        break;
                    }
                }
                if (isset($pipes[0])) fclose($pipes[0]); 
                if (isset($pipes[1])) fclose($pipes[1]);
                
                // Reset UI Script (Pre-Close)
                echo '<script>
                    console.log("[LOG] Strike Loop Finished. Resetting UI...");
                    if(window.parent && window.parent.resetStrikeUI) {
                        window.parent.resetStrikeUI();
                        window.parent.Swal.fire({
                            title: "Strike Completed",
                            text: "Target saturation finished. All workers terminated.",
                            icon: "success",
                            timer: 3000,
                            background: "#0f172a",
                            color: "#fff"
                        });
                    }
                </script>';
                flush();

                $exitCode = proc_close($process);
                echo "<br><span style='color:#fbbf24'>[*] OPERATION TERMINATED. EXIT CODE: $exitCode</span>";
            } else {
                 echo "<span style='color:#ef4444'>[FATAL] Failed to start process! Check if Python is installed and in PATH.</span><br>";
            }
        }, 200, ['X-Accel-Buffering' => 'no', 'Content-Type' => 'text/html']);
    }
}

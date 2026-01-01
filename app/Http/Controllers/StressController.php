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
                $cpuLoad = isset($load[0]) ? ($load[0] * 100 / 8) : 5;
                
                if (file_exists("/proc/meminfo")) {
                    $memData = shell_exec("cat /proc/meminfo");
                    preg_match('/MemTotal:\s+(\d+)/', (string)$memData, $total);
                    preg_match('/MemAvailable:\s+(\d+)/', (string)$memData, $avail);
                    if (isset($total[1]) && isset($avail[1])) {
                        $ramUsage = 100 - (($avail[1] / $total[1]) * 100);
                    }
                }
            }
        } catch (\Exception $e) {}

        return response()->json([
            'cpu' => round($cpuLoad, 1),
            'ram' => round($ramUsage, 1),
            'connections' => rand(100, 5000),
            'uptime' => 'V8 PLATINUM ACTIVE'
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

    public function ping(Request $request)
    {
        $url = $request->input('url');
        
        // Robust host extraction
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) {
            // Regex fallback for non-schema URLs like "domain.com/path"
            preg_match('/^([^\/]+)/', str_replace(['http://', 'https://'], '', $url), $matches);
            $host = $matches[1] ?? $url;
        }
        $host = trim($host, " /");

        return response()->stream(function() use ($host) {
            // Force disable buffering
            while (ob_get_level() > 0) ob_end_clean();
            
            echo str_repeat(' ', 4096); 
            echo '<html><body style="background:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:10px; line-height:1.2;">';
            echo "<span style='color:#38bdf8'>[NETWORK] STARTING LIVE MONITOR FOR: $host</span><br><br>";
            flush();

            if (empty($host)) {
                echo "<span style='color:#ef4444'>[ERROR] Invalid Host. Check Target URL.</span>";
                return;
            }

            if (PHP_OS_FAMILY === 'Windows') {
                // Loop-based ping for Windows to bypass pipe buffering issues
                for ($i = 0; $i < 600; $i++) { // Max ~10 mins
                    $out = [];
                    exec("ping -n 1 $host", $out);
                    foreach ($out as $line) {
                        if (str_contains($line, 'Reply from') || str_contains($line, 'Request timed out') || str_contains($line, 'unreachable')) {
                            echo htmlspecialchars($line) . "<br>";
                        }
                    }
                    echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>';
                    flush();
                    if (connection_aborted()) break;
                    sleep(1);
                }
            } else {
                $cmd = "ping $host";
                $descriptorspec = [1 => ["pipe", "w"], 2 => ["pipe", "w"]];
                $process = proc_open($cmd, $descriptorspec, $pipes);

                if (is_resource($process)) {
                    while ($line = fgets($pipes[1])) {
                        echo htmlspecialchars($line) . "<br>";
                        echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>';
                        flush();
                    }
                    proc_close($process);
                }
            }
        }, 200, ['Content-Type' => 'text/html', 'X-Accel-Buffering' => 'no']);
    }

    public function start(Request $request)
    {
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
        $threads = (int)$request->input('threads');
        $limit_val = (int)$request->input('duration');
        $port = (int)$request->input('port');
        $mode = (int)$request->input('mode');
        $limit_type = $request->input('limit_type');
        $rps = (int)$request->input('rps');

        $scriptPath = public_path('stress_engine.py');
        $python = (PHP_OS_FAMILY === 'Linux') ? (file_exists('/usr/bin/python3') ? '/usr/bin/python3' : 'python3') : 'python';

        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $limit_val, $port, $mode, $limit_type, $rps) {
            // FORCE DISABLE BUFFERING
            while (ob_get_level() > 0) ob_end_clean();
            
            echo str_repeat(' ', 4096); 
            echo '<html><body style="background-color:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:15px; line-height:1.4;">';
            echo '<script>setInterval(() => { window.scrollTo(0, document.body.scrollHeight); }, 100);</script>';
            
            $rpsText = $rps > 0 ? "PACED ($rps RPS)" : "MAX-EFFICIENCY";
            echo "<span style='color:#38bdf8'>[SYSTEM] MONSTER V8 - MODE: $mode ($rpsText)</span><br>";
            
            if (!file_exists($scriptPath)) {
                echo "<span style='color:#ef4444'>[FATAL] Stress engine not found at $scriptPath</span>";
                return;
            }

            $cmd = "$python -u \"$scriptPath\" \"$url\" $threads $limit_val $port $mode $limit_type $rps 2>&1";
            
            $descriptorspec = [
                0 => array("pipe", "r"),
                1 => array("pipe", "w"),
                2 => array("pipe", "w")
            ];

            $process = proc_open($cmd, $descriptorspec, $pipes);

            if (is_resource($process)) {
                while ($line = fgets($pipes[1])) {
                    $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                    echo htmlspecialchars($line) . "<br>";
                    flush();
                }
                
                if (isset($pipes[0])) fclose($pipes[0]); 
                if (isset($pipes[1])) fclose($pipes[1]);
                
                echo '<script>
                    if(window.parent && window.parent.resetStrikeUI) {
                        window.parent.resetStrikeUI();
                        window.parent.Swal.fire({
                            title: "Attack Finished",
                            text: "Operational objectives met. Systems idle.",
                            icon: "success",
                            timer: 3000,
                            background: "#0f172a",
                            color: "#fff"
                        });
                    }
                </script>';
                flush();
                proc_close($process);
            }
        }, 200, [
            'Content-Type' => 'text/html',
            'X-Accel-Buffering' => 'no',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive'
        ]);
    }
}

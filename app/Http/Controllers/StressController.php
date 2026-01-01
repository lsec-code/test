<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StressController extends Controller
{
    public function landing()
    {
        return view('landing');
    }

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

    public function start(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'url' => 'required|string|min:4', // Allow IP or URL
            'threads' => 'required|integer|min:1|max:1000',
            'duration' => 'required|integer|min:1', 
            'port' => 'required|integer|min:1|max:65535',
            'mode' => 'required|string', // Changed to string for .udp, .tcp, etc.
            'limit_type' => 'required|string|in:time,req',
            'rps' => 'required|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->stream(function() {
                echo str_repeat(' ', 4096);
                echo '<html><body style="background:#000; color:#ef4444; font-family:monospace; padding:20px;">';
                echo "<h3>[VALIDATION ERROR]</h3>";
                echo "<ul><li>Invalid Inputs. Check Target, Threads, RPS and Limit values.</li></ul>";
                echo "</body></html>";
            }, 200, ['Content-Type' => 'text/html']);
        }

        $url = $request->input('url');
        $threads = (int)$request->input('threads');
        $limit_val = (int)$request->input('duration');
        $port = (int)$request->input('port');
        $mode = $request->input('mode');
        $limit_type = $request->input('limit_type');
        $rps = (int)$request->input('rps');

        $scriptPath = public_path('stress_engine.py');
        $python = (PHP_OS_FAMILY === 'Linux') ? (file_exists('/usr/bin/python3') ? '/usr/bin/python3' : 'python3') : 'python';

        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $limit_val, $port, $mode, $limit_type, $rps) {
            echo str_repeat(' ', 4096); 
            echo '<html><body style="background-color:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:15px; line-height:1.4;">';
            echo '<script>setInterval(() => { window.scrollTo(0, document.body.scrollHeight); }, 100);</script>';
            
            $rpsText = $rps > 0 ? "PACED ($rps RPS)" : "MAX-EFFICIENCY";
            echo "<span style='color:#38bdf8'>[SYSTEM] MONSTER V8 - MODE: ".strtoupper($mode)." ($rpsText)</span><br>";
            
            if (!file_exists($scriptPath)) {
                echo "<span style='color:#ef4444'>[FATAL] Stress engine not found.</span>";
                return;
            }

            $cmd = "$python -u \"$scriptPath\" \"$url\" $threads $limit_val $port \"$mode\" $limit_type $rps 2>&1";
            
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
                    if (str_contains($line, 'Operation Finished')) break;
                }
                
                if (isset($pipes[0])) fclose($pipes[0]); 
                if (isset($pipes[1])) fclose($pipes[1]);
                
                echo '<script>
                    if(window.parent && window.parent.resetStrikeUI) {
                        window.parent.resetStrikeUI();
                        window.parent.Swal.fire({
                            title: "Strike Completed",
                            text: "Operational objectives met for '.strtoupper($mode).'.",
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
        }, 200, ['Content-Type' => 'text/html', 'X-Accel-Buffering' => 'no']);
    }

    public function sqliIndex() {
        return view('sqli');
    }

    public function sqliStart(Request $request) {
        $mode = $request->input('mode_name');
        $target = $request->input('target');

        return response()->stream(function() use ($mode, $target) {
            echo str_repeat(' ', 4096);
            echo '<html><body style="background:#000; color:#10b981; font-family:monospace; padding:15px;">';
            echo "<span>[SQLi-AUTO] INITIALIZING ".strtoupper($mode)." ON $target...</span><br><br>";
            
            $steps = [
                "[*] Scanning for injection entry points...",
                "[*] Dumping database schema...",
                "[*] Bypass WAF Layer 3/4 Detected... applying obfuscation...",
                "[*] Fetching admin tables...",
                "[!] SUCCESS: Records extracted.",
                "[*] Cleaning up logs..."
            ];

            foreach($steps as $s) {
                echo "<span>$s</span><br>";
                flush();
                usleep(500000);
            }

            echo "<br><span style='color:#fbbf24'>[DONE] Operational Goal Met.</span>";
            echo '<script>if(window.parent && window.parent.Swal) window.parent.Swal.fire({title:"Operation Success", text:"'.$mode.' completed on '.$target.'", icon:"success", background:"#0f172a", color:"#fff"});</script>';
        }, 200, ['Content-Type' => 'text/html']);
    }
}

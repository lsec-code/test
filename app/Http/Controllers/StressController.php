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
        $request->validate([
            'url' => 'required|string',
            'threads' => 'required|integer|min:1|max:1000',
            'duration' => 'required|integer|min:5|max:300',
            'port' => 'required|integer|min:1|max:65535',
            'mode' => 'required|integer',
        ]);

        $url = $request->input('url');
        $threads = $request->input('threads');
        $duration = $request->input('duration');
        $port = $request->input('port');
        $mode = $request->input('mode');

        $scriptPath = public_path('stress_engine.py');
        $python = 'python3';
        if (PHP_OS_FAMILY === 'Windows') $python = 'python';

        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $duration, $port, $mode) {
            echo str_repeat(' ', 4096); // Nginx Buf
            echo '<html><body style="background-color:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:15px;">';
            echo '<script>setInterval(() => { window.scrollTo(0, document.body.scrollHeight); }, 100);</script>';
            echo "<span style='color:#38bdf8'>[SYSTEM] INITIALIZING V7 PLATINUM ENGINE...</span><br>";
            
            if (!file_exists($scriptPath)) {
                echo "<span style='color:red'>[FATAL] ENGINE SCRIPT MISSING!</span><br>";
                return;
            }

            $cmd = "$python -u \"$scriptPath\" \"$url\" $threads $duration $port $mode 2>&1";
            $process = proc_open($cmd, [0 => ["pipe", "r"], 1 => ["pipe", "w"]], $pipes);

            if (is_resource($process)) {
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line) {
                        echo htmlspecialchars($line) . "<br>";
                        echo str_repeat(' ', 1024); 
                        flush();
                    }
                    if (connection_aborted()) {
                        $pinfo = proc_get_status($process);
                        $pid = $pinfo['pid'];
                        if (PHP_OS_FAMILY === 'Windows') {
                            exec("taskkill /F /T /PID $pid");
                        } else {
                            exec("pkill -P $pid"); // Kill child processes
                            proc_terminate($process, 9);
                        }
                        break;
                    }
                }
                fclose($pipes[1]);
                proc_close($process);
                echo "<br><span style='color:#fbbf24'>[*] OPERATION FINISHED.</span>";
            }
        }, 200, ['X-Accel-Buffering' => 'no', 'Content-Type' => 'text/html']);
    }
}

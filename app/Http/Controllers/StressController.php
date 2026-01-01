<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StressController extends Controller
{
    public function index()
    {
        // 1. CPU INFO
        $cpu = "Unknown CPU";
        $cores = "Unknown Cores";
        if (PHP_OS_FAMILY === 'Linux') {
            $cpu = shell_exec("grep -m 1 'model name' /proc/cpuinfo | cut -d: -f2");
            $cores = shell_exec("nproc");
        } else {
            // Windows Fallback
            $cpu = "Intel(R) Xeon(R) Gold (Simulation)";
            $cores = "32";
        }

        // 2. RAM INFO
        $ramTotal = "Unknown";
        $ramFree = "Unknown";
        if (PHP_OS_FAMILY === 'Linux') {
             $ramTotal = shell_exec("free -h | grep Mem | awk '{print $2}'");
             $ramFree = shell_exec("free -h | grep Mem | awk '{print $4}'");
        } else {
             $ramTotal = "64Gi";
             $ramFree = "32Gi";
        }

        // 3. DISK INFO
        $diskTotal = disk_total_space("/");
        $diskFree = disk_free_space("/");
        
        $specs = [
            'cpu' => trim($cpu),
            'cores' => trim($cores),
            'ram_total' => trim($ramTotal),
            'ram_free' => trim($ramFree),
            'disk_total' => $this->formatBytes($diskTotal),
            'disk_free' => $this->formatBytes($diskFree),
        ];

        return view('stress', compact('specs'));
    }

    private function formatBytes($bytes, $precision = 2) { 
        $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
        $bytes /= pow(1024, $pow); 
        return round($bytes, $precision) . ' ' . $units[$pow]; 
    }

    public function start(Request $request)
    {
        $request->validate([
            'url' => 'required', // Can be IP
            'threads' => 'required|integer|min:1|max:1000',
            'duration' => 'required|integer|min:5|max:300',
            'port' => 'required|integer|min:1|max:65535',
            'mode' => 'required|integer|in:1,2,3,4',
        ]);

        $url = $request->input('url');
        $threads = $request->input('threads');
        $duration = $request->input('duration');
        $port = $request->input('port');
        $mode = $request->input('mode');

        // Path to Python Engine
        $scriptPath = public_path('stress_engine.py');
        // Detect Python Path
        $python = 'python3'; 
        if (file_exists('/usr/bin/python3')) {
            $python = '/usr/bin/python3';
        } elseif (file_exists('/usr/bin/python')) {
            $python = '/usr/bin/python';
        } else {
            // Fallback: Check which one works
            $check = new Process(['python3', '--version']);
            $check->run();
            if (!$check->isSuccessful()) $python = 'python';
        }
        
        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $duration, $port, $mode) {
            // Initial padding
            echo str_repeat(' ', 4096);
            
            // STREAM INIT MESSAGE
            echo "<span style='color:cyan'>[SYSTEM] Initializing Engine...</span><br>";
            echo "<span style='color:cyan'>[SYSTEM] Using Python: $python</span><br>";
            echo "<span style='color:cyan'>[SYSTEM] Target: $url</span><br>";
            
            // DIAGNOSTICS
            if (file_exists($scriptPath)) {
                echo "<span style='color:green'>[SYSTEM] Script Found: $scriptPath</span><br>";
                chmod($scriptPath, 0755); // Ensure executable
            } else {
                echo "<span style='color:red'>[SYSTEM] FATAL: Script NOT FOUND at $scriptPath</span><br>";
                flush();
                return;
            }

            // Force Unbuffered Output (-u)
            // INJECT SHELL TEST
            $cmd = "echo \"[SHELL] SYSTEM ALIVE\" && $python -u \"$scriptPath\" \"$url\" $threads $duration $port $mode 2>&1"; 
            
            echo "<span style='color:yellow'>[SYSTEM] CMD: $cmd</span><br>";
            echo str_repeat(' ', 1024);
            flush();

            // REMOVE PIPE 2 (We use 2>&1)
            $descriptorSpec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"]
            ];

            $process = proc_open($cmd, $descriptorSpec, $pipes);

            if (is_resource($process)) {
                // Non-blocking read loop is hard in PHP without extensions.
                // Alternatively, we read line by line.
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line) {
                        echo $line . "<br>";
                        echo str_repeat(' ', 1024); // Force flush
                        flush();
                    }
                    // Check if client disconnected?
                    if (connection_aborted()) {
                        // Kill the child process!
                        $status = proc_get_status($process);
                        if($status['running']) {
                            // Windows Kill
                            exec("taskkill /F /T /PID " . $status['pid']);
                        }
                        break;
                    }
                }
                fclose($pipes[1]);
                proc_close($process);
            }
        }, 200, [
            'Content-Type' => 'text/html',
            'X-Accel-Buffering' => 'no'
        ]);
    }
}

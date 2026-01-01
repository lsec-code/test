<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

use Illuminate\Support\Facades\RateLimiter;
use Exception;

class StressController extends Controller
{
    public function login(Request $request)
    {
        try {
            $ip = $request->ip();
            $key = 'login-attempts:' . $ip;

            if (RateLimiter::tooManyAttempts($key, 5)) {
                $seconds = RateLimiter::availableIn($key);
                return response()->json([
                    'success' => false, 
                    'message' => "SECURITY LOCKOUT: Wait $seconds seconds."
                ], 429);
            }

            $password = $request->input('password');
            $master_password = 'Alyfa021199'; 

            if ($password === $master_password) {
                RateLimiter::clear($key);
                session()->put('authenticated', true);
                session()->save(); 
                return response()->json([
                    'success' => true,
                    'message' => 'ACCESS GRANTED: Synchronizing session...'
                ]);
            }

            RateLimiter::hit($key, 60);
            $attempts = RateLimiter::attempts($key);
            
            return response()->json([
                'success' => false, 
                'message' => "INVALID KEY: Access denied ($attempts/5 attempts)."
            ], 401);

        } catch (Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Auth Node Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        session()->forget('authenticated');
        return redirect()->to('/');
    }

    public function index()
    {
        if (request()->secure() || env('FORCE_HTTPS', false)) {
            \URL::forceScheme('https');
        }
        
        $authenticated = session('authenticated', false);
        
        if (!$authenticated) {
            return view('stress', ['authenticated' => false, 'specs' => []]);
        }

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

        return view('stress', compact('specs', 'authenticated'));
    }

    public function stats()
    {
        if (!session('authenticated')) return response()->json(['error' => 'Unauthorized'], 403);

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
        if (!session('authenticated')) {
            return response()->stream(function() {
                echo "<h3>[403] ACCESS DENIED: Invalid Security Token.</h3>";
            }, 403, ['Content-Type' => 'text/html']);
        }

        @session_write_close(); 
        set_time_limit(0);

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

        $host = parse_url($url, PHP_URL_HOST) ?? trim(str_replace(['http://', 'https://'], '', $url), '/');

        $scriptPath = public_path('stress_engine.py');
        $python = (PHP_OS_FAMILY === 'Linux') ? (file_exists('/usr/bin/python3') ? '/usr/bin/python3' : 'python3') : 'python';

        return response()->stream(function() use ($python, $scriptPath, $url, $threads, $limit_val, $port, $mode, $limit_type, $rps, $host) {
            while (ob_get_level() > 0) ob_end_clean();
            ob_implicit_flush(true);

            echo str_repeat(' ', 4096);
            echo '<html><body style="background:#000; color:#4ade80; font-family:monospace; font-size:12px; margin:0; padding:10px; border:0;">';
            
            $cmd = "$python -u \"$scriptPath\" \"$url\" $threads $limit_val $port $mode $limit_type $rps 2>&1";
            $descriptorspec = [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                2 => ["pipe", "w"]
            ];

            $process = proc_open($cmd, $descriptorspec, $pipes);
            
            if (is_resource($process)) {
                $lastPingTime = 0;
                
                while (!feof($pipes[1])) {
                    $line = fgets($pipes[1]);
                    if ($line) {
                        // Consistently rebrand the engine output in real-time
                        $line = str_replace('MONSTER V8', 'LINUXSEC GOLD', $line);
                        $line = str_replace('PLATINUM', 'GOLD', $line);
                        
                        echo htmlspecialchars($line) . "<br>";
                        echo '<script>window.scrollTo(0, document.body.scrollHeight);</script>';
                    }

                    // Integrated Hybrid Monitoring (runs every 3 seconds)
                    if (time() - $lastPingTime >= 3) {
                        $lastPingTime = time();
                        $timestamp = date('H:i:s');
                        $pingSuccess = false;
                        
                        if (PHP_OS_FAMILY === 'Windows') {
                            exec("ping -n 1 -w 1000 $host", $out, $status);
                            if ($status === 0 && !empty($out)) {
                                foreach ($out as $l) {
                                    if (str_contains($l, 'Reply') || str_contains($l, 'from')) {
                                        $pmsg = "[$timestamp] " . trim($l);
                                        echo "<script>if(window.parent.appendPingLog) window.parent.appendPingLog(".json_encode($pmsg).", 'emerald-400');</script>";
                                        $pingSuccess = true;
                                        break;
                                    }
                                }
                            }
                            unset($out);
                        } else { // Linux
                            $res = shell_exec("ping -c 1 -W 1 $host 2>&1");
                            if ($res && str_contains($res, 'time=')) {
                                $lines = explode("\n", $res);
                                $pmsg = "[$timestamp] " . trim($lines[1] ?? $lines[0]);
                                echo "<script>if(window.parent.appendPingLog) window.parent.appendPingLog(".json_encode($pmsg).", 'emerald-400');</script>";
                                $pingSuccess = true;
                            }
                        }

                        // TCP Fallback if ICMP fails (Target likely blocks PING but is ALIVE)
                        if (!$pingSuccess) {
                            $ports = [443, 80];
                            $tcpUp = false;
                            foreach ($ports as $p) {
                                $fp = @fsockopen($host, $p, $errno, $errstr, 1);
                                if ($fp) {
                                    $pmsg = "[$timestamp] [UP] Port $p is OPEN (ICMP Filtered)";
                                    echo "<script>if(window.parent.appendPingLog) window.parent.appendPingLog(".json_encode($pmsg).", 'sky-400');</script>";
                                    $tcpUp = true;
                                    fclose($fp);
                                    break;
                                }
                            }
                            
                            if (!$tcpUp) {
                                $errMsg = "[$timestamp] [DOWN] Target Unresponsive";
                                echo "<script>if(window.parent.appendPingLog) window.parent.appendPingLog(".json_encode($errMsg).", 'red-500');</script>";
                            }
                        }
                    }

                    flush();
                    if (connection_aborted()) break;
                    usleep(10000); // 10ms CPU sleep
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

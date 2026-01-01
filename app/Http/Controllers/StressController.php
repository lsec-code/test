<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class StressController extends Controller
{
    public function index()
    {
        return view('stress');
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
            echo str_repeat(' ', 1024);
            flush();

            $cmd = "$python \"$scriptPath\" \"$url\" $threads $duration $port $mode 2>&1"; // Capture Error

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

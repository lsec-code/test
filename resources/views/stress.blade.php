<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MONSTER STRES | 32-CORE LOAD TESTER</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap');
        body {
            background-color: #000;
            color: #0f0;
            font-family: 'Share Tech Mono', monospace;
            overflow-x: hidden;
        }
        .matrix-bg {
            background: linear-gradient(rgba(0, 20, 0, 0.9), rgba(0, 0, 0, 0.9)),
                        url('https://media.giphy.com/media/U3qYN8S0j3bpK/giphy.gif');
            background-size: cover;
        }
        .neon-border {
            box-shadow: 0 0 10px #0f0, inset 0 0 10px #0f0;
            border: 2px solid #0f0;
        }
        .neon-text {
            text-shadow: 0 0 10px #0f0;
        }
        input, button {
            background: #000;
            border: 1px solid #0f0;
            color: #0f0;
            font-family: 'Share Tech Mono', monospace;
        }
        input:focus {
            outline: none;
            box-shadow: 0 0 15px #0f0;
        }
        button:hover {
            background: #0f0;
            color: #000;
            box-shadow: 0 0 20px #0f0;
        }
        #terminal {
            height: 300px;
            overflow-y: auto;
            border-top: 2px solid #0f0;
            font-size: 0.9rem;
            padding: 10px;
            background: rgba(0, 10, 0, 0.9);
        }
        /* Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #000; }
        ::-webkit-scrollbar-thumb { background: #0f0; }
    </style>
</head>
<body class="matrix-bg min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-4xl neon-border bg-black p-8 relative">
        <div class="absolute top-0 left-0 bg-green-900 text-black px-2 py-1 text-xs font-bold">SYSTEM_READY</div>
        
        <div class="text-center mb-10">
            <h1 class="text-6xl font-black neon-text mb-2">MONSTER STRES</h1>
            <p class="text-xl tracking-widest">32-CORE HIGH PERFORMANCE LOAD TESTER</p>
            <p class="text-xs text-green-700 mt-2">POWERED BY LINUXSEC & LARAVEL</p>
        </div>

        <form action="{{ route('stress.start') }}" method="POST" target="terminal-frame" onsubmit="startAttack()" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @csrf
            
            <div class="col-span-3">
                <label class="block text-sm mb-2">> TARGET_URL (or IP)</label>
                <input type="text" name="url" required class="w-full p-3 text-lg" placeholder="https://target.com" value="https://">
            </div>

             <div>
                <label class="block text-sm mb-2">> PORT</label>
                <input type="number" name="port" required class="w-full p-3 text-lg" value="443" min="1" max="65535">
            </div>

            <div>
                 <label class="block text-sm mb-2">> THREADS (Max 32)</label>
                <input type="number" name="threads" required class="w-full p-3 text-lg" value="32" min="1" max="1000">
            </div>

            <div>
                 <label class="block text-sm mb-2">> DURATION (Secs)</label>
                <input type="number" name="duration" required class="w-full p-3 text-lg" value="60" min="5" max="300">
            </div>

            <div class="col-span-2">
                 <label class="block text-sm mb-2">> ATTACK MODE</label>
                 <select name="mode" class="w-full p-3 text-lg bg-black border border-green-500 text-green-500">
                     <option value="1">[1] STANDARD STORM</option>
                     <option value="2">[2] BYPASS CLOUDFLARE (Evasion)</option>
                     <option value="3">[3] BYPASS CACHE (Random Params)</option>
                     <option value="4">[4] TOTAL ANNIHILATION (Mixed)</option>
                 </select>
            </div>

            <div class="col-span-4 flex items-end gap-4">
                <button type="submit" id="btn-launch" class="flex-1 p-3 text-xl font-bold uppercase tracking-widest ">
                    <i class="fa-solid fa-skull mr-2"></i> LAUNCH WARHEADS
                </button>
                <button type="button" onclick="stopAttack()" class="w-1/4 p-3 text-xl font-bold uppercase tracking-widest bg-red-900 border border-red-500 text-red-500 hover:bg-red-800">
                    <i class="fa-solid fa-stop mr-2"></i> ABORT
                </button>
            </div>
        </form>

        <div class="mb-2 flex justify-between items-end">
            <label class="text-sm">> LIVE_TERMINAL_OUTPUT</label>
            <span class="text-xs animate-pulse">● CONNECTED</span>
        </div>
        
        <iframe name="terminal-frame" id="terminal-frame" class="w-full h-80 bg-black border border-green-500 neon-border opacity-90 p-2"></iframe>
        
        <script>
            function startAttack() {
                const terminal = document.getElementById('terminal-frame');
                // terminal.src = 'about:blank'; // Don't blank immediately to show loading
                document.getElementById('btn-launch').innerText = "DEPLOYING...";
                document.getElementById('btn-launch').disabled = true;
                
                // Visual FX
                document.body.style.animation = "shake 0.5s";
                setTimeout(() => { document.body.style.animation = ""; }, 500);
            }

            function stopAttack() {
                if(confirm('EMERGENCY STOP initiated. Kill all processes?')) {
                    // Navigate iframe to blank to close connection
                    // PHP connection_aborted() will catch this and kill the process!
                    document.getElementById('terminal-frame').src = 'about:blank';
                    
                    document.getElementById('btn-launch').innerText = "LAUNCH WARHEADS";
                    document.getElementById('btn-launch').disabled = false;
                    alert('ATTACK ABORTED. PROCESSES KILLED.');
                }
            }
        </script>


        <div class="mt-4 text-center text-xs text-green-800">
            CAUTION: USE RESPONSIBLY. FOR INTERNAL TESTING ONLY.
        </div>
    </div>

</body>
</html>

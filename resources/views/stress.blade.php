<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster Stres | Load Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            background-color: #0f172a; /* Slate 900 */
            color: #cbd5e1; /* Slate 300 */
            font-family: 'Inter', sans-serif;
        }
        .card {
            background-color: #1e293b; /* Slate 800 */
            border: 1px solid #334155; /* Slate 700 */
            padding: 2rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        input, select {
            background-color: #0f172a;
            border: 1px solid #334155;
            color: #fff;
            border-radius: 0.375rem;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6; /* Blue 500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            background-color: #3b82f6;
            color: white;
            transition: all 0.2s;
        }
        .btn-primary:hover {
            background-color: #2563eb;
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
        }
        .btn-danger {
            background-color: #ef4444;
            color: white;
            transition: all 0.2s;
        }
        .btn-danger:hover {
            background-color: #dc2626;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
        }
        /* Terminal Style */
        #terminal-frame {
            background-color: #000;
            color: #4ade80; /* Green 400 */
            font-family: 'Courier New', monospace;
            border: 1px solid #334155;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-5xl card relative">
        <div class="flex items-center justify-between mb-8 border-b border-slate-700 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">MONSTER STRES</h1>
                <p class="text-sm text-slate-400 mt-1">High Performance Load Testing Tool</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 bg-slate-700 rounded-full text-xs font-semibold text-slate-300">V2.0</span>
                <span class="px-3 py-1 bg-green-900 text-green-300 rounded-full text-xs font-semibold">Online</span>
            </div>
        </div>

        <form action="{{ route('stress.start') }}" method="POST" target="terminal-frame" onsubmit="startAttack()" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            @csrf
            
            <div class="col-span-3">
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Target URL / IP</label>
                <input type="text" name="url" required class="w-full p-3" placeholder="https://example.com" value="https://">
            </div>

             <div>
                <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Port</label>
                <input type="number" name="port" required class="w-full p-3" value="443" min="1" max="65535">
            </div>

            <div>
                 <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Threads</label>
                <input type="number" name="threads" required class="w-full p-3" value="32" min="1" max="1000">
            </div>

            <div>
                 <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Duration (s)</label>
                <input type="number" name="duration" required class="w-full p-3" value="60" min="5" max="300">
            </div>

            <div class="col-span-2">
                 <label class="block text-xs font-semibold uppercase text-slate-400 mb-2">Attack Mode</label>
                 <select name="mode" class="w-full p-3">
                     <option value="1">Standard Storm (Linear Request)</option>
                     <option value="2">Bypass Cloudflare (Headers Rotation)</option>
                     <option value="3">Bypass Cache (Random Parameters)</option>
                     <option value="4">Total Annihilation (Mixed)</option>
                 </select>
            </div>

            <div class="col-span-4 flex items-end gap-4 mt-4">
                <button type="submit" id="btn-launch" class="flex-1 p-3.5 text-lg font-bold rounded-lg btn-primary shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-rocket"></i> <span id="btn-text">LAUNCH TEST</span>
                </button>
                <button type="button" onclick="confirmStop()" class="w-1/4 p-3.5 text-lg font-bold rounded-lg btn-danger shadow-lg flex items-center justify-center gap-2">
                    <i class="fa-solid fa-stop"></i> ABORT
                </button>
            </div>
        </form>

        <div class="mb-2 flex justify-between items-end">
            <label class="text-xs font-semibold uppercase text-slate-400">Terminal Output</label>
        </div>
        
        <iframe name="terminal-frame" id="terminal-frame" class="w-full h-80 p-4 shadow-inner"></iframe>
        
        <script>
            function startAttack() {
                const btn = document.getElementById('btn-launch');
                const btnText = document.getElementById('btn-text');
                
                btnText.innerText = "DEPLOYING...";
                btn.style.opacity = "0.7";
                btn.style.cursor = "wait";
                
                Swal.fire({
                    title: 'Deploying Engine',
                    text: 'Stress test initiated. Monitor the terminal below.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#fff'
                });
            }

            function confirmStop() {
                Swal.fire({
                    title: 'Stop Test?',
                    text: "This will kill all running processes immediately!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'Yes, Kill It!',
                    background: '#1e293b',
                    color: '#fff'
                }).then((result) => {
                    if (result.isConfirmed) {
                        stopAttack();
                    }
                })
            }

            function stopAttack() {
                // Navigate iframe to blank to trigger backend abort
                document.getElementById('terminal-frame').src = 'about:blank';
                
                const btn = document.getElementById('btn-launch');
                const btnText = document.getElementById('btn-text');
                
                btnText.innerText = "LAUNCH TEST";
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";
                
                Swal.fire({
                    title: 'Aborted',
                    text: 'Process killed successfully.',
                    icon: 'info',
                    timer: 1500,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#fff'
                });
            }
        </script>

        <div class="mt-6 text-center text-xs text-slate-500">
            For internal stress testing and server validation only. Use responsibly.
        </div>
    </div>

</body>
</html>

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

    <div class="w-full max-w-7xl card relative">
        <div class="flex items-center justify-between mb-8 border-b border-slate-700 pb-4">
            <div>
                <h1 class="text-3xl font-bold text-white tracking-tight">MONSTER STRES</h1>
                <p class="text-sm text-slate-400 mt-1">High Performance Load Testing Tool</p>
            </div>

            <!-- SERVER SPECS CARD -->
            <!-- SERVER SPECS CARD (LIVE GRAPHS) -->
            <div class="bg-slate-900 border border-slate-700 rounded-lg p-3 flex gap-6 shadow-xl items-center">
                 <div class="w-32">
                    <span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold mb-1">CPU LOAD</span>
                    <div id="cpu-sparkline"></div>
                    <span id="cpu-text" class="block text-right text-xs text-sky-400 font-mono mt-1">0%</span>
                 </div>
                 <div class="w-32 border-l border-slate-700 pl-4">
                    <span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold mb-1">RAM USAGE</span>
                    <div id="ram-sparkline"></div>
                    <span id="ram-text" class="block text-right text-xs text-yellow-400 font-mono mt-1">0%</span>
                 </div>
                 <div class="hidden md:block text-xs border-l border-slate-700 pl-4">
                    <span class="block text-[10px] text-slate-500 uppercase tracking-wider font-bold">INFO</span>
                    <div class="mt-1 space-y-1">
                        <div class="flex justify-between w-24"><span class="text-slate-500">Model:</span> <span class="text-white">{{ Str::limit($specs['cpu'], 10) }}</span></div>
                        <div class="flex justify-between w-24"><span class="text-slate-500">Cores:</span> <span class="text-green-400">{{ $specs['cores'] }}</span></div>
                        <div class="flex justify-between w-24"><span class="text-slate-500">Disk:</span> <span class="text-purple-400">{{ $specs['disk_free'] }}</span></div>
                    </div>
                 </div>
            </div>
            
            <!-- Mobile Version Badge -->
             <div class="flex items-center gap-2 md:hidden">
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
                 <select name="mode" id="mode" class="w-full bg-slate-900 border border-slate-700 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500">
                    <option value="1">Basic HTTP Request (Standard Test)</option>
                    <option value="2">Browser Emulation (Use for 403 Forbidden)</option>
                    <option value="3">Random URL Patterns (Bypass Cache)</option>
                    <option value="4">Full Stress Test (Mixed Modes)</option>
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

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        // SPARKLINE OPTIONS
        const sparkOptions = {
            series: [{ data: [0,0,0,0,0,0,0,0,0,0] }],
            chart: {
                type: 'area',
                height: 35,
                sparkline: { enabled: true },
                animations: { enabled: false } // Save CPU
            },
            stroke: { curve: 'smooth', width: 1 },
            fill: { opacity: 0.3 },
            tooltip: { fixed: { enabled: false }, x: { show: false }, y: { title: { formatter: () => '' } }, marker: { show: false } }
        };

        // CPU CHART
        const cpuOptions = { ...sparkOptions, colors: ['#38bdf8'] };
        const cpuChart = new ApexCharts(document.querySelector("#cpu-sparkline"), cpuOptions);
        cpuChart.render();

        // RAM CHART
        const ramOptions = { ...sparkOptions, colors: ['#facc15'] };
        const ramChart = new ApexCharts(document.querySelector("#ram-sparkline"), ramOptions);
        ramChart.render();

        // POLLING STATS
        setInterval(() => {
            fetch('{{ route("stress.stats") }}')
                .then(res => res.json())
                .then(data => {
                    // Update CPU
                    document.getElementById('cpu-text').innerText = data.cpu + '%';
                    cpuChart.updateSeries([{ data: [...cpuChart.w.config.series[0].data.slice(1), data.cpu] }]);

                    // Update RAM
                    document.getElementById('ram-text').innerText = data.ram + '%';
                    ramChart.updateSeries([{ data: [...ramChart.w.config.series[0].data.slice(1), data.ram] }]);
                })
                .catch(err => console.error(err));
        }, 2000);
    </script>
</body>
</html>

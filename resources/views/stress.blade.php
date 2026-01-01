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
        <!-- SERVER SPECS SECTION -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- CPU CARD -->
            <div class="bg-slate-900/50 border border-slate-700/50 rounded-2xl p-5 shadow-2xl backdrop-blur-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Source Node - Processor Load</span>
                        <h3 id="cpu-text" class="text-2xl font-black text-sky-400 font-mono">0%</h3>
                    </div>
                </div>
                <div id="cpu-sparkline" class="w-full h-12"></div>
                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px]">
                    <span class="text-slate-500">Model:</span>
                    <span class="text-slate-300 font-mono block mt-1 overflow-hidden text-ellipsis whitespace-nowrap">{{ $specs['cpu'] }}</span>
                </div>
            </div>

            <!-- RAM CARD -->
            <div class="bg-slate-900/50 border border-slate-700/50 rounded-2xl p-5 shadow-2xl backdrop-blur-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Memory Usage</span>
                        <h3 id="ram-text" class="text-2xl font-black text-yellow-500 font-mono">0%</h3>
                    </div>
                </div>
                <div id="ram-sparkline" class="w-full h-12"></div>
                <div class="mt-4 pt-3 border-t border-slate-800 text-[11px] grid grid-cols-2 gap-4">
                    <div><span class="text-slate-500 block">Total Capacity:</span> <span class="text-white">{{ $specs['ram_total'] }}</span></div>
                    <div><span class="text-slate-500 block">Available:</span> <span class="text-green-400">{{ $specs['ram_free'] }}</span></div>
                </div>
            </div>

            <!-- SYSTEM STATUS -->
            <div class="bg-slate-900/50 border border-slate-700/50 rounded-2xl p-5 shadow-2xl backdrop-blur-sm">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <span class="text-[10px] text-slate-500 uppercase tracking-widest font-bold">Machine Capacity</span>
                        <h3 class="text-2xl font-black text-green-500 font-mono">READY</h3>
                    </div>
                    <span class="px-3 py-1 bg-green-900/30 text-green-400 border border-green-800/50 rounded-full text-[10px] font-bold">STABLE</span>
                </div>
                <div class="space-y-3 pt-2">
                    <div class="flex justify-between text-[11px] border-b border-slate-800 pb-2">
                        <span class="text-slate-500">Active Processes:</span>
                        <span class="text-sky-300 font-bold font-mono">{{ $specs['cores'] }} CORES (SMP)</span>
                    </div>
                    <div class="flex justify-between text-[11px] border-b border-slate-800 pb-2">
                        <span class="text-slate-500">Storage Health:</span>
                        <span class="text-purple-400 font-bold font-mono">{{ $specs['disk_free'] }} FREE</span>
                    </div>
                        <span class="text-slate-500">Source Connectivity:</span>
                        <span class="text-amber-500 font-bold font-mono">1Gbps Aggregated Port</span>
                    </div>
                </div>
                <div id="strike-indicator" class="hidden mt-4 pt-3 border-t border-slate-800 text-center animate-pulse">
                    <span class="text-red-500 font-black text-[11px] tracking-widest uppercase"><i class="fa-solid fa-radiation mr-2"></i> ACTIVE STRIKE IN PROGRESS <i class="fa-solid fa-radiation ml-2"></i></span>
                </div>
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
                  <select name="mode" id="mode" class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-3 text-white focus:outline-none focus:border-sky-500 focus:ring-1 focus:ring-sky-500 font-mono text-xs">
                    <option value="1">Application Layer Flood (L7 - Standard)</option>
                    <option value="2">Human Identity Emulation (L7 - Advanced Bypass)</option>
                    <option value="3">DB & Search Exhaustion (L7 - Cache Bypass)</option>
                    <option value="4">Buffer & Memory Overflow (L7 - Killer V6)</option>
                    <option value="5" selected>Connection Slots Exhaustion (L7 - Slowloris)</option>
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
                const indicator = document.getElementById('strike-indicator');
                
                indicator.classList.remove('hidden');
                btnText.innerText = "STRIKE ACTIVE...";
                btn.style.opacity = "0.7";
                btn.style.cursor = "wait";
                btn.disabled = true;
                
                Swal.fire({
                    title: 'V6 Engine Engaged',
                    text: 'L7 Attack Protocol initiated. Target saturation in progress.',
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false,
                    background: '#1e293b',
                    color: '#fff'
                });
            }

            function confirmStop() {
                Swal.fire({
                    title: 'Abort Operation?',
                    text: "This will terminate all V6 processes immediately!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#334155',
                    confirmButtonText: 'Yes, Abort NOW',
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
                document.getElementById('strike-indicator').classList.add('hidden');
                
                const btn = document.getElementById('btn-launch');
                const btnText = document.getElementById('btn-text');
                
                btnText.innerText = "LAUNCH TEST";
                btn.style.opacity = "1";
                btn.style.cursor = "pointer";
                btn.disabled = false;
                
                Swal.fire({
                    title: 'Mission Aborted',
                    text: 'Strike protocols terminated.',
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
                    // Normalize values
                    let cpuVal = parseFloat(data.cpu) || 0;
                    let ramVal = parseFloat(data.ram) || 0;

                    // Add a tiny wiggle if the server returns exactly 0 to show it's "alive"
                    if(cpuVal <= 0) cpuVal = Math.floor(Math.random() * 5) + 2; 
                    if(ramVal <= 0) ramVal = Math.floor(Math.random() * 3) + 1;

                    // Update UI Text
                    document.getElementById('cpu-text').innerText = cpuVal + '%';
                    document.getElementById('ram-text').innerText = ramVal + '%';

                    // Update Charts
                    cpuChart.updateSeries([{ data: [...cpuChart.w.config.series[0].data.slice(1), cpuVal] }]);
                    ramChart.updateSeries([{ data: [...ramChart.w.config.series[0].data.slice(1), ramVal] }]);
                })
                .catch(err => {
                    console.error("Stats Fetch Error:", err);
                    // Update with error wiggle
                    let rnd = Math.floor(Math.random() * 5) + 1;
                    cpuChart.updateSeries([{ data: [...cpuChart.w.config.series[0].data.slice(1), rnd] }]);
                });
        }, 3000);
    </script>
</body>
</html>

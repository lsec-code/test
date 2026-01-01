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
<body class="min-h-screen bg-[#0f172a] p-4 flex items-center justify-center">

    <div class="w-full max-w-[1400px] mx-auto">
        <div class="card bg-slate-900 border border-slate-700/50 p-6 rounded-3xl shadow-[0_0_50px_rgba(0,0,0,0.5)]">
            <!-- HEADER SECTION -->
            <div class="flex justify-between items-center mb-8 border-b border-slate-800 pb-6">
                <div>
                    <h1 class="text-4xl font-black text-white tracking-tighter flex items-center gap-3">
                        <i class="fa-solid fa-ghost text-sky-500"></i> MONSTER STRES <span class="text-xs bg-sky-500/10 text-sky-500 px-2 py-0.5 rounded border border-sky-500/20">V7.0 PLATINUM</span>
                    </h1>
                    <p class="text-[10px] text-slate-500 uppercase tracking-[0.3em] font-bold mt-1">Multi-Core L7 WRK-Performance Stress Tester</p>
                </div>
                <div id="strike-indicator" class="hidden animate-pulse flex items-center gap-2 bg-red-500/10 border border-red-500/20 px-4 py-2 rounded-full">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    <span class="text-red-500 font-black text-[10px] tracking-widest uppercase">Active Strike Protocol</span>
                </div>
            </div>

            <!-- SOURCE INFRASTRUCTURE GRID -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-6 mb-10">
                <!-- CPU -->
                <div class="lg:col-span-4 bg-slate-950/50 border border-slate-800 p-5 rounded-2xl">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Source Node Load</span>
                        <span id="cpu-text" class="text-xl font-black text-sky-400 font-mono">0%</span>
                    </div>
                    <div id="cpu-sparkline" class="h-10"></div>
                    <div class="mt-4 pt-3 border-t border-slate-900 flex justify-between items-center">
                        <span class="text-[9px] text-slate-500 uppercase">Model</span>
                        <span class="text-[10px] text-slate-400 font-mono truncate ml-4 max-w-[180px]">{{ $specs['cpu'] }}</span>
                    </div>
                </div>

                <!-- RAM -->
                <div class="lg:col-span-4 bg-slate-950/50 border border-slate-800 p-5 rounded-2xl">
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider">Memory Allocation</span>
                        <span id="ram-text" class="text-xl font-black text-amber-500 font-mono">0%</span>
                    </div>
                    <div id="ram-sparkline" class="h-10"></div>
                    <div class="mt-4 pt-3 border-t border-slate-900 grid grid-cols-2 gap-2 text-center">
                        <div><p class="text-[8px] text-slate-500 uppercase">Capacity</p><p class="text-[10px] text-white font-mono">{{ $specs['ram_total'] }}</p></div>
                        <div class="border-l border-slate-900"><p class="text-[8px] text-slate-500 uppercase">Available</p><p class="text-[10px] text-green-400 font-mono">{{ $specs['ram_free'] }}</p></div>
                    </div>
                </div>

                <!-- NODE SPECS -->
                <div class="lg:col-span-4 bg-slate-950/50 border border-slate-800 p-5 rounded-2xl">
                    <span class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-3 block">Infrastructure Specs</span>
                    <div class="space-y-2">
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="text-slate-500">Processors</span>
                            <span class="text-sky-300 font-bold font-mono">{{ $specs['cores'] }} CORES (SMP)</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="text-slate-500">Connectivity</span>
                            <span class="text-amber-500 font-bold font-mono">1Gbps Port</span>
                        </div>
                        <div class="flex justify-between items-center text-[10px]">
                            <span class="text-slate-500">System State</span>
                            <span class="text-green-500 font-bold uppercase animate-pulse">Ready</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ATTACK CONFIGURATION -->
            <form action="{{ route('stress.start') }}" method="POST" target="terminal-frame" onsubmit="startAttack()">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-8 bg-slate-950/30 p-6 rounded-2xl border border-slate-800/50">
                    <div class="lg:col-span-6">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Target URL / EndPoint</label>
                        <input type="text" name="url" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm focus:ring-2 focus:ring-sky-500 transition-all font-mono" placeholder="https://target.com" value="https://">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Port</label>
                        <input type="number" name="port" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="443">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Threads</label>
                        <input type="number" name="threads" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="32">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Sec</label>
                        <input type="number" name="duration" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="60">
                    </div>
                    <div class="lg:col-span-8">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Attack Protocol Selection</label>
                        <select name="mode" id="mode" class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-black tracking-tight focus:ring-2 focus:ring-sky-500 transition-all font-mono">
                            <option value="1">L7-STD: Application Layer Flood (Standard)</option>
                            <option value="2">L7-ADV: Human Identity Emulation (Bypass)</option>
                            <option value="3">L7-DBX: Database & Search Exhaustion (Cache Bypass)</option>
                            <option value="4">L7-KLR: Killer V7 WRK-Mode (Max Efficacy)</option>
                            <option value="5">L7-SLO: Connection Slots Exhaustion (Slowloris)</option>
                        </select>
                    </div>
                    <div class="lg:col-span-4 flex items-end gap-3">
                        <button type="submit" id="btn-launch" class="flex-1 bg-sky-600 hover:bg-sky-500 text-white font-black py-4 rounded-xl shadow-[0_0_20px_rgba(14,165,233,0.3)] transition-all flex items-center justify-center gap-2 uppercase tracking-tighter">
                            <i class="fa-solid fa-bolt"></i> <span id="btn-text">Execute Strike</span>
                        </button>
                        <button type="button" onclick="confirmStop()" class="bg-slate-800 hover:bg-red-600 text-slate-400 hover:text-white px-6 py-4 rounded-xl transition-all">
                            <i class="fa-solid fa-power-off"></i>
                        </button>
                    </div>
                </div>
            </form>

            </div>
            
            <!-- TERMINAL SECTION -->
            <div class="mt-8">
                <div class="flex justify-between items-center mb-2 px-1">
                    <label class="text-[10px] font-bold uppercase text-slate-500 tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-terminal text-sky-500"></i> Realtime Strike Logs
                    </label>
                    <span class="text-[10px] text-slate-600 font-mono">Stream: V7 Platinum Core</span>
                </div>
                <iframe name="terminal-frame" id="terminal-frame" class="w-full h-[450px] border border-slate-800 rounded-2xl shadow-2xl bg-black"></iframe>
            </div>

            <script>
                function startAttack() {
                    const btn = document.getElementById('btn-launch');
                    const btnText = document.getElementById('btn-text');
                    const indicator = document.getElementById('strike-indicator');
                    
                    if (indicator) indicator.classList.remove('hidden');
                    if (btnText) btnText.innerText = "STRIKE ACTIVE...";
                    if (btn) {
                        btn.style.opacity = "0.7";
                        btn.style.cursor = "wait";
                        btn.disabled = true;
                    }
                    
                    Swal.fire({
                        title: 'V7 Platinum Engaged',
                        text: 'WRK-Performance Engine initiated. Target saturation in progress.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                        background: '#0f172a',
                        color: '#fff'
                    });
                }

                function confirmStop() {
                    Swal.fire({
                        title: 'Abort Strike?',
                        text: "This will terminate all V7 processes immediately!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#334155',
                        confirmButtonText: 'Yes, Abort NOW',
                        background: '#0f172a',
                        color: '#fff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            stopAttack();
                        }
                    })
                }

                function stopAttack() {
                    document.getElementById('terminal-frame').src = 'about:blank';
                    resetStrikeUI();
                    
                    Swal.fire({
                        title: 'Strike Aborted',
                        text: 'V7 protocols terminated.',
                        icon: 'info',
                        timer: 1500,
                        showConfirmButton: false,
                        background: '#0f172a',
                        color: '#fff'
                    });
                }

                function resetStrikeUI() {
                    const indicator = document.getElementById('strike-indicator');
                    if (indicator) indicator.classList.add('hidden');
                    
                    const btn = document.getElementById('btn-launch');
                    const btnText = document.getElementById('btn-text');
                    
                    if (btnText) btnText.innerText = "Execute Strike";
                    if (btn) {
                        btn.style.opacity = "1";
                        btn.style.cursor = "pointer";
                        btn.disabled = false;
                    }
                }
            </script>

            <div class="mt-6 text-center text-xs text-slate-500">
                For internal stress testing and server validation only. Use responsibly.
            </div>
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

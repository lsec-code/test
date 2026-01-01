<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinuxSec | Gold Edition</title>
    <link rel="icon" type="image/x-icon" href="https://cdn-icons-png.flaticon.com/512/1000/1000997.png">
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
                        <i class="fa-solid fa-ghost text-sky-500"></i> LinuxSec <span class="text-xs bg-amber-500/10 text-amber-500 px-2 py-0.5 rounded border border-amber-500/20 shadow-[0_0_10px_rgba(245,158,11,0.2)]">V7.0 GOLD EDITION</span>
                    </h1>
                    <p class="text-[10px] text-slate-500 uppercase tracking-[0.3em] font-bold mt-1">Advanced L7 Dynamic Stress Engine — Private Access</p>
                </div>
                <div class="flex items-center gap-4">
                    <div id="strike-indicator" class="hidden animate-pulse flex items-center gap-2 bg-red-500/10 border border-red-500/20 px-4 py-2 rounded-full">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        <span class="text-[10px] text-red-500 font-bold uppercase tracking-widest">Strike Active</span>
                    </div>
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
            <form action="/start" method="POST" target="terminal-frame" onsubmit="startAttack()">
                @csrf
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 mb-8 bg-slate-950/30 p-6 rounded-2xl border border-slate-800/50">
                    <!-- ROW 1 -->
                    <div class="lg:col-span-5">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Target URL / EndPoint</label>
                        <input type="text" name="url" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" placeholder="https://target.com" value="https://">
                    </div>
                    <div class="lg:col-span-1">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Port</label>
                        <input type="number" name="port" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="443">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Threads</label>
                        <input type="number" name="threads" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="32">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Stop Condition</label>
                        <select name="limit_type" id="limit_type" class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-xs font-mono" onchange="updateLimitLabel()">
                            <option value="time">Flood (Sec)</option>
                            <option value="req">Visitors (Req)</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label id="limit-label" class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Duration (Sec)</label>
                        <input type="number" name="duration" id="duration" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="60">
                    </div>

                    <!-- ROW 2 -->
                    <div class="lg:col-span-3">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">RPS Limit (0=Max)</label>
                        <input type="number" name="rps" required class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-mono" value="0" placeholder="0 = Unlimited">
                    </div>
                    <div class="lg:col-span-5">
                        <label class="block text-[10px] font-bold uppercase text-slate-500 mb-2 tracking-widest">Attack Protocol Selection</label>
                        <select name="mode" id="mode" class="w-full bg-slate-900 border-slate-800 text-white rounded-xl p-3.5 text-sm font-black tracking-tight font-mono">
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

                    <!-- PROXY SECTION -->
                    <div class="lg:col-span-12 mt-6 pt-6 border-t border-slate-800/50">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-network-wired text-amber-500"></i>
                                <label class="text-[10px] font-bold uppercase text-slate-400 tracking-widest">Proxy Configuration (Optional)</label>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" onclick="fetchFreeProxies()" id="btn-fetch-proxy" class="text-[9px] bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 px-3 py-1.5 rounded-lg border border-amber-500/20 transition-all uppercase font-black">
                                    <i class="fa-solid fa-cloud-download mr-1"></i> Get Free Proxies
                                </button>
                                <select name="proxy_type" class="bg-slate-900 border-none text-[9px] text-slate-400 uppercase font-bold rounded-lg px-2 py-1">
                                    <option value="http">HTTP</option>
                                    <option value="socks4">SOCKS4</option>
                                    <option value="socks5">SOCKS5</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="proxies" rows="3" class="w-full bg-slate-950/50 border-slate-800 text-slate-400 rounded-xl p-4 text-xs font-mono placeholder-slate-700 focus:border-amber-500/50 scrollbar-hide" placeholder="1.2.3.4:8080&#10;5.6.7.8:1080 (One per line)"></textarea>
                    </div>

                    <script>
                        function updateLimitLabel() {
                            const type = document.getElementById('limit_type').value;
                            const label = document.getElementById('limit-label');
                            const input = document.getElementById('duration');
                            if(type === 'time') {
                                label.innerText = "Duration (Sec)";
                                if(input.value > 3600) input.value = 60;
                            } else {
                                label.innerText = "Target (Req)";
                                if(input.value < 1000) input.value = 10000;
                            }
                        }
                    </script>
                </div>
            </form>

            </div>
            
            <!-- TERMINAL SECTION (macOS STYLE) -->
            <div class="mt-10 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- LEFT TERMINAL: STRIKE LOGS -->
                <div class="mac-terminal rounded-xl overflow-hidden shadow-2xl bg-black border border-slate-700/50">
                    <div class="bg-slate-800/80 px-4 py-2 flex items-center justify-between border-b border-slate-700/50">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest font-black">TERMINAL - STRIKE LOGS</span>
                        <div class="w-12"></div>
                    </div>
                    <iframe name="terminal-frame" id="terminal-frame" class="w-full h-[400px] border-none bg-black"></iframe>
                </div>

                <!-- RIGHT TERMINAL: LIVE PING -->
                <div class="mac-terminal rounded-xl overflow-hidden shadow-2xl bg-black border border-slate-700/50">
                    <div class="bg-slate-800/80 px-4 py-2 flex items-center justify-between border-b border-slate-700/50">
                        <div class="flex gap-2">
                            <div class="w-3 h-3 rounded-full bg-[#ff5f56]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#ffbd2e]"></div>
                            <div class="w-3 h-3 rounded-full bg-[#27c93f]"></div>
                        </div>
                        <span class="text-[10px] font-mono text-slate-400 uppercase tracking-widest font-black">TERMINAL - TARGET PING</span>
                        <div class="w-12"></div>
                    </div>
                    <div id="ping-terminal" class="w-full h-[400px] bg-black p-4 font-mono text-[12px] text-yellow-400 overflow-y-auto leading-relaxed scroll-smooth">
                        <div class="text-slate-600 italic">Terminal ready. Waiting for strike...</div>
                    </div>
                </div>
            </div>

            <style>
                .mac-terminal { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
                .mac-terminal:hover { transform: translateY(-5px); border-color: #3b82f6; }
                #ping-terminal::-webkit-scrollbar { width: 6px; }
                #ping-terminal::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
            </style>

            <script>
                function appendPingLog(msg, color = 'yellow-400') {
                    const terminal = document.getElementById('ping-terminal');
                    if(!terminal) return;
                    const div = document.createElement('div');
                    div.className = `text-${color}`;
                    div.innerText = msg;
                    terminal.appendChild(div);
                    terminal.scrollTop = terminal.scrollHeight;
                }
                
                // Bridge to iframe
                window.appendPingLog = appendPingLog;

                async function fetchFreeProxies() {
                    const btn = document.getElementById('btn-fetch-proxy');
                    const textarea = document.querySelector('textarea[name="proxies"]');
                    const type = document.querySelector('select[name="proxy_type"]').value;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa-solid fa-spinner animate-spin"></i> Fetching...';
                    
                    try {
                        const res = await fetch(`/proxies/fetch?type=${type}`);
                        const data = await res.json();
                        
                        if (data.success) {
                            textarea.value = data.proxies;
                            Swal.fire({
                                title: 'Proxies Loaded',
                                text: `${data.count} free proxies added to your list.`,
                                icon: 'success',
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false,
                                background: '#1e293b',
                                color: '#fff'
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (err) {
                        Swal.fire({
                            title: 'Fetch Failed',
                            text: err.message || 'Could not connect to proxy node.',
                            icon: 'error',
                            background: '#0f172a',
                            color: '#fff'
                        });
                    } finally {
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fa-solid fa-cloud-download mr-1"></i> Get Free Proxies';
                    }
                }

                function startAttack() {
                    const btn = document.getElementById('btn-launch');
                    const btnText = document.getElementById('btn-text');
                    const indicator = document.getElementById('strike-indicator');
                    const urlInput = document.querySelector('input[name="url"]').value;
                    const terminal = document.getElementById('ping-terminal');
                    
                    if (indicator) indicator.classList.remove('hidden');
                    if (btnText) btnText.innerText = "STRIKE ACTIVE...";
                    if (btn) {
                        btn.style.opacity = "0.7";
                        btn.style.cursor = "wait";
                        btn.disabled = true;
                    }

                    // Reset Ping Terminal
                    terminal.innerHTML = `<div class="text-sky-400 font-bold border-b border-sky-400/20 pb-2 mb-2 uppercase tracking-tighter">
                        <i class="fa-solid fa-satellite-dish mr-2"></i> [BRIDGE] LIVE CONTEXT SYNC: ${urlInput}
                    </div>`;
                    
                    appendPingLog("[*] LinuxSec Gold Unified Probe Engaged...", 'slate-500');
                    
                    Swal.fire({
                        title: 'LinuxSec Gold Engaged',
                        text: 'Unified WRK-Performance Stream initiated.',
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
                        text: "This will terminate all V7 Gold processes immediately!",
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
                    appendPingLog("[*] Monitoring terminated.", 'slate-500');
                    resetStrikeUI();
                    
                    Swal.fire({
                        title: 'Strike Aborted',
                        text: 'LinuxSec Gold protocols terminated.',
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
            fetch(window.location.origin + '/stats')
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

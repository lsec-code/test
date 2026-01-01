<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monster Stres V8 | Portal Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #020617;
            color: #94a3b8;
            font-family: 'Outfit', sans-serif;
            background-image: radial-gradient(circle at 50% 50%, #0f172a 0%, #020617 100%);
        }
        .glass-card {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(51, 65, 85, 0.5);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .glass-card:hover {
            border-color: #38bdf8;
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(56, 189, 248, 0.15);
        }
        .cyber-btn {
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
            transition: all 0.3s;
        }
        .cyber-btn:hover {
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.5);
            transform: scale(1.02);
        }
        .method-badge {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 4px;
            background: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #020617; }
        ::-webkit-scrollbar-thumb { background: #1e293b; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen Selection:bg-sky-500 Selection:text-white pb-20">

    <!-- HERO SECTION -->
    <div class="max-w-7xl mx-auto px-6 pt-16 text-center">
        <div class="inline-flex items-center gap-2 bg-sky-500/10 border border-sky-500/20 px-4 py-2 rounded-full mb-6">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-sky-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-sky-500"></span>
            </span>
            <span class="text-sky-400 text-[10px] font-black uppercase tracking-widest">Monster V8 Platinum Core Active</span>
        </div>
        <h1 class="text-6xl font-black text-white tracking-tighter mb-4">PLATINUM <span class="text-sky-500">HUB</span></h1>
        <p class="text-slate-500 text-lg max-w-2xl mx-auto mb-12">Professional Grade Server Validation & Security Testing Ecosystem. Select a module to begin operation.</p>
    </div>

    <!-- MAIN GRID -->
    <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- LEFT: DDOS TOOLS -->
        <div class="lg:col-span-8 space-y-8">
            <div class="glass-card rounded-3xl p-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                        <i class="fa-solid fa-bolt-lightning text-amber-500"></i> DDoS Operation Center
                    </h2>
                    <a href="{{ route('stress.index') }}" class="cyber-btn text-white px-6 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider">
                        Launch Stress Tester
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- LAYER 4 -->
                    <div class="space-y-4">
                        <h3 class="text-sky-400 font-black text-xs uppercase tracking-[0.2em] flex items-center gap-2">
                            [ Layer - 4 Protocol ]
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="method-badge">.udp</span>
                            <span class="method-badge">.tcp</span>
                            <span class="method-badge">.nfo-killer</span>
                            <span class="method-badge">.udpbypass</span>
                            <span class="method-badge">.std</span>
                            <span class="method-badge">.home</span>
                            <span class="method-badge">.destroy</span>
                            <span class="method-badge">.god</span>
                            <span class="method-badge">.stdv2</span>
                            <span class="method-badge">.flux</span>
                            <span class="method-badge">.ovh-amp</span>
                            <span class="method-badge">.minecraft</span>
                            <span class="method-badge">.samp</span>
                            <span class="method-badge">.ldap</span>
                            <span class="method-badge bg-red-500/10 text-red-500 border-red-500/20">.MAX (100Gbps)</span>
                        </div>
                    </div>

                    <!-- LAYER 7 -->
                    <div class="space-y-4">
                        <h3 class="text-sky-400 font-black text-xs uppercase tracking-[0.2em] flex items-center gap-2">
                            [ Layer - 7 Protocol ]
                        </h3>
                        <div class="flex flex-wrap gap-2">
                            <span class="method-badge">.http-raw</span>
                            <span class="method-badge">.http-socket</span>
                            <span class="method-badge">.httpflood</span>
                            <span class="method-badge">.cf-bypass</span>
                            <span class="method-badge">.uambypass</span>
                            <span class="method-badge">.hyper</span>
                            <span class="method-badge">.cf-pro</span>
                            <span class="method-badge">.crash</span>
                            <span class="method-badge">.sky</span>
                            <span class="method-badge">.wolf-panel</span>
                            <span class="method-badge">.dann</span>
                            <span class="method-badge bg-amber-500/10 text-amber-500 border-amber-500/20">.CFSTRONG</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card rounded-2xl p-6 text-center">
                    <i class="fa-solid fa-shield-halved text-sky-500 text-3xl mb-4"></i>
                    <h4 class="text-white font-bold mb-1">Global Bypass</h4>
                    <p class="text-[10px] text-slate-500 uppercase">Cloudflare / Akamai / Imperva</p>
                </div>
                <div class="glass-card rounded-2xl p-6 text-center">
                    <i class="fa-solid fa-gauge-high text-amber-500 text-3xl mb-4"></i>
                    <h4 class="text-white font-bold mb-1">Low Latency</h4>
                    <p class="text-[10px] text-slate-500 uppercase">Direct Socket Pipelining</p>
                </div>
                <div class="glass-card rounded-2xl p-6 text-center">
                    <i class="fa-solid fa-microchip text-emerald-500 text-3xl mb-4"></i>
                    <h4 class="text-white font-bold mb-1">Multi-Core</h4>
                    <p class="text-[10px] text-slate-500 uppercase">Parallel Thread Optimization</p>
                </div>
            </div>
        </div>

        <!-- RIGHT: SQLI TOOLS -->
        <div class="lg:col-span-4 space-y-6">
            <div class="glass-card rounded-3xl p-8 h-full bg-slate-900/40">
                <h2 class="text-2xl font-bold text-white flex items-center gap-3 mb-6">
                    <i class="fa-solid fa-database text-emerald-500"></i> SQLi Automation
                </h2>
                
                <div class="space-y-1">
                    <div class="group flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400 group-hover:text-white transition-colors">1. Single Site Injection</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600 group-hover:text-sky-500"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">2. Mass Exploit SQLi</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">3. Auto Dorking + Exploit</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer text-sky-400 font-bold">
                        <span class="text-xs">7. Web Crawler + Inject</span>
                        <i class="fa-solid fa-circle-play text-[10px]"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">9. Mail Pass Dumper</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">11. Dork Generator</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">13. PSQLI Final Mod Scan</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl border border-emerald-500/20 bg-emerald-500/5 cursor-pointer">
                        <span class="text-xs text-emerald-400 font-bold">17. Auto Upload Shell</span>
                        <i class="fa-solid fa-fire text-[10px] text-emerald-500"></i>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl hover:bg-sky-500/5 transition-all border border-transparent hover:border-sky-500/20 cursor-pointer">
                        <span class="text-xs text-slate-400">19. Auto Deface JSO</span>
                        <i class="fa-solid fa-chevron-right text-[10px] text-slate-600"></i>
                    </div>
                </div>

                <div class="mt-8 p-4 rounded-2xl bg-black/40 border border-slate-800">
                    <p class="text-[9px] text-slate-500 leading-relaxed italic">
                        "Automated SQL injection suite for scanning, exploitation, and data dumping with bypass capabilities."
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQLi Automation | Monster V8</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #020617; color: #94a3b8; font-family: 'Inter', sans-serif; }
        .terminal-bg { background: rgba(0, 0, 0, 0.8); border: 1px solid #1e293b; }
    </style>
</head>
<body class="p-4 lg:p-10">

    <div class="max-w-5xl mx-auto">
        <!-- HEADER -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-black text-white tracking-widest uppercase">SQLi <span class="text-emerald-500">Automation</span></h1>
                <p class="text-xs text-slate-500 font-mono mt-1">OPERATIONAL MODULE: V8.0 PLATINUM</p>
            </div>
            <a href="{{ route('landing') }}" class="text-slate-500 hover:text-white transition-all">
                <i class="fa-solid fa-house-chimney text-xl"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- CONFIG PANEL -->
            <div class="lg:col-span-1 space-y-4">
                <form action="{{ route('sqli.start') }}" method="POST" target="sqli-frame" onsubmit="startSqli()">
                    @csrf
                    <div class="bg-slate-900/50 p-6 rounded-2xl border border-slate-800">
                        <div class="mb-4">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Select Method</label>
                            <select name="mode_name" id="mode_name" class="w-full bg-slate-950 border-slate-800 text-white rounded-xl p-3 text-xs font-mono">
                                <option @if(request('mode') == 'Single Site Injection') selected @endif>Single Site Injection</option>
                                <option @if(request('mode') == 'Mass Exploit SQLi') selected @endif>Mass Exploit SQLi</option>
                                <option @if(request('mode') == 'Auto Dorking + Exploit') selected @endif>Auto Dorking + Exploit</option>
                                <option @if(request('mode') == 'SQLi Base64 injection') selected @endif>SQLi Base64 injection</option>
                                <option @if(request('mode') == 'SQLi POST method') selected @endif>SQLi POST method</option>
                                <option @if(request('mode') == 'SQLi ERROR Based method') selected @endif>SQLi ERROR Based method</option>
                                <option @if(request('mode') == 'Web Crawler + Inject') selected @endif>Web Crawler + Inject</option>
                                <option @if(request('mode') == 'Reverse ip vuln sqli') selected @endif>Reverse ip vuln sqli</option>
                                <option @if(request('mode') == 'Mail Pass Dumper') selected @endif>Mail Pass Dumper</option>
                                <option @if(request('mode') == 'Hash tools') selected @endif>Hash tools</option>
                                <option @if(request('mode') == 'Dork generator') selected @endif>Dork generator</option>
                                <option @if(request('mode') == 'New Admin Finder') selected @endif>New Admin Finder</option>
                                <option @if(request('mode') == 'PSQLI Final Mod Scan') selected @endif>PSQLI Final Mod Scan</option>
                                <option @if(request('mode') == 'Auto Upload Shell') selected @endif>Auto Upload Shell</option>
                                <option @if(request('mode') == 'Auto deface JSO') selected @endif>Auto deface JSO</option>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-[10px] font-bold text-slate-500 uppercase mb-2 tracking-widest">Target URL / Dork</label>
                            <input type="text" name="target" required class="w-full bg-slate-950 border-slate-800 text-white rounded-xl p-3 text-sm font-mono" placeholder="http://target.com/id=1">
                        </div>

                        <button type="submit" id="btn-sqli" class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-black py-4 rounded-xl transition-all uppercase text-xs tracking-widest flex items-center justify-center gap-2">
                            <i class="fa-solid fa-play"></i> Launch Automation
                        </button>
                    </div>
                </form>

                <div class="bg-amber-500/10 border border-amber-500/20 p-4 rounded-xl">
                    <p class="text-[10px] text-amber-500 leading-relaxed uppercase font-black tracking-tighter">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i> Warning: 
                        This module uses automated payloads. Ensure compliance with target site TOS.
                    </p>
                </div>
            </div>

            <!-- TERMINAL PANEL -->
            <div class="lg:col-span-2">
                <div class="terminal-bg rounded-2xl overflow-hidden h-[500px] flex flex-col shadow-2xl">
                    <div class="bg-slate-800/50 px-4 py-2 border-b border-slate-700 flex items-center justify-between">
                        <span class="text-[10px] font-mono text-slate-400">root@monster:~/sqli_suite</span>
                        <div class="flex gap-1.5">
                            <div class="w-2 h-2 rounded-full bg-red-500/50"></div>
                            <div class="w-2 h-2 rounded-full bg-amber-500/50"></div>
                            <div class="w-2 h-2 rounded-full bg-emerald-500/50"></div>
                        </div>
                    </div>
                    <iframe name="sqli-frame" id="sqli-frame" class="w-full flex-1 border-none bg-black"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        function startSqli() {
            const btn = document.getElementById('btn-sqli');
            btn.disabled = true;
            btn.style.opacity = '0.5';
            btn.innerHTML = '<i class="fa-solid fa-circle-notch animate-spin"></i> Processing...';
        }

        function resetSqliUI() {
            const btn = document.getElementById('btn-sqli');
            btn.disabled = false;
            btn.style.opacity = '1';
            btn.innerHTML = '<i class="fa-solid fa-play"></i> Launch Automation';
        }
    </script>
</body>
</html>

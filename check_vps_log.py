import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" CEK LOG MQTT SUBSCRIBER DI VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    return stdout.read().decode()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mengambil log terbaru dari Laravel (mencari error device_id/mqtt)...")
    
    # Check if supervisor mqtt service is running
    status = ssh_exec(client, "supervisorctl status")
    print(f"\n[STATUS SUPERVISOR]:\n{status}")
    
    # Tail the log
    logs = ssh_exec(client, "tail -n 80 /var/www/motorcycle_management/storage/logs/laravel.log")
    print(f"\n[LOG LARAVEL TERBARU]:\n{logs}")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()

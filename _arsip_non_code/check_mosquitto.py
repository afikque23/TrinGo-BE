import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" CEK CONFIG MOSQUITTO ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    return stdout.read().decode()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Mosquitto Config:")
    print(ssh_exec(client, "cat /etc/mosquitto/mosquitto.conf"))
    
    print("\n[+] Mosquitto Conf.d:")
    print(ssh_exec(client, "cat /etc/mosquitto/conf.d/default.conf"))
    
    print("\n[+] Cek isi file passwd:")
    print(ssh_exec(client, "cat /etc/mosquitto/passwd"))
    
    print("\n[+] Cek status Mosquitto:")
    print(ssh_exec(client, "systemctl status mosquitto"))
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()

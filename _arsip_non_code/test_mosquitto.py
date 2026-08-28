import paramiko
import getpass
import sys

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" TEST MOSQUITTO DIRECTLY ON VPS ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    return stdout.read().decode()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Cek .env file:")
    print(ssh_exec(client, "grep '^MQTT_' /var/www/motorcycle_management/.env"))
    
    print("\n[+] Test PUBLISH lewat CLI dengan erenvsreiner:")
    res = ssh_exec(client, "mosquitto_pub -h 127.0.0.1 -p 1883 -u tringgo_mqtt -P erenvsreiner -t 'test/topic' -m 'hello' -d")
    print(res)
    
    print("\n[+] Test PUBLISH lewat CLI dengan Tringgo123:")
    res2 = ssh_exec(client, "mosquitto_pub -h 127.0.0.1 -p 1883 -u tringgo_mqtt -P Tringgo123 -t 'test/topic' -m 'hello' -d")
    print(res2)

except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()

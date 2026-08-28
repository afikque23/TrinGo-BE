import paramiko
import getpass
import re

hostname = "203.194.115.228"
username = "root"

print("=====================================================")
print(" MEMPERBAIKI KREDENSIAL MQTT TRINGGO ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    print(f"[SERVER] {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("\n[+] Membaca kredensial dari file .env Laravel di server...")
    env_content = ssh_exec(client, "cat /var/www/motorcycle_management/.env")
    
    mqtt_user_match = re.search(r"MQTT_USERNAME=(.+)", env_content)
    mqtt_pass_match = re.search(r"MQTT_PASSWORD=(.+)", env_content)
    
    if not mqtt_user_match or not mqtt_pass_match:
        print("[!] Gagal menemukan MQTT_USERNAME atau MQTT_PASSWORD di file .env server.")
        exit(1)
        
    mqtt_user = mqtt_user_match.group(1).strip()
    mqtt_pass = mqtt_pass_match.group(1).strip()
    
    print(f"    -> Ditemukan Username: {mqtt_user}")
    print(f"    -> Ditemukan Password: {mqtt_pass}")
    
    print("\n[+] Mengonfigurasi Mosquitto (Broker MQTT) dengan kredensial tersebut...")
    # Setup mosquitto config
    ssh_exec(client, """cat <<EOF > /etc/mosquitto/conf.d/tringgo.conf
listener 1883 0.0.0.0
allow_anonymous false
password_file /etc/mosquitto/passwd
EOF""")
    
    # Create password file and add user
    ssh_exec(client, "touch /etc/mosquitto/passwd")
    ssh_exec(client, f"mosquitto_passwd -b /etc/mosquitto/passwd {mqtt_user} {mqtt_pass}")
    
    # Restart mosquitto
    ssh_exec(client, "systemctl restart mosquitto")
    
    print("\n=====================================================")
    print(" 🎉 PERBAIKAN SELESAI! 🎉")
    print(f" Kredensial Mosquitto sudah disinkronkan dengan file .env.")
    print(" Silakan coba sambungkan perangkat ESP32 Anda lagi!")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
finally:
    client.close()

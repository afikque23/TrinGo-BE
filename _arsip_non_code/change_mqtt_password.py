import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
new_mqtt_password = "erenvsreiner"
remote_base = "/var/www/motorcycle_management"

print("=====================================================")
print(" UBAH PASSWORD MQTT (BROKER & LARAVEL) ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    print(f"\n[SERVER] Mengerjakan: {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out = stdout.read().decode()
    if out.strip():
        print(out)
    return out

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print(f"\n[+] Memperbarui password Mosquitto MQTT menjadi: {new_mqtt_password}")
    # Gunakan mosquitto_passwd dengan parameter -b (batch mode) untuk ubah password tanpa prompt
    ssh_exec(client, f"mosquitto_passwd -b /etc/mosquitto/passwd tringgo_mqtt {new_mqtt_password}")
    
    print("\n[+] Merestart layanan Mosquitto Broker...")
    ssh_exec(client, "systemctl restart mosquitto")
    
    print("\n[+] Memperbarui konfigurasi password MQTT di file .env Laravel...")
    ssh_exec(client, f"sed -i 's/^MQTT_PASSWORD=.*/MQTT_PASSWORD={new_mqtt_password}/' {remote_base}/.env")
    
    print("\n[+] Membersihkan cache Laravel & Merestart Worker MQTT (Supervisor)...")
    ssh_exec(client, f"cd {remote_base} && php artisan config:clear && php artisan cache:clear")
    ssh_exec(client, "supervisorctl restart all")
    
    client.close()
    print("\n=====================================================")
    print(" 🎉 BERHASIL! Password MQTT telah diganti! 🎉")
    print(f" Jangan lupa untuk mengubah password di kode ESP32 kamu menjadi:")
    print(f" Password MQTT ESP32 : {new_mqtt_password}")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")

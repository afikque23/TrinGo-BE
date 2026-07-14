import paramiko
import os
import shutil
import time
import getpass
import re
import zipfile

hostname = "203.194.115.228"
username = "root"
local_base = "c:/laragon/www/motorcycle_management"
remote_base = "/var/www/motorcycle_management"

print("=====================================================")
print(" FULL DEPLOY TRINGGO - ALMALINUX VPS ")
print("=====================================================")
print(f"Memulai koneksi ke {hostname} (Pastikan VPS sudah aktif).")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS baru Anda: ")

def ssh_exec(client, command, print_output=True):
    print(f"\n[SERVER] Mengerjakan: {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=True)
    out_buffer = []
    
    while True:
        line = stdout.readline()
        if not line:
            break
        out_buffer.append(line)
        if print_output:
            print(line, end="")
            
    exit_status = stdout.channel.recv_exit_status()
    full_out = "".join(out_buffer)
    
    if exit_status != 0:
        print(f"Command failed with exit status {exit_status}")
        raise Exception("SSH Command failed")
    return full_out

def zipdir(path, ziph):
    for root, dirs, files in os.walk(path):
        # Abaikan folder yang tidak perlu untuk menghemat kuota dan waktu upload
        if 'vendor' in dirs: dirs.remove('vendor')
        if 'node_modules' in dirs: dirs.remove('node_modules')
        if '.git' in dirs: dirs.remove('.git')
        
        for file in files:
            if file == '.env': continue # abaikan file env lokal
            if file == 'full_deploy.py': continue
            if file.endswith('.zip'): continue
            
            file_path = os.path.join(root, file)
            arcname = os.path.relpath(file_path, path)
            ziph.write(file_path, arcname)

try:
    print("\n[+] Menghubungkan ke VPS...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    try:
        client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    except TypeError:
        # Fallback if paramiko is older and doesn't support disabled_algorithms
        client.connect(hostname, username=username, password=password)
    
    # 1. Upload and run setup-ubuntu.sh
    print("\n[+] Mengunggah file setup-ubuntu.sh...")
    sftp = client.open_sftp()
    sftp.put(os.path.join(local_base, "setup-ubuntu.sh"), "/root/setup-ubuntu.sh")
    
    print("\n[+] Menjalankan instalasi server (Proses ini memakan waktu sekitar 5 - 10 menit, mohon jangan ditutup)...")
    setup_output = ssh_exec(client, "chmod +x /root/setup-ubuntu.sh && /root/setup-ubuntu.sh")
    
    # Parse credentials
    db_pass_match = re.search(r"DB_PASSWORD=([A-Za-z0-9]+)", setup_output)
    mqtt_pass_match = re.search(r"MQTT_PASSWORD=([A-Za-z0-9]+)", setup_output)
    
    db_pass = db_pass_match.group(1) if db_pass_match else "unknown"
    mqtt_pass = mqtt_pass_match.group(1) if mqtt_pass_match else "unknown"
    
    print(f"\n[INFO] Kredensial berhasil didapatkan otomatis:")
    print(f"DB_PASSWORD = {db_pass}")
    print(f"MQTT_PASSWORD = {mqtt_pass}")
    
    # 2. Compress project
    print("\n[+] Mengompres proyek lokal (zip) agar upload lebih cepat...")
    zip_path = os.path.join(local_base, "project_deploy.zip")
    with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zipf:
        zipdir(local_base, zipf)
    print("Selesai mengompres.")

    # 3. Upload project
    print(f"\n[+] Mengunggah file proyek ke server...")
    sftp.put(zip_path, "/root/project.zip")
    sftp.close()
    
    # 4. Extract and Setup
    print("\n[+] Mengekstrak dan mengatur proyek di server...")
    ssh_exec(client, f"rm -rf {remote_base}/*") # Bersihkan folder jika ada
    ssh_exec(client, f"unzip -o -q /root/project.zip -d {remote_base}")
    
    # Create .env dynamically
    env_content = f"""APP_NAME=TringGo
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://tringgo.site

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=motorcyclemanagement
DB_USERNAME=tringgo_db
DB_PASSWORD={db_pass}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=motorcycleappnotification@gmail.com
MAIL_PASSWORD=jslhtlwqftlusbix
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=motorcycleapptification@gmail.com
MAIL_FROM_NAME="${{APP_NAME}}"

MQTT_HOST=127.0.0.1
MQTT_PORT=1883
MQTT_USERNAME=tringgo_mqtt
MQTT_PASSWORD={mqtt_pass}
MQTT_TOPICS=vehicle/+/telemetry

GEMINI_API_KEY=AIzaSyAtPITeOK6sSSM4sk1YGLjw7gCHs-ztQZA
GEMINI_MODEL=gemini-2.5-flash
GEMINI_MAX_OUTPUT_TOKENS=64
"""
    # write env
    sftp = client.open_sftp()
    with sftp.file(f"{remote_base}/.env", "w") as f:
        f.write(env_content)
    sftp.close()
    
    # 5. Run Composer, generate key, migrate, set permissions
    print("\n[+] Menjalankan perintah instalasi Laravel...")
    ssh_exec(client, f"cd {remote_base} && composer install --no-interaction --no-dev --optimize-autoloader", print_output=False)
    ssh_exec(client, f"cd {remote_base} && php artisan key:generate", print_output=False)
    ssh_exec(client, f"cd {remote_base} && php artisan migrate --force", print_output=True)
    ssh_exec(client, f"cd {remote_base} && php artisan storage:link", print_output=False)
    
    print("\n[+] Mengatur hak akses keamanan (Permissions)...")
    ssh_exec(client, f"chown -R www-data:www-data {remote_base}", print_output=False)
    ssh_exec(client, f"chmod -R 775 {remote_base}/storage {remote_base}/bootstrap/cache", print_output=False)
    
    # Cleanup
    ssh_exec(client, f"rm -f /root/project.zip", print_output=False)
    if os.path.exists(zip_path):
        os.remove(zip_path)
    
    # Restart services just in case
    print("\n[+] Merestart layanan server...")
    ssh_exec(client, "systemctl restart supervisor nginx php8.2-fpm", print_output=False)
    
    client.close()
    print("\n=====================================================")
    print(" 🎉 BERHASIL! Instalasi server & Website TringGo selesai! 🎉")
    print(f" Silakan akses website Anda di: http://tringgo.site")
    print("\n **CATATAN PENTING**: Jangan lupa perbarui file config.h")
    print(" di kodingan ESP32 Anda dengan kredensial MQTT berikut:")
    print(f" MQTT Username : tringgo_mqtt")
    print(f" MQTT Password : {mqtt_pass}")
    print("=====================================================")
    
except Exception as e:
    print(f"\n[!] TERJADI KESALAHAN: {str(e)}")
    print("Jika error koneksi (Authentication failed), pastikan password yang Anda masukkan benar.")

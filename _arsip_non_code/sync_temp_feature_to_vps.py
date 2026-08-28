import paramiko
import os
from stat import S_ISDIR
from getpass import getpass

VPS_IP = '203.194.115.228'
VPS_USER = 'root'
VPS_PATH = '/var/www/motorcycle_management'

FILES_TO_SYNC = [
    (r"app\Services\TelemetryIngestService.php", "app/Services/TelemetryIngestService.php"),
    (r"app\Http\Controllers\Api\TrackingController.php", "app/Http/Controllers/Api/TrackingController.php"),
    (r"app\Http\Resources\TripResource.php", "app/Http/Resources/TripResource.php"),
    (r"database\migrations\2026_08_10_102339_add_temperature_fields_to_trips_and_points_table.php", "database/migrations/2026_08_10_102339_add_temperature_fields_to_trips_and_points_table.php")
]

def main():
    print("============================================================")
    print("   SYNC & MIGRATE FITUR SUHU MESIN DETAIL KE VPS")
    print("============================================================")
    
    password = getpass(prompt='Masukkan Password ROOT VPS: ')
    
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    
    try:
        print("\n[+] Menghubungkan ke VPS...")
        ssh.connect(hostname=VPS_IP, username=VPS_USER, password=password)
        print("✅ Terhubung ke VPS!\n")
        
        sftp = ssh.open_sftp()
        
        print("[+] Mengupload File PHP & Migration...")
        local_base_path = r"c:\laragon\www\motorcycle_management"
        
        for local_rel, remote_rel in FILES_TO_SYNC:
            local_path = os.path.join(local_base_path, local_rel)
            remote_path = f"{VPS_PATH}/{remote_rel}"
            
            print(f"    -> Mengupload {local_rel} ...")
            sftp.put(local_path, remote_path)
            
        print("✅ Semua file berhasil diupload!\n")
        
        print("[+] Menjalankan Migration di VPS...")
        command = f"cd {VPS_PATH} && php artisan migrate --force"
        print(f"    [CMD] {command}")
        stdin, stdout, stderr = ssh.exec_command(command)
        out = stdout.read().decode('utf-8')
        err = stderr.read().decode('utf-8')
        
        print("\n[OUTPUT MIGRATION]")
        print(out)
        if err:
            print("[ERROR MIGRATION]")
            print(err)
            
        print("✅ PROSES SELESAI!")
        
    except Exception as e:
        print(f"❌ ERROR: {e}")
    finally:
        ssh.close()

if __name__ == '__main__':
    main()

import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("Masukkan Password ROOT VPS untuk debug DB:")
password = getpass.getpass()

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    print("--- MENJALANKAN MIGRASI ---")
    stdin, stdout, stderr = ssh.exec_command("php /var/www/motorcycle_management/artisan migrate --force")
    print(stdout.read().decode())
    print(stderr.read().decode())
    
    print("--- DEBUGGING TRIP POINTS ---")
    stdin, stdout, stderr = ssh.exec_command("php /var/www/motorcycle_management/artisan tinker --execute=\"dump(App\\Models\\TripPoint::orderByDesc('id')->take(5)->get(['id','trip_id','engine_temp_c','created_at'])->toArray());\"")
    print(stdout.read().decode())
    
    print("--- DEBUGGING TRIP ---")
    stdin, stdout, stderr = ssh.exec_command("php /var/www/motorcycle_management/artisan tinker --execute=\"dump(App\\Models\\Trip::orderByDesc('id')->first(['id','status','avg_temperature_c','max_temperature_c','min_temperature_c'])->toArray());\"")
    print(stdout.read().decode())
    
finally:
    ssh.close()

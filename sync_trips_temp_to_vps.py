import paramiko
import getpass
import os

hostname = "203.194.115.228"
username = "root"

print("Masukkan Password ROOT VPS untuk sinkronisasi backend:")
password = getpass.getpass()

local_tracking_controller = r"c:\laragon\www\motorcycle_management\app\Http\Controllers\Api\TrackingController.php"
local_trip_resource = r"c:\laragon\www\motorcycle_management\app\Http\Resources\TripResource.php"

remote_tracking_controller = "/var/www/motorcycle_management/app/Http/Controllers/Api/TrackingController.php"
remote_trip_resource = "/var/www/motorcycle_management/app/Http/Resources/TripResource.php"

try:
    # SSH Client for permissions/chown later
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    # SFTP Client for file transfer
    transport = paramiko.Transport((hostname, 22))
    transport.connect(username=username, password=password)
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    print("Uploading TrackingController.php...")
    sftp.put(local_tracking_controller, remote_tracking_controller)
    print("Uploading TripResource.php...")
    sftp.put(local_trip_resource, remote_trip_resource)
    
    sftp.close()
    transport.close()
    
    print("Fixing permissions...")
    ssh.exec_command(f"chown www-data:www-data {remote_tracking_controller}")
    ssh.exec_command(f"chown www-data:www-data {remote_trip_resource}")
    
    print("✅ BERHASIL! File backend telah disinkronkan ke VPS.")
    
finally:
    ssh.close()

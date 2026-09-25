import paramiko
import getpass
import os

hostname = "203.194.115.228"
username = "root"

print("Masukkan Password ROOT VPS untuk sinkronisasi backend:")
password = getpass.getpass()

local_tracking_controller = r"c:\laragon\www\motorcycle_management\app\Http\Controllers\Api\TrackingController.php"
local_trip_resource = r"c:\laragon\www\motorcycle_management\app\Http\Resources\TripResource.php"
local_trip_point = r"c:\laragon\www\motorcycle_management\app\Models\TripPoint.php"
local_trip_model = r"c:\laragon\www\motorcycle_management\app\Models\Trip.php"
local_telemetry_service = r"c:\laragon\www\motorcycle_management\app\Services\TelemetryIngestService.php"
local_migration = r"c:\laragon\www\motorcycle_management\database\migrations\2026_08_11_fix_temperature_columns_in_trips.php"
local_old_migration = r"c:\laragon\www\motorcycle_management\database\migrations\2026_08_10_102339_add_temperature_fields_to_trips_and_points_table.php"

remote_tracking_controller = "/var/www/motorcycle_management/app/Http/Controllers/Api/TrackingController.php"
remote_trip_resource = "/var/www/motorcycle_management/app/Http/Resources/TripResource.php"
remote_trip_point = "/var/www/motorcycle_management/app/Models/TripPoint.php"
remote_trip_model = "/var/www/motorcycle_management/app/Models/Trip.php"
remote_telemetry_service = "/var/www/motorcycle_management/app/Services/TelemetryIngestService.php"
remote_migration = "/var/www/motorcycle_management/database/migrations/2026_08_11_fix_temperature_columns_in_trips.php"
remote_old_migration = "/var/www/motorcycle_management/database/migrations/2026_08_10_102339_add_temperature_fields_to_trips_and_points_table.php"

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
    print("Uploading TripPoint.php (FIX FILLABLE)...")
    sftp.put(local_trip_point, remote_trip_point)
    print("Uploading Trip.php (FIX FILLABLE TRIP)...")
    sftp.put(local_trip_model, remote_trip_model)
    print("Uploading TelemetryIngestService.php (FIX GPS FILTER)...")
    sftp.put(local_telemetry_service, remote_telemetry_service)
    print("Uploading Old Migration File (FIX CRASH)...")
    sftp.put(local_old_migration, remote_old_migration)
    print("Uploading New Migration File...")
    sftp.put(local_migration, remote_migration)
    
    sftp.close()
    transport.close()
    
    print("Fixing permissions...")
    ssh.exec_command(f"chown www-data:www-data {remote_tracking_controller}")
    ssh.exec_command(f"chown www-data:www-data {remote_trip_resource}")
    ssh.exec_command(f"chown www-data:www-data {remote_trip_point}")
    ssh.exec_command(f"chown www-data:www-data {remote_trip_model}")
    ssh.exec_command(f"chown www-data:www-data {remote_telemetry_service}")
    ssh.exec_command(f"chown www-data:www-data {remote_old_migration}")
    ssh.exec_command(f"chown www-data:www-data {remote_migration}")
    
    print("Restarting Laravel Queue/Horizon (agar MQTT Worker terupdate)...")
    ssh.exec_command("php /var/www/motorcycle_management/artisan queue:restart")
    
    print("Restarting MQTT Subscriber Daemon...")
    ssh.exec_command("pkill -f 'mqtt:subscribe'")
    
    print("Running Database Migrations di VPS (Menambahkan kolom suhu)...")
    ssh.exec_command("php /var/www/motorcycle_management/artisan migrate --force")
    
    print("✅ BERHASIL! File backend telah disinkronkan ke VPS dan Database di-update.")
    
finally:
    ssh.close()

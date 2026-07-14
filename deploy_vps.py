import paramiko
import sys
import os

hostname = "203.194.115.228"
username = "root"
password = "Tringgo123"

# List of files to upload (relative to local root)
files_to_upload = [
    "app/Http/Controllers/Api/AuthController.php",
    "app/Http/Controllers/AuthController.php",
    "app/Http/Requests/Auth/RegisterRequest.php",
    "app/Services/Fuzzy/FuzzyEngineV2.php",
    "app/Services/RecommendationService.php",
    "public/js/fuzzy/admin.js",
    "resources/views/admin/fuzzy/index.blade.php",
    "public/db-admin/index.php",
    "app/Services/TelemetryIngestService.php",
    "app/Services/Fuzzy/FuzzyEngine.php",
    "app/Services/Fuzzy/DefaultFuzzyConfig.php",
    "resources/views/admin/fuzzy_config/edit.blade.php"
]

local_base = "c:/laragon/www/motorcycle_management"
remote_base = "/var/www/motorcycle_management"

try:
    print(f"Connecting to {hostname}...")
    transport = paramiko.Transport((hostname, 22))
    transport.connect(username=username, password=password)
    
    sftp = paramiko.SFTPClient.from_transport(transport)
    
    for file_path in files_to_upload:
        local_path = os.path.join(local_base, file_path)
        remote_path = f"{remote_base}/{file_path}"
        
        # Ensure remote directory exists
        remote_dir = os.path.dirname(remote_path)
        try:
            sftp.stat(remote_dir)
        except IOError:
            print(f"Directory {remote_dir} does not exist. (Assuming it does for now, Laravel structure should exist)")
            
        print(f"Uploading {local_path} -> {remote_path}")
        sftp.put(local_path, remote_path)
        
    sftp.close()
    
    print("Files uploaded successfully. Clearing cache...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    stdin, stdout, stderr = client.exec_command(f"cd {remote_base} && php artisan view:clear && php artisan cache:clear")
    out = stdout.read().decode()
    err = stderr.read().decode()
    
    if out: print("--- STDOUT ---\n", out)
    if err: print("--- STDERR ---\n", err)
    
    client.close()
    transport.close()
    print("Deployment completed!")

except Exception as e:
    print(f"Failed: {str(e)}")
    sys.exit(1)

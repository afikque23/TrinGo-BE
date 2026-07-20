import paramiko
import sys

import getpass

hostname = "203.194.115.228"
username = "root"
print("=====================================================")
print(" UPDATE VPS .ENV TRINGGO ")
print("=====================================================")
password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")
remote_base = "/var/www/motorcycle_management"

env_addition = """
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=motorcycleappnotification@gmail.com
MAIL_PASSWORD=jslhtlwqftlusbix
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=motorcycleapptification@gmail.com
MAIL_FROM_NAME="${APP_NAME}"

GEMINI_API_KEY=AIzaSyAtPITeOK6sSSM4sk1YGLjw7gCHs-ztQZA
GEMINI_MODEL=gemini-2.5-flash
GEMINI_MAX_OUTPUT_TOKENS=64
"""

try:
    print(f"Connecting to {hostname}...")
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password)
    
    # Check if they are already in the .env file to prevent duplicates
    check_command = f"grep 'MAIL_MAILER' {remote_base}/.env"
    stdin, stdout, stderr = client.exec_command(check_command)
    output = stdout.read().decode().strip()
    
    if output:
        print("MAIL settings already exist in the remote .env. Overwriting them using sed might be tricky, let's just append if not there, or maybe we can clear old ones.")
        # We can just remove old lines and append new ones
        remove_cmd = f"sed -i '/^MAIL_/d' {remote_base}/.env && sed -i '/^GEMINI_/d' {remote_base}/.env"
        client.exec_command(remove_cmd)
        
    print("Appending new MAIL and GEMINI settings to remote .env...")
    append_cmd = f"echo '{env_addition}' >> {remote_base}/.env"
    client.exec_command(append_cmd)
    
    print("Clearing Laravel Cache...")
    stdin, stdout, stderr = client.exec_command(f"cd {remote_base} && php artisan config:clear && php artisan cache:clear")
    out = stdout.read().decode()
    err = stderr.read().decode()
    
    if out: print("--- STDOUT ---\n", out)
    if err: print("--- STDERR ---\n", err)
    
    client.close()
    print("Remote .env updated successfully!")
    
except Exception as e:
    print(f"Failed: {str(e)}")
    sys.exit(1)

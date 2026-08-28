import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"

print("Masukkan Password ROOT VPS:")
password = getpass.getpass()

try:
    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(hostname, username=username, password=password, disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
    
    stdin, stdout, stderr = client.exec_command("cat /var/www/motorcycle_management/app/Http/Resources/TripResource.php")
    print(stdout.read().decode())
finally:
    client.close()

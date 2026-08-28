import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    print(f"\n[CMD] {command}")
    stdin, stdout, stderr = client.exec_command(command, get_pty=False)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    if out: print("[OUT]", out)
    if err: print("[ERR]", err)
    return out, err

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
except TypeError:
    client.connect(hostname, username=username, password=password)

print("\n=== Menjalankan NotificationTemplateSeederUpdate di VPS ===")
ssh_exec(client, f"cd {remote_base} && php artisan db:seed --class=NotificationTemplateSeederUpdate --force")

print("\n=== Verifikasi template fuzzy_critical di DB ===")
ssh_exec(client, f"cd {remote_base} && php artisan tinker --execute=\"$t = \\App\\Models\\NotificationTemplate::where('trigger_type','fuzzy_critical')->first(); echo $t ? 'FOUND: '.$t->name.' | active='.$t->is_active : 'NOT FOUND';\"")

print("\n=== Selesai! ===")
client.close()

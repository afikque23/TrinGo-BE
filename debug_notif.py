import paramiko
import getpass

hostname = "203.194.115.228"
username = "root"
remote_base = "/var/www/motorcycle_management"

password = getpass.getpass(prompt="Masukkan Password ROOT VPS Anda: ")

def ssh_exec(client, command):
    stdin, stdout, stderr = client.exec_command(command, get_pty=False)
    out = stdout.read().decode('utf-8', errors='replace')
    err = stderr.read().decode('utf-8', errors='replace')
    return out, err

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    client.connect(hostname, username=username, password=password,
                   disabled_algorithms={'pubkeys': ['rsa-sha2-256', 'rsa-sha2-512']})
except TypeError:
    client.connect(hostname, username=username, password=password)

print("\n========== LARAVEL LOG (50 baris terakhir) ==========")
out, err = ssh_exec(client, f"tail -n 50 {remote_base}/storage/logs/laravel.log")
print(out)
if err:
    print("[STDERR]", err)

print("\n========== TEST ARTISAN TINKER (kirim event manual) ==========")
test_cmd = f"cd {remote_base} && php artisan tinker --execute=\"\\$v = \\App\\\\Models\\\\Vehicle::with('user')->first(); echo 'Vehicle: '.(\\$v ? \\$v->title : 'NONE').PHP_EOL; if(\\$v){{ \\App\\\\Events\\\\VehicleStatusChecked::dispatch(\\$v); echo 'Event dispatched!'.PHP_EOL; }}\""
out, err = ssh_exec(client, test_cmd)
print(out)
if err:
    print("[STDERR]", err)

print("\n========== LOG SETELAH EVENT ==========")
out, err = ssh_exec(client, f"tail -n 30 {remote_base}/storage/logs/laravel.log")
print(out)

client.close()

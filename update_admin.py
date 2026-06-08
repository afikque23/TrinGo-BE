import paramiko

host = "203.194.115.228"
user = "root"
password = "Tringgo123"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    print(f"Connecting to {host}...")
    client.connect(hostname=host, username=user, password=password, timeout=10)
    print("Connected! Executing tinker command...")
    
    # We will try to find the admin user (role=admin, or id=1) and update email and password
    tinker_script = """
$user = App\\Models\\User::where('email', 'admin@admin.com')->first();
if (!$user) {
    $user = App\\Models\\User::where('role', 'admin')->first();
}
if (!$user) {
    $user = App\\Models\\User::find(1);
}
if ($user) {
    $user->email = 'admin@tringgo.com';
    $user->password = Hash::make('admin123');
    $user->save();
    echo 'SUCCESS: Admin updated to admin@tringgo.com';
} else {
    echo 'ERROR: No user found to update.';
}
"""
    # Escape for bash
    tinker_script_escaped = tinker_script.replace('$', '\\$').replace('"', '\\"').replace('`', '\\`')
    command_str = f"cd /var/www/motorcycle_management && php artisan tinker --execute=\"{tinker_script_escaped}\""
    
    stdin, stdout, stderr = client.exec_command(command_str)
    
    exit_status = stdout.channel.recv_exit_status()
    out = stdout.read().decode('utf-8', errors='ignore')
    err = stderr.read().decode('utf-8', errors='ignore')
    
    print(f"Exit status: {exit_status}")
    print(f"STDOUT:\n{out}")
    print(f"STDERR:\n{err}")
    
finally:
    client.close()

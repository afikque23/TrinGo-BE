import paramiko

host = "203.194.115.228"
user = "root"
password = "Tringgo123"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    print(f"Connecting to {host}...")
    client.connect(hostname=host, username=user, password=password, timeout=10)
    print("Connected! Executing commands...")
    
    commands = [
        "mysql -u root -e \"CREATE USER IF NOT EXISTS 'tringgo'@'localhost' IDENTIFIED BY 'tringgo'; GRANT ALL PRIVILEGES ON motorcyclemanagement.* TO 'tringgo'@'localhost'; FLUSH PRIVILEGES;\"",
        "mysql -u root -e \"DROP USER IF EXISTS 'tringgo_db'@'localhost'; FLUSH PRIVILEGES;\"",
        "sed -i 's/^DB_USERNAME=.*/DB_USERNAME=tringgo/g' /var/www/motorcycle_management/.env",
        "sed -i 's/^DB_PASSWORD=.*/DB_PASSWORD=tringgo/g' /var/www/motorcycle_management/.env",
        "cd /var/www/motorcycle_management && php artisan config:clear",
        "systemctl restart supervisord",
        "echo 'DB_UPDATED_SUCCESSFULLY'"
    ]
    
    command_str = " && ".join(commands)
    
    stdin, stdout, stderr = client.exec_command(command_str)
    
    exit_status = stdout.channel.recv_exit_status()
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    
    print(f"Exit status: {exit_status}")
    print(f"STDOUT:\n{out}")
    print(f"STDERR:\n{err}")
    
finally:
    client.close()

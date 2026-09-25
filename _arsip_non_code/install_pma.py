import paramiko
import sys

host = "203.194.115.228"
user = "root"
password = "Tringgo123"

client = paramiko.SSHClient()
client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
try:
    print(f"Connecting to {host}...")
    client.connect(hostname=host, username=user, password=password, timeout=10)
    print("Connected! Executing commands...")
    
    command_str = "dnf install -y wget unzip && cd /var/www/motorcycle_management/public && wget https://www.phpmyadmin.net/downloads/phpMyAdmin-latest-all-languages.zip && unzip -q -o phpMyAdmin-latest-all-languages.zip && rm -rf db-admin && mv phpMyAdmin-*-all-languages db-admin && rm -f phpMyAdmin-latest-all-languages.zip && chown -R nginx:nginx db-admin && echo 'SUCCESS_ALL'"
    
    stdin, stdout, stderr = client.exec_command(command_str)
    
    exit_status = stdout.channel.recv_exit_status()
    out = stdout.read().decode('utf-8')
    err = stderr.read().decode('utf-8')
    
    print(f"Exit status: {exit_status}")
    print(f"STDOUT:\n{out}")
    print(f"STDERR:\n{err}")
    
finally:
    client.close()

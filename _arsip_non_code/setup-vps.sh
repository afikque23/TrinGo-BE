#!/bin/bash

# ==============================================================================
# Script Setup VPS AlmaLinux 8 - Project TringGo
# Memasang: PHP 8.2, Nginx, MariaDB (MySQL), Mosquitto (MQTT), Supervisor, Composer & Node.js
# Harap jalankan script ini sebagai root: sudo bash setup-vps.sh
# ==============================================================================

# Exit on error
set -e

# Pastikan dijalankan sebagai root
if [ "$EUID" -ne 0 ]; then
  echo "[-] ERROR: Script ini harus dijalankan dengan hak akses root (sudo)."
  exit 1
fi

# ==============================================================================
# KONFIGURASI VARIABEL
# Silakan sesuaikan dengan kebutuhan Anda
# ==============================================================================
DB_DATABASE="motorcyclemanagement"
DB_USER="tringgo_db"
DB_PASSWORD=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9')

MQTT_USER="tringgo_mqtt"
MQTT_PASSWORD=$(openssl rand -base64 12 | tr -dc 'a-zA-Z0-9')

APP_DIR="/var/www/motorcycle_management"
DOMAIN="vps.tringgo.site"
IP_SERVER="203.194.115.228"

echo "======================================================================"
echo " MEMULAI INSTALASI SERVIS VPS UNTUK TRINGGO (ALMALINUX 8) "
echo "======================================================================"
echo "DB Database  : $DB_DATABASE"
echo "DB User      : $DB_USER"
echo "DB Password  : $DB_PASSWORD"
echo "MQTT User    : $MQTT_USER"
echo "MQTT Password: $MQTT_PASSWORD"
echo "======================================================================"
sleep 3

# 1. Update system & install EPEL & Remi Repositories
echo "[+] 1. Memasang repository EPEL dan Remi..."
dnf update -y
dnf install -y epel-release
dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
dnf install -y unzip git tar policycoreutils-python-utils

# 2. Reset dan enable module PHP 8.2
echo "[+] 2. Mengaktifkan dan memasang PHP 8.2..."
dnf module reset php -y
dnf module enable php:remi-8.2 -y
dnf install -y php php-fpm php-cli php-mysqlnd php-mbstring php-xml php-curl php-zip php-gd php-bcmath php-sockets php-intl php-opcache php-json

# 3. Konfigurasi PHP-FPM agar berjalan di bawah user nginx
echo "[+] 3. Mengonfigurasi PHP-FPM..."
sed -i 's/user = apache/user = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/group = apache/group = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/;listen.owner = nobody/listen.owner = nginx/g' /etc/php-fpm.d/www.conf
sed -i 's/;listen.group = nobody/listen.group = nginx/g' /etc/php-fpm.d/www.conf

systemctl enable php-fpm --now

# 4. Pasang Nginx
echo "[+] 4. Memasang dan mengonfigurasi Nginx..."
dnf install -y nginx
systemctl enable nginx --now

# Buat konfigurasi virtual host Nginx untuk Laravel
cat <<EOF > /etc/nginx/conf.d/tringgo.conf
server {
    listen 80;
    server_name $DOMAIN $IP_SERVER;
    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php\$ {
        fastcgi_pass unix:/run/php-fpm/www.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

systemctl restart nginx

# 5. Pasang MariaDB (MySQL-compatible)
echo "[+] 5. Memasang dan mengonfigurasi MariaDB..."
dnf install -y mariadb-server
systemctl enable mariadb --now

# Membuat Database dan User
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USER}'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

# 6. Pasang Mosquitto (MQTT Broker)
echo "[+] 6. Memasang Mosquitto MQTT Broker..."
dnf install -y mosquitto

# Konfigurasi Mosquitto agar mendengarkan port 1883 dari luar dan menggunakan auth
cat <<EOF > /etc/mosquitto/conf.d/tringgo.conf
listener 1883 0.0.0.0
allow_anonymous false
password_file /etc/mosquitto/passwd
EOF

touch /etc/mosquitto/passwd
mosquitto_passwd -b /etc/mosquitto/passwd "$MQTT_USER" "$MQTT_PASSWORD"

systemctl enable mosquitto --now
systemctl restart mosquitto

# 7. Pasang Node.js & Composer
echo "[+] 7. Memasang Node.js v20 dan Composer..."
dnf module reset nodejs -y
dnf module enable nodejs:20 -y
dnf install -y nodejs

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# 8. Konfigurasi Firewall
echo "[+] 8. Mengatur Firewall (Membuka port 80, 443, 1883)..."
systemctl enable firewalld --now
firewall-cmd --permanent --add-service=http
firewall-cmd --permanent --add-service=https
firewall-cmd --permanent --add-port=1883/tcp
firewall-cmd --permanent --add-port=22/tcp
firewall-cmd --reload

# 9. Buat Direktori Aplikasi & Atur Permission
echo "[+] 9. Membuat direktori aplikasi..."
mkdir -p "$APP_DIR"
chown -R nginx:nginx "$APP_DIR"
chmod -R 775 "$APP_DIR"

# 10. Pasang Supervisor (Process Manager)
echo "[+] 10. Memasang dan mengonfigurasi Supervisor..."
dnf install -y supervisor
systemctl enable supervisord --now

# Buat config untuk Laravel Queue Worker
cat <<EOF > /etc/supervisord.d/tringgo-worker.ini
[program:tringgo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=nginx
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
stopwaitsecs=3600
EOF

# Buat config untuk Laravel MQTT Subscriber Daemon
cat <<EOF > /etc/supervisord.d/tringgo-mqtt.ini
[program:tringgo-mqtt]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan mqtt:subscribe
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=nginx
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/mqtt-subscriber.log
EOF

systemctl restart supervisord

echo "======================================================================"
echo " INSTALASI BERHASIL DISAJIKAN! "
echo "======================================================================"
echo "Mohon catat detail kredensial berikut untuk ditaruh di file .env Anda:"
echo ""
echo "--- DATABASE CONFIG ---"
echo "DB_CONNECTION=mysql"
echo "DB_HOST=127.0.0.1"
echo "DB_PORT=3306"
echo "DB_DATABASE=$DB_DATABASE"
echo "DB_USERNAME=$DB_USER"
echo "DB_PASSWORD=$DB_PASSWORD"
echo ""
echo "--- MQTT CONFIG ---"
echo "MQTT_HOST=127.0.0.1"
echo "MQTT_PORT=1883"
echo "MQTT_USERNAME=$MQTT_USER"
echo "MQTT_PASSWORD=$MQTT_PASSWORD"
echo "MQTT_TOPICS=vehicle/+/telemetry"
echo ""
echo "Hubungkan perangkat IoT (ESP32/ESP8266) Anda menggunakan:"
echo "Broker IP : $IP_SERVER"
echo "Port      : 1883"
echo "Username  : $MQTT_USER"
echo "Password  : $MQTT_PASSWORD"
echo "======================================================================"

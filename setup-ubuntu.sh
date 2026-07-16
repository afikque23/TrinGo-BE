#!/bin/bash
# ==============================================================================
# Script Setup VPS Ubuntu 22.04 - Project TringGo
# ==============================================================================
set -e
if [ "$EUID" -ne 0 ]; then echo "[-] ERROR: Harus dijalankan sebagai root."; exit 1; fi

export DEBIAN_FRONTEND=noninteractive

DB_DATABASE="motorcyclemanagement"
DB_USER="tringgo_db"
DB_PASSWORD="praupos1"
MQTT_USER="tringgo_mqtt"
MQTT_PASSWORD="erenvsreiner"
APP_DIR="/var/www/motorcycle_management"
DOMAIN="vps.tringgo.site"
IP_SERVER="203.194.115.228"

echo "======================================================================"
echo " MEMULAI INSTALASI SERVIS VPS UNTUK TRINGGO (UBUNTU 22.04) "
echo "======================================================================"
echo "DB_PASSWORD=$DB_PASSWORD"
echo "MQTT_PASSWORD=$MQTT_PASSWORD"
echo "======================================================================"

echo "[+] 1. Update sistem dan install paket dasar..."
apt-get update --allow-releaseinfo-change -y
apt-get install -y software-properties-common curl git unzip zip ufw nginx mariadb-server mosquitto mosquitto-clients supervisor

echo "[+] 2. Memasang PHP 8.2..."
add-apt-repository -y ppa:ondrej/php
apt-get update --allow-releaseinfo-change -y
apt-get install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath php8.2-intl php8.2-opcache

echo "[+] 3. Konfigurasi Nginx..."
if [ -f "/etc/nginx/sites-available/tringgo" ] && grep -q "ssl_certificate" "/etc/nginx/sites-available/tringgo"; then
    echo "[!] Konfigurasi SSL Nginx sudah ada. Melewati penimpaan konfigurasi Nginx agar SSL tidak hilang."
else
    cat <<EOF > /etc/nginx/sites-available/tringgo
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
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF
    ln -sf /etc/nginx/sites-available/tringgo /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    systemctl restart nginx
fi

echo "[+] 4. Konfigurasi MariaDB..."
mysql -u root -e "CREATE DATABASE IF NOT EXISTS \`${DB_DATABASE}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost';"
mysql -u root -e "ALTER USER '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASSWORD}';"
mysql -u root -e "GRANT ALL PRIVILEGES ON \`${DB_DATABASE}\`.* TO '${DB_USER}'@'localhost';"
mysql -u root -e "FLUSH PRIVILEGES;"

echo "[+] 5. Konfigurasi Mosquitto..."
cat <<EOF > /etc/mosquitto/conf.d/tringgo.conf
listener 1883 0.0.0.0
allow_anonymous false
password_file /etc/mosquitto/passwd
EOF
touch /etc/mosquitto/passwd
mosquitto_passwd -b /etc/mosquitto/passwd "$MQTT_USER" "$MQTT_PASSWORD"
systemctl restart mosquitto

echo "[+] 6. Memasang Node.js v20 dan Composer..."
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

echo "[+] 7. Mengatur Firewall (UFW)..."
ufw --force enable
ufw allow 'Nginx Full'
ufw allow 1883/tcp
ufw allow 22/tcp

echo "[+] 8. Membuat direktori aplikasi..."
mkdir -p "$APP_DIR"
chown -R www-data:www-data "$APP_DIR"
chmod -R 775 "$APP_DIR"

echo "[+] 9. Mengonfigurasi Supervisor..."
cat <<EOF > /etc/supervisor/conf.d/tringgo-worker.conf
[program:tringgo-worker]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/worker.log
EOF

cat <<EOF > /etc/supervisor/conf.d/tringgo-mqtt.conf
[program:tringgo-mqtt]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan mqtt:subscribe
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/mqtt-subscriber.log
EOF

supervisorctl reread || true
supervisorctl update || true

echo "======================================================================"
echo " INSTALASI UBUNTU BERHASIL! "
echo "======================================================================"

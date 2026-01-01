#!/bin/bash

# MONSTER STRES INSTALLER (UBUNTU/DEBIAN)
# Target: /var/www/test
# Port: 9999
# Repository: https://github.com/lsec-code/test.git
# Created by: LinuxSec

# Colors
GREEN='\033[0;32m'
CYAN='\033[0;36m'
RED='\033[0;31m'
NC='\033[0m'

clear
echo -e "${CYAN}"
cat << "EOF"
  __  __  ____  _   _  _____ _______ ______ _____  
 |  \/  |/ __ \| \ | |/ ____|__   __|  ____|  __ \ 
 | \  / | |  | |  \| | (___    | |  | |__  | |__) |
 | |\/| | |  | | . ` |\___ \   | |  |  __| |  _  / 
 | |  | | |__| | |\  |____) |  | |  | |____| | \ \ 
 |_|  |_|\____/|_| \_|_____/   |_|  |______|_|  \_\
                                                   
      >>> STRESS TESTER DEPLOYMENT SCRIPT <<<
EOF
echo -e "${NC}"

# 1. Check Root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}[!] This script must be run as root.${NC}" 
   exit 1
fi

PROJECT_DIR="/var/www/test"
REPO_URL="https://github.com/lsec-code/test.git"

echo -e "${GREEN}[+] Updating System & Installing Dependencies...${NC}"
apt update -y
apt install -y software-properties-common curl git unzip nginx python3 python3-pip

# Add PHP Repository
add-apt-repository ppa:ondrej/php -y
apt update -y
apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-common php8.3-mysql php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml php8.3-bcmath

# Install Composer
if ! command -v composer &> /dev/null; then
    echo -e "${GREEN}[+] Installing Composer...${NC}"
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
fi

# 2. Setup Project Directory
echo -e "${GREEN}[+] Setting up Project at ${PROJECT_DIR}...${NC}"



# Logic: Clone if not exists, Pull if exists
if [ ! -d "$PROJECT_DIR/.git" ]; then
    echo -e "${CYAN}[+] Clone Repository from ${REPO_URL}...${NC}"
    rm -rf $PROJECT_DIR # Clear integrity
    git clone $REPO_URL $PROJECT_DIR
else
    echo -e "${CYAN}[+] Repository exists. Pulling latest updates...${NC}"
    cd $PROJECT_DIR
    git pull origin main
fi

# SAFETY CHECK: Fix Nested Folder Issue (e.g. /var/www/test/laravel/composer.json)
if [ ! -f "$PROJECT_DIR/composer.json" ]; then
    echo -e "${YELLOW}[!] composer.json not found in root. Checking subdirectories...${NC}"
    SUB_DIR=$(find $PROJECT_DIR -name "composer.json" -type f | head -n 1 | xargs dirname)
    if [ ! -z "$SUB_DIR" ] && [ "$SUB_DIR" != "$PROJECT_DIR" ]; then
        echo -e "${CYAN}[+] Found project in $SUB_DIR. Moving to root...${NC}"
        mv $SUB_DIR/* $PROJECT_DIR/
        mv $SUB_DIR/.* $PROJECT_DIR/ 2>/dev/null
    else
         echo -e "${RED}[!] FATAL: No composer.json found. Clone failed or Repo is empty.${NC}"
         exit 1
    fi
fi

cd $PROJECT_DIR
export COMPOSER_ALLOW_SUPERUSER=1

# 3. Laravel Setup
echo -e "${GREEN}[+] Configuring Laravel...${NC}"
cp .env.example .env

# AUTOMATIC FIX: Use File Session to avoid Database errors
sed -i 's/SESSION_DRIVER=database/SESSION_DRIVER=file/g' .env
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/g' .env

# Create SQLite DB if missing (required for basic operations sometimes)
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
fi

composer install --optimize-autoloader --no-dev
php artisan key:generate
php artisan migrate --force # Run migrations just in case
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Permissions
chown -R www-data:www-data $PROJECT_DIR
chmod -R 775 $PROJECT_DIR/storage
chmod -R 775 $PROJECT_DIR/bootstrap/cache
chmod -R 775 $PROJECT_DIR/database
chmod -R 775 $PROJECT_DIR/bootstrap/cache

# 4. Nginx Setup
echo -e "${GREEN}[+] Configuring Nginx (Port 9999)...${NC}"
cat > /etc/nginx/sites-available/test.conf <<EOF
server {
    listen 9999;
    server_name _;
    root $PROJECT_DIR/public;

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

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# Enable Site
ln -sf /etc/nginx/sites-available/test.conf /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
service nginx restart
service php8.3-fpm restart

# 5. Finalize
echo -e "${CYAN}"
echo "=================================================="
echo "   INSTALLATION COMPLETE!"
echo "=================================================="
echo -e "${GREEN}[+] URL: http://localhost:9999/test (via Tunnel)"
echo -e "${GREEN}[+] Directory: $PROJECT_DIR"
echo -e "${NC}"

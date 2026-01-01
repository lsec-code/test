#!/bin/bash

# MONSTER STRES UNINSTALLER
# Created by: LinuxSec

# Colors
RED='\033[0;31m'
NC='\033[0m'
YELLOW='\033[1;33m'

# Check Root
if [[ $EUID -ne 0 ]]; then
   echo -e "${RED}[!] This script must be run as root.${NC}" 
   exit 1
fi

echo -e "${RED}WARNING: This will delete /var/www/test and the Nginx configuration.${NC}"
read -p "Are you sure? (y/n) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborting."
    exit 1
fi

echo -e "${YELLOW}[*] Removing Nginx Config...${NC}"
rm -f /etc/nginx/sites-enabled/test.conf
rm -f /etc/nginx/sites-available/test.conf

echo -e "${YELLOW}[*] Removing Project Directory...${NC}"
rm -rf /var/www/test

echo -e "${YELLOW}[*] Reloading Nginx...${NC}"
service nginx restart

echo -e "${RED}[+] Uninstallation Complete.${NC}"

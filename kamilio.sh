#!/bin/bash

set -e

echo "=== Kamailio Installer (Ubuntu 24.04) ==="

# --- VARIABLES ---
DB_NAME="voip"
DB_USER="root"
DB_PASS="bkkbkk"
SIP_USER="100"
SIP_PASS="123456"

# --- UPDATE SYSTEM ---
echo "[1/8] Updating system..."
sudo apt update -y
sudo apt upgrade -y

# --- INSTALL DEPENDENCIES ---
echo "[2/8] Installing dependencies..."
sudo apt install -y gnupg2 curl lsb-release ca-certificates mysql-server

# --- ADD KAMAILIO REPO ---
echo "[3/8] Adding Kamailio repository..."
echo "deb http://deb.kamailio.org/kamailio60 $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/kamailio.list

curl -fsSL http://deb.kamailio.org/kamailiodebkey.gpg | \
  sudo gpg --dearmor -o /usr/share/keyrings/kamailio.gpg

echo "deb [signed-by=/usr/share/keyrings/kamailio.gpg] http://deb.kamailio.org/kamailio60 $(lsb_release -cs) main" | sudo tee /etc/apt/sources.list.d/kamailio.list

# --- INSTALL KAMAILIO ---
echo "[4/8] Installing Kamailio..."
sudo apt update -y
sudo apt install -y kamailio kamailio-mysql-modules kamailio-websocket-modules

# --- START MYSQL ---
echo "[5/8] Configuring MySQL..."
sudo systemctl enable mysql
sudo systemctl start mysql

# Create DB + user
sudo mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
sudo mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
sudo mysql -e "FLUSH PRIVILEGES;"

# --- CONFIGURE KAMAILIO DB ---
echo "[6/8] Configuring Kamailio DB..."
sudo sed -i "s/^#\?DBENGINE=.*/DBENGINE=MYSQL/" /etc/kamailio/kamctlrc
sudo sed -i "s/^#\?DBNAME=.*/DBNAME=${DB_NAME}/" /etc/kamailio/kamctlrc
sudo sed -i "s/^#\?DBRWUSER=.*/DBRWUSER=${DB_USER}/" /etc/kamailio/kamctlrc
sudo sed -i "s/^#\?DBRWPW=.*/DBRWPW=${DB_PASS}/" /etc/kamailio/kamctlrc

# Initialize DB schema
sudo kamdbctl create <<EOF
y
EOF

# --- ENABLE + START SERVICE ---
echo "[7/8] Starting Kamailio..."
sudo systemctl enable kamailio
sudo systemctl restart kamailio

# --- CREATE SIP USER ---
echo "[8/8] Creating SIP user..."
sudo kamctl add ${SIP_USER} ${SIP_PASS}

# --- FIREWALL ---
echo "[+] Opening SIP ports..."
sudo ufw allow 5060/udp || true
sudo ufw allow 5060/tcp || true

echo ""
echo "=== INSTALL COMPLETE ==="
echo "SIP USER: ${SIP_USER}"
echo "SIP PASS: ${SIP_PASS}"
echo "DB: ${DB_NAME}"
echo ""
echo "Check status:"
echo "  systemctl status kamailio"
echo "  ss -lntup | grep 5060"

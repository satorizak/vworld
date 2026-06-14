#!/bin/bash
# Install Apache and PHP
sudo apt-get update -q
sudo apt-get install -y -q apache2 php libapache2-mod-php

# Link folders to web root
sudo ln -sf /workspaces/vworld/1uworld /var/www/html/1uworld
sudo ln -sf /workspaces/vworld/1worldthings /var/www/html/1worldthings

# Set permissions
sudo chmod -R 777 /workspaces/vworld/1uworld/data
sudo chmod -R 777 /workspaces/vworld/1uworld/billboards

# Start Apache
sudo service apache2 start

echo "Setup complete!"

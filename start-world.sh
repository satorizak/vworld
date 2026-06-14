#!/bin/bash
sudo service apache2 start
sudo chmod -R 777 /workspaces/vworld/1uworld/data
sudo chmod -R 777 /workspaces/vworld/1uworld/billboards
echo "Starting Cloudflare tunnel..."
./cloudflared tunnel --url http://localhost:80

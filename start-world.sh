#!/bin/bash
# Install Apache and PHP if needed
if ! command -v apache2 > /dev/null; then
  echo "Installing Apache and PHP..."
  sudo apt-get update -q
  sudo apt-get install -y -q apache2 php libapache2-mod-php
fi

# Link folders
sudo ln -sf /workspaces/vworld/1uworld /var/www/html/1uworld
sudo ln -sf /workspaces/vworld/1worldthings /var/www/html/1worldthings

# Set permissions
sudo chmod -R 777 /workspaces/vworld/1uworld/data
sudo chmod -R 777 /workspaces/vworld/1uworld/billboards

# Start Apache
sudo service apache2 start

# Download cloudflared if missing
if [ ! -f "./cloudflared" ]; then
  echo "Downloading cloudflared..."
  curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o cloudflared
  chmod +x cloudflared
fi

# Start keep-alive in background
nohup bash keep-alive.sh &

echo "Starting Cloudflare tunnel..."
./cloudflared tunnel --url http://localhost:80 2>&1 | while IFS= read -r line; do
    echo "$line"
    if echo "$line" | grep -q "trycloudflare.com"; then
        URL=$(echo "$line" | grep -o 'https://[^ ]*trycloudflare.com')
        if [ ! -z "$URL" ]; then
            echo "{\"url\":\"$URL\"}" > /workspaces/vworld/world-url.json
            git -C /workspaces/vworld add world-url.json
            git -C /workspaces/vworld commit -m "update tunnel url"
            git -C /workspaces/vworld push
            echo "URL published to GitHub: $URL"
        fi
    fi
done

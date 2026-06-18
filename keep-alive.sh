#!/bin/bash
while true; do
  # Keep codespace alive
  echo "$(date) - keeping alive" >> /tmp/keepalive.log
  # Check if apache is running, restart if not
  if ! pgrep apache2 > /dev/null; then
    echo "$(date) - restarting apache" >> /tmp/keepalive.log
    sudo service apache2 start
  fi
  sleep 20
done

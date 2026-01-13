#!/bin/bash
echo "Changing ownership to daemon user (the user running Apache)..."
sudo chown -R daemon:daemon storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
echo "Done! Please try accessing the site again."

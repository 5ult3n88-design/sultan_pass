#!/bin/bash
echo "Fixing all Laravel permissions for daemon user..."
sudo chown -R daemon:daemon storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
sudo find storage -type f -exec chmod 664 {} \;
sudo find storage -type d -exec chmod 775 {} \;
echo "Clearing old cache files..."
rm -rf bootstrap/cache/*.php
rm -rf storage/framework/cache/data/*
rm -rf storage/framework/sessions/*
rm -rf storage/framework/views/*
echo "Done! Laravel should work now."

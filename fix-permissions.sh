#!/bin/bash
# Fix Laravel storage and cache permissions for XAMPP on macOS

echo "Fixing Laravel storage permissions..."

# Change ownership to Apache user (_www on macOS)
sudo chown -R _www:_www /Applications/XAMPP/xamppfiles/htdocs/pass-main/storage
sudo chown -R _www:_www /Applications/XAMPP/xamppfiles/htdocs/pass-main/bootstrap/cache

# Set proper permissions (775 for directories, 664 for files)
sudo chmod -R 775 /Applications/XAMPP/xamppfiles/htdocs/pass-main/storage
sudo chmod -R 775 /Applications/XAMPP/xamppfiles/htdocs/pass-main/bootstrap/cache

echo "Permissions fixed! Clearing Laravel caches..."

# Clear caches
cd /Applications/XAMPP/xamppfiles/htdocs/pass-main
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "Done! Your application should now work at http://localhost/pass-main/public"

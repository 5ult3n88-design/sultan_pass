#!/bin/bash
echo "Fixing storage permissions for daemon user (Apache)..."
sudo chown -R daemon:daemon storage
sudo chmod -R 775 storage
echo "Done! Storage is now owned by daemon user."

#!/bin/bash

# Kill any existing servers on port 8000
lsof -ti:8000 | xargs kill -9 2>/dev/null

# Unset any conflicting environment variables
unset DB_DATABASE
unset DB_USERNAME
unset DB_PASSWORD

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear

# Start the server
echo "Starting LangConnect server on http://localhost:8000"
echo "Using database: langconnect"
php artisan serve --host=0.0.0.0 --port=8000

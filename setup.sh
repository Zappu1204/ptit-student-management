#!/bin/bash

# PTIT Student Management System Setup Script

echo "Setting up PTIT Student Management System..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file from .env.example..."
    cp .env.example .env
    echo "Please edit the .env file with your database credentials."
else
    echo ".env file already exists, skipping..."
fi

# Create uploads directory if it doesn't exist
if [ ! -d uploads ]; then
    echo "Creating uploads directory..."
    mkdir -p uploads
    echo "# This file ensures the uploads directory is created" > uploads/.gitkeep
    echo "Uploads directory created."
else
    echo "Uploads directory already exists, skipping..."
fi

# Set appropriate permissions
echo "Setting permissions..."
chmod 755 -R .
chmod 777 -R uploads
chmod 644 .env

echo "Checking for database..."
# This part requires mysql command-line client
# Uncomment and modify if you want to automate database creation
# mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS ptit_student_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
# mysql -u root -p ptit_student_management < db/schema.sql

echo ""
echo "Setup completed!"
echo ""
echo "Next steps:"
echo "1. Edit .env file with your database credentials"
echo "2. Import database schema: mysql -u username -p ptit_student_management < db/schema.sql"
echo "3. Configure your web server to point to this directory"
echo "4. Visit the website in your browser"
echo ""
echo "Default login:"
echo "- Admin: admin / password"
echo "- Student: B22CN123 / password" 
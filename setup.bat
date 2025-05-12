@echo off
echo Setting up PTIT Student Management System...

rem Create .env file if it doesn't exist
if not exist .env (
    echo Creating .env file from .env.example...
    copy .env.example .env
    echo Please edit the .env file with your database credentials.
) else (
    echo .env file already exists, skipping...
)

rem Create uploads directory if it doesn't exist
if not exist uploads\ (
    echo Creating uploads directory...
    mkdir uploads
    echo # This file ensures the uploads directory is created > uploads\.gitkeep
    echo Uploads directory created.
) else (
    echo Uploads directory already exists, skipping...
)

echo.
echo Setup completed!
echo.
echo Next steps:
echo 1. Edit .env file with your database credentials
echo 2. Import database schema using phpMyAdmin or MySQL CLI
echo 3. Configure your web server to point to this directory
echo 4. Visit the website in your browser
echo.
echo Default login:
echo - Admin: admin / password
echo - Student: B22CN123 / password

pause 
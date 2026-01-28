#!/bin/bash

# Start PHP built-in server dengan system PHP 8.3 (yang punya MongoDB)
# Server akan berjalan di http://localhost:8888

cd /opt/lampp/htdocs/coding-day-app

echo "================================"
echo "Starting PHP Server with MongoDB"
echo "================================"
echo ""
echo "Server running at: http://localhost:8888"
echo "Login page: http://localhost:8888"
echo ""
echo "Test Accounts:"
echo "  - PANITIA: panitia@codingday.com / admin123"
echo "  - JURI: juri1@codingday.com / juri123"
echo "  - PESERTA: leader1@team.com / password"
echo ""
echo "Press Ctrl+C to stop server"
echo ""

# Start server using system PHP 8.3 with MongoDB extension
/usr/bin/php -S localhost:8888 router.php

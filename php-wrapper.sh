#!/bin/bash

# Wrapper script untuk menjalankan PHP dengan MongoDB extension
# File ini menggunakan system PHP 8.3 yang sudah punya MongoDB extension

# Get the script filename from PHP_SELF or first argument
SCRIPT="${1:-.}"

# Export environment variables untuk PHP
export PATH="/usr/bin:$PATH"

# Execute dengan system PHP yang punya MongoDB
/usr/bin/php "$SCRIPT"

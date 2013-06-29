#!/bin/bash
cd "`dirname "$0"`/../web"
php -S ${1:-"localhost:9000"} app_logindev.php

<?php

require_once __DIR__ . '/../class/HtzoneApi.php';
require_once __DIR__ . '/../class/Category.php';
require_once __DIR__ . '/../class/Item.php';

// Set up test environment
putenv('APP_ENV=testing');
putenv('DB_PATH=:memory:'); 
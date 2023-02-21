<?php
Phar::mapPhar();

$basePath = 'phar://' . __FILE__ . '/';
require $basePath . 'vendor/autoload.php';

__HALT_COMPILER();

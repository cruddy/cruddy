<?php

/**
 * This is the entry file for a test application.
 */

require __DIR__.'/bootstrap/autoload.php';

$app = require_once __DIR__.'/bootstrap/start.php';

$app->run();

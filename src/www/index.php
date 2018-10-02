<?php
/**
 * @md
 *   SAMPLE base index file that includes all files from ./index.php.d directory
 */

$app = require '../bootstrap.php';

foreach (new \DirectoryIterator(__DIR__ . '/index.php.d') as $fileInfo) {
    if($fileInfo->isFile())
        require_once $fileInfo->getPathname();
}

$app->run();

<?php
/**
 * AmazonSimpleAdmin (ASA1)
 *
 * Autoloader for Amazon Creators API SDK
 *
 * Maps namespace Amazon\CreatorsAPI\v1 to this directory.
 * SDK has been modified to use AsaGuzzleHttp instead of GuzzleHttp.
 *
 * @author Timo Reith
 */

/**
 * Register the autoloader for Amazon Creators API SDK classes
 */
spl_autoload_register(function ($class) {
    // Only handle Amazon\CreatorsAPI namespace
    $prefix = 'Amazon\\CreatorsAPI\\v1\\';

    // Check if the class uses the prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relativeClass = substr($class, $len);

    // Base directory for the namespace
    $baseDir = __DIR__ . '/';

    // Replace namespace separators with directory separators
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require_once $file;
    }
});

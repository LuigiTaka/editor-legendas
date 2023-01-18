<?php
function my_autoloader($class_name) {
    // Replace the namespace with the file path
    $file_path = str_replace('EditorLegenda', __DIR__.DIRECTORY_SEPARATOR."src", $class_name);
    $file_path = str_replace('\\', DIRECTORY_SEPARATOR, $file_path);

    // Add the .php extension to the file path
    $file_path .= '.php';

    // Check if the file exists
    if (file_exists($file_path)) {
        // Include the file
        require_once $file_path;
    }
}
// Register the autoloader function
spl_autoload_register('my_autoloader');

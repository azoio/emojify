<?php
require_once('init.php');

$cfg = RegistryConfig::getInstance();

try {
    (new \Controllers\Form())
        ->processRequest();
}
catch (Exception $e) {
    if ($e->getCode() != 0) {
        http_response_code($e->getCode());
    }
    else {
        http_response_code(500);
    }
    echo nl2br(PHP_EOL . $e->getMessage());
}

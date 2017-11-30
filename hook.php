<?php
require __DIR__ . '/vendor/autoload.php';
$config = require(__DIR__ . '/config/config.php');

try {
    $telegram = \paulzi\ipcamera\Bot::init($config);
    $telegram->handle();
} catch (Exception $e) {
    \Longman\TelegramBot\TelegramLog::error($e);
    echo json_encode([
        'ok'     => true,
        'result' => true,
    ]);
}

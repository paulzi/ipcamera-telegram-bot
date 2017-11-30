<?php
require __DIR__ . '/vendor/autoload.php';
$config = require(__DIR__ . '/config/config.php');

try {
    $telegram = \paulzi\ipcamera\Bot::init($config);
    $telegram->watch();
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    \Longman\TelegramBot\TelegramLog::error($e);
} catch (Exception $e) {
    \Longman\TelegramBot\TelegramLog::error($e);
}

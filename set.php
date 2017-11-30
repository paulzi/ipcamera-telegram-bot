<?php
require __DIR__ . '/vendor/autoload.php';
$config = require(__DIR__ . '/config/config.php');

try {
    $telegram = new Longman\TelegramBot\Telegram($config['apiKey'], $config['name']);
    $result = $telegram->setWebhook($config['hookUrl']);
    if ($result->isOk()) {
        echo $result->getDescription();
    }
} catch (Longman\TelegramBot\Exception\TelegramException $e) {
    echo $e;
}
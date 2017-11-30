<?php
namespace paulzi\ipcamera;

use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use paulzi\ipcamera\db\Connection;

class Bot extends Telegram
{
    /**
     * @var Connection
     */
    public $db;

    /**
     * @var array
     */
    public $config;

    /**
     * @var string
     */
    protected $version = '0.1.0';


    /**
     * @param array $config
     * @return static
     */
    public static function init($config)
    {
        $bot = new static($config['apiKey'], $config['name']);
        $bot->config = $config;
        $bot->addCommandsPath(__DIR__ . '/Commands');
        $bot->setUploadPath(__DIR__ . '/../data');

        if (!empty($config['logs']['error'])) {
            TelegramLog::initErrorLog($config['logs']['error']);
        }
        if (!empty($config['logs']['debug'])) {
            TelegramLog::initDebugLog($config['logs']['debug']);
        }
        if (!empty($config['logs']['update'])) {
            TelegramLog::initUpdateLog($config['logs']['update']);
        }

        return $bot;
    }

    /**
     */
    public function watch()
    {
        $daemon = new Daemon($this);
        $daemon->run();
    }
}
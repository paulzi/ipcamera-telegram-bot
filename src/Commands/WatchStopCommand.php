<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use paulzi\ipcamera\Bot;
use paulzi\ipcamera\Pid;
use paulzi\ipcamera\UserCommand;

/**
 * @property Bot $telegram
 * @method \Longman\TelegramBot\Entities\Message getMessage()
 */
class WatchStopCommand extends UserCommand
{
    protected $name = 'watchstop';
    protected $description = 'Останавливает слежение';
    protected $usage = '/watchstop';
    protected $version = '0.0.1';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->checkUser()) {
            return $this->sendText('Вам не разрешено производить данную операцию');
        }

        $pid = new Pid('pid/bot.pid');
        if (!$pid->isRunning()) {
            return $this->sendText("\xE2\x9D\x8C Слежение не запущено");
        }

        $pid->kill();
        return $this->sendText("\xE2\x9C\x85 Слежение остановлено");
    }
}

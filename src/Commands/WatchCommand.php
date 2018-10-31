<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use paulzi\ipcamera\Bot;
use paulzi\ipcamera\Pid;
use paulzi\ipcamera\UserCommand;

/**
 * @property Bot $telegram
 * @method \Longman\TelegramBot\Entities\Message getMessage()
 */
class WatchCommand extends UserCommand
{
    protected $name = 'watch';
    protected $description = 'Запускает слежение';
    protected $usage = '/watch';
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
        if ($pid->check()) {
            return $this->sendText("\xE2\x9D\x8C Слежение уже запущено");
        }

        passthru('nohup php watch.php > /dev/null 2>&1 &', $result);
        if (!$result) {
            return $this->sendText("\xE2\x9C\x85 Слежение запущено");
        } else {
            return $this->sendText("\xE2\x9D\x8C Ошибка запуска");
        }
    }
}

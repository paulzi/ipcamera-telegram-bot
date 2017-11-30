<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use paulzi\ipcamera\Bot;
use paulzi\ipcamera\Pid;
use paulzi\ipcamera\UserCommand;

/**
 * @property Bot $telegram
 * @method \Longman\TelegramBot\Entities\Message getMessage()
 */
class StatusCommand extends UserCommand
{
    protected $name = 'status';
    protected $description = 'Проверяет статус слежения';
    protected $usage = '/status';
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
            $deviceStatus = file_get_contents($this->telegram->config['dataDir'] . '/device-status.json');
            $deviceStatus = json_decode($deviceStatus, true);
            $now    = time();
            $period = $this->telegram->config['checkMacsTimeout'];
            $text = "\xE2\x9C\x94 Слежение запущено (PID " . $pid->getPid() . ")";
            foreach ($deviceStatus as $name => $time) {
                if ($now - $time < $period) {
                    $text .= "\n\xF0\x9F\x8F\xA0 {$name} дома";
                } else {
                    $text .= "\n\xF0\x9F\x8F\x83 {$name} ушёл в " . date('H:i (Y-m-d)', $time);
                }
            }
            return $this->sendText($text);
        } else {
            return $this->sendText("\xE2\x9C\x96 Слежение остановлено");
        }
    }
}

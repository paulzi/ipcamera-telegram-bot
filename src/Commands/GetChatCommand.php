<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use paulzi\ipcamera\Bot;
use paulzi\ipcamera\UserCommand;

/**
 * @property Bot $telegram
 * @method \Longman\TelegramBot\Entities\Message getMessage()
 */
class GetChatCommand extends UserCommand
{
    protected $name = 'getchat';
    protected $description = 'Пишет id-чата';
    protected $usage = '/getchat';
    protected $version = '0.0.1';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->checkUser()) {
            return $this->sendText('Вам не разрешено производить данную операцию');
        }

        $chatId = $this->getMessage()->getChat()->getId();

        return $this->sendText("ID чата: {$chatId}");
    }
}

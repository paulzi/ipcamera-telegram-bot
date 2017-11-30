<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

/**
 * User "/help" command
 */
class HelpCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'help';
    protected $description = 'Показывает помощь';
    protected $usage = '/help или /help <команда>';
    protected $version = '1.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $command = trim($message->getText(true));

        //If no command parameter is passed, show the list
        if ($command === '') {
            $text = "*" . $this->telegram->getBotUsername() . "*\n\n";

            $text .= "*Управление слежением:*\n";
            $text .= "/photo - получить текущее изображение\n";
            $text .= "/photo *{номер}* - получить последнее изображение, под указаным номером с конца\n";
            $text .= "/video - получить текущие 10 секунд видео\n";
            $text .= "/video *{номер}* - получить последнее видео, под указаным номером с конца\n";
            $text .= "/watch - запускает слежение\n";
            $text .= "/watchstop - останавливает слежение\n";
            $text .= "/status - статус слежения\n";
            $text .= "\n";

            $text .= "*Вспомогательные команды:*\n";
            $text .= "/kb - включить клавиатуру быстрого набора команд\n";
            $text .= "/hidekb - отключить клавиатуру быстрого набора команд\n";
            $text .= "/getchat - получить ID чата\n";
            $text .= "\n";

            $text .= "Для подробного отображения использования конкретной команды используйте: /help `команда`";
        } else {
            /** @var Command[] $commands */
            $commands = array_filter($this->telegram->getCommandsList(), function ($command) {
                /** @var Command $command */
                return (!$command->isSystemCommand() && $command->isEnabled());
            });
            $command = str_replace('/', '', $command);
            if (isset($commands[$command])) {
                $command = $commands[$command];
                $text = 'Команда: ' . $command->getName() . ' v' . $command->getVersion() . "\n";
                $text .= 'Описание: ' . $command->getDescription() . "\n";
                $text .= 'Использование: ' . $command->getUsage();
            } else {
                $text = 'Команда /' . $command . ' не найдена';
            }
        }

        $data = [
            'chat_id'    => $chat_id,
            'text'       => $text,
            'parse_mode' => 'MARKDOWN',
        ];

        return Request::sendMessage($data);
    }
}

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

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Request;

/**
 * User "/hidekeyboard" command
 */
class HidekbCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'hidekb';
    protected $description = 'Скрывает клавиатуру быстрого доступа';
    protected $usage = '/hidekb';
    protected $version = '0.0.6';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $data = [
            'chat_id'      => $chat_id,
            'text'         => 'Клавиатура скрыта',
            'reply_markup' => Keyboard::remove(),
        ];

        return Request::sendMessage($data);
    }
}

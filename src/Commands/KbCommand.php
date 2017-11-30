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
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Entities\Keyboard;

/**
 * User "/keyboard" command
 */
class KbCommand extends UserCommand
{
    /**#@+
     * {@inheritdoc}
     */
    protected $name = 'kb';
    protected $description = 'Показывает клавиатуру быстрого доступа';
    protected $usage = '/kb';
    protected $version = '0.0.1';
    /**#@-*/

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        return Request::sendMessage([
            'chat_id'      => $chat_id,
            'text'         => 'Клавиатура включена',
            'reply_markup' => new Keyboard(
                [
                    'keyboard' => [['/watch', '/watchstop', '/status'], ['/photo', '/video']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                    'selective' => false
                ]
            ),
        ]);
    }
}

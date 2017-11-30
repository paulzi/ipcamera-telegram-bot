<?php
namespace paulzi\ipcamera;

use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Request;

trait CommandTrait
{
    /**
     * @return mixed
     */
    public function checkUser()
    {
        $list   = $this->telegram->config['users'];
        $chatId = $this->telegram->config['chatId'];

        $isInList = in_array($this->getMessage()->getFrom()->getUsername(), $list);
        $isInChat = $chatId == $this->getMessage()->getChat()->getId();

        return $isInList || $isInChat;
    }

    /**
     * @return string
     */
    protected function getUsername()
    {
        /** @var User $from */
        $from   = $this->getMessage()->getFrom();
        $result = $from->getUsername();
        if ($result) {
            return '@' . $result;
        }
        if ($from->getLastName() || $from->getFirstName()) {
            return implode(' ', [$from->getLastName(), $from->getFirstName()]);
        }
        return 'id' . $from->getId();
    }

    /**
     * @param string $text
     * @param array $options
     * @return mixed
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    protected function sendText($text, $options = [])
    {
        if (is_array($text)) {
            $text = $text[mt_rand(0, count($text) - 1)];
        }
        $message = $this->getMessage();
        return Request::sendMessage($options + [
                'chat_id'             => $message->getChat()->getId(),
                'reply_to_message_id' => $message->getMessageId(),
                'text'                => $text,
            ]);
    }
}
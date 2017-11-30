<?php
namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use paulzi\ipcamera\Bot;
use paulzi\ipcamera\UserCommand;

/**
 * @property Bot $telegram
 * @method \Longman\TelegramBot\Entities\Message getMessage()
 */
class PhotoCommand extends UserCommand
{
    protected $name = 'photo';
    protected $description = 'Получает текущее изображение с камеры';
    protected $usage = '/photo';
    protected $version = '0.0.1';

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        if (!$this->checkUser()) {
            return $this->sendText('Вам не разрешено производить данную операцию');
        }
        
        $index  = (int)trim($this->getMessage()->getText(true));
        if ($index) {
            $folder = $this->telegram->config['watchFolder'];
            $files  = glob("{$folder}/*.jpg");
            $index  = count($files) - $index;
            if ($index < 0) {
                return $this->sendText("\xF0\x9F\x86\x98 Нет такого фото");
            } else {
                $file = $files[$index];
                return Request::sendPhoto([
                    'chat_id' => $this->getMessage()->getChat()->getId(),
                    'caption' => basename($file),
                    'photo'   => Request::encodeFile($file),
                ]);
            }
        }

        $url = $this->telegram->config['photoUrl'];
        try {
            $ctx = stream_context_create([
                'http' => ['timeout' => 5],
            ]);
            $content = file_get_contents($url, null, $ctx);
            $file    = tempnam(sys_get_temp_dir(), 'ipcamerabot');
            file_put_contents($file, $content);
            Request::sendPhoto([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'caption' => date('Y-m-d H:i:s'),
                'photo'   => Request::encodeFile($file),
            ]);
            unlink($file);
            return Request::emptyResponse();
        } catch (TelegramException $e) {
            return $this->sendText('\xF0\x9F\x86\x98 Ошибка отправки фото');
        }
    }
}

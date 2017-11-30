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
class VideoCommand extends UserCommand
{
    protected $name = 'video';
    protected $description = 'Получает текущее видео с камеры';
    protected $usage = '/video';
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
            $files  = glob("{$folder}/*.mp4");
            $index  = count($files) - $index;
            if ($index < 0) {
                return $this->sendText("\xF0\x9F\x86\x98 Нет такого видео");
            } else {
                $file = $files[$index];
                return Request::sendVideo([
                    'chat_id' => $this->getMessage()->getChat()->getId(),
                    'caption' => basename($file),
                    'video'   => Request::encodeFile($file),
                ]);
            }
        }
        
        $url = $this->telegram->config['videoUrl'];
        try {
            $file   = tempnam(sys_get_temp_dir(), 'ipcamerabot') . '.mp4';
            passthru("avconv -rtsp_transport tcp -i {$url} -t 5 -an -vcodec copy {$file}");
            Request::sendVideo([
                'chat_id' => $this->getMessage()->getChat()->getId(),
                'caption' => date('Y-m-d H:i:s'),
                'video'   => Request::encodeFile($file),
            ]);
            unlink($file);
            return Request::emptyResponse();
        } catch (TelegramException $e) {
            return $this->sendText('\xF0\x9F\x86\x98 Ошибка отправки видео');
        }
    }
}

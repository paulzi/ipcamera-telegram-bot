<?php
namespace paulzi\ipcamera;

use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;

class Daemon
{
    /**
     * @var Bot
     */
    protected $bot;

    /**
     * @var Pid
     */
    protected $pid;

    /**
     * @var array
     */
    protected $existFiles;

    /**
     * @var array
     */
    protected $lastOnline = [];

    /**
     * @var array
     */
    protected $deviceStatus = [];


    /**
     * @param Bot $bot
     */
    public function __construct($bot)
    {
        $this->bot = $bot;
    }

    /**
     */
    public function run()
    {
        $this->pid = new Pid('pid/bot.pid');
        if ($this->pid->check()) {
            $this->warning('Слежение за папкой уже запущено');
        }
        $this->info('Запускаем слежение за папкой', true);
        $this->pid->start();
        try {
            $this->loop();
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }
        $this->pid->stop();
        $this->info('Завершение слежения за папкой', true);
    }

    /**
     */
    protected function loop()
    {
        $i = 0;
        while (true) {
            if ($i === 0) {
                $this->statMacs();
            }
            $this->checkFolder();
            $this->checkDeviceStatus();
            if (!$this->pid->check()) {
                break;
            }
            $i = ($i + 1) % $this->bot->config['checkMacsPeriod'];
            sleep(1);
        }
    }

    /**
     */
    protected function checkFolder()
    {
        $folder   = $this->bot->config['watchFolder'];
        $isInHome = $this->isInHome();

        // photo
        $files  = glob("{$folder}/*.jpg");
        $exists = $files;
        foreach ($files as $file) {
            if ($this->existFiles && !in_array($file, $this->existFiles)) {
                if ($this->checkFile($file)) {
                    if ($this->checkRecordVideo()) {
                        $this->recordVideo();
                    }
                    if ($isInHome) {
                        $this->sendText("\xF0\x9F\x9A\xAA Открыта дверь", true);
                    } else {
                        $this->sendFile($file);
                    }
                }
            }
        }

        // video
        $files  = glob("{$folder}/*.mp4");
        $exists = array_merge($exists, $files);
        foreach ($files as $file) {
            if ($this->existFiles && !in_array($file, $this->existFiles)) {
                if (!$isInHome) {
                    $this->sendFile($file);
                }
            }
        }

        $this->existFiles = $exists;
    }

    /**
     * @return array
     */
    protected function checkMacs()
    {
        $result = [];
        $macs = $this->bot->config['deviceMacs'];
        foreach ($macs as $name => $data) {
            $mac = $data['mac'];
            $ip  = $data['ip'];
            $output = exec("arp-scan -q --retry=3 --timeout=500 --numeric --destaddr={$mac} {$ip} | grep -oP --color=never \"{$mac}\"");
            $result[$name] = $output === $mac;
        }
        return $result;
    }

    /**
     */
    protected function statMacs()
    {
        $now = time();
        $status = $this->checkMacs();
        foreach ($status as $name => $online) {
            if ($online) {
                $this->lastOnline[$name] = $now;
            }
        }
    }

    /**
     * @return bool
     */
    protected function isInHome()
    {
        $period = $this->bot->config['checkMacsTimeout'];
        $now    = time();
        foreach ($this->lastOnline as $name => $time) {
            if ($now - $time < $period) {
                return true;
            }
        }
        return false;
    }

    /**
     */
    protected function checkDeviceStatus()
    {
        $inHome  = false;
        $isLeave = false;
        $period  = $this->bot->config['checkMacsTimeout'];
        $now     = time();
        foreach ($this->lastOnline as $name => $time) {
            $state = $now - $time < $period;
            if (isset($this->deviceStatus[$name])) {
                if ($state && !$this->deviceStatus[$name]) {
                    $this->sendText("\xF0\x9F\x8F\xA0 {$name} пришёл в " . date('H:i', $time), true);
                }
                if (!$state && $this->deviceStatus[$name]) {
                    $this->sendText("\xF0\x9F\x8F\x83 {$name} ушёл в " . date('H:i', $time), true);
                    $isLeave = true;
                }
            }
            $this->deviceStatus[$name] = $state;
            $inHome = $inHome || $state;
        }
        if ($isLeave && !$inHome) {
            $this->sendText("\xE2\x9A\xA0 дома никого нет", true);
        }
        file_put_contents($this->bot->config['dataDir'] . '/device-status.json', json_encode($this->lastOnline, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    /**
     * @return bool
     */
    protected function checkRecordVideo()
    {
        static $time;
        if (!$time || $time < time() - $this->bot->config['videoTime']) {
            $time = time();
            return true;
        }
        return false;
    }

    /**
     */
    protected function recordVideo()
    {
        $file   = tempnam(sys_get_temp_dir(), 'ipcamerabot') . '.mp4';
        $out    = "{$this->bot->config['watchFolder']}/vid_" . date('Y-m-d_H-i-s') . '.mp4';
        $ffmpeg = "avconv -rtsp_transport tcp -i {$this->bot->config['videoUrl']} -t {$this->bot->config['videoTime']} -an -vcodec copy {$file}";
        $mv     = "mv {$file} {$out}";
        passthru("nohup sh -c '{$ffmpeg} && {$mv}' > /dev/null 2>&1 &");
    }

    /**
     * @param string $file
     * @return bool
     */
    protected function checkFile($file)
    {
        $img = new \Imagick($file);
        $list = $this->bot->config['checkImageRegions'];
        $avg = [];
        $mid = [];
        foreach ($list as $i => $item) {
            $iterator = $img->getPixelRegionIterator($item[0], $item[1], $item[2], $item[3]);
            foreach ($iterator as $row) {
                foreach ($row as $pixel) {
                    $color = $pixel->getColor();
                    foreach ($color as $ch => $value) {
                        $avg[$i][$ch][] = $value;
                    }
                }
            }
            foreach ($avg[$i] as $ch => $values) {
                $avg[$i][$ch] = array_sum($values) / count($values);
                $mid[$ch][] = $avg[$i][$ch];
            }
        }
        foreach ($mid as $ch => $values) {
            $mid[$ch] = array_sum($values) / count($values);
        }
        foreach ($avg as $i => $color) {
            $sum = 0;
            foreach (['r', 'g', 'b'] as $ch) {
                $sum += pow($mid[$ch] - $avg[$i][$ch], 2);
            }
            $avg[$i] = sqrt($sum);
        }
        return max($avg) > $this->bot->config['checkImageThreshold'];
    }

    /**
     * @param string $text
     * @param bool $private
     */
    protected function sendText($text, $private = false)
    {
        Request::sendMessage([
            'chat_id' => $this->bot->config[$private ? 'privateChatId' : 'chatId'],
            'text'    => $text,
        ]);
    }

    /**
     * @param string $file
     * @param bool $private
     */
    protected function sendFile($file, $private = false)
    {
        try {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'mp4') {
                Request::sendVideo([
                    'chat_id' => $this->bot->config[$private ? 'privateChatId' : 'chatId'],
                    'caption' => basename($file),
                    'video'   => Request::encodeFile($file),
                ]);
            } else {
                Request::sendPhoto([
                    'chat_id' => $this->bot->config[$private ? 'privateChatId' : 'chatId'],
                    'caption' => basename($file),
                    'photo'   => Request::encodeFile($file),
                ]);
            }
        } catch (TelegramException $e) {
            $this->warning('Картинка не отправлена: ' . basename($file));
        }
    }

    /**
     * @param string $string
     * @param bool $log
     */
    public function info($string, $log = false)
    {
        echo $string . PHP_EOL;
        if ($log) {
            $this->log($string, 'info');
        }
    }

    /**
     * @param string $string
     */
    public function warning($string)
    {
        $out = "\033[33m{$string}\033[0m";
        echo $out . PHP_EOL;
        $this->log($string, 'warning');

        if (!empty($this->bot->config['chatId']) && !empty($this->bot->config['sendErrorsToTelegram'])) {
            Request::sendMessage([
                'chat_id' => $this->bot->config['privateChatId'],
                'text'    => "\xE2\x9A\xA0 Предупреждение парсера: {$string}",
            ]);
        }
    }

    /**
     * @param string $string
     */
    public function error($string)
    {
        $out = "\033[31m{$string}\033[0m";
        echo $out . PHP_EOL;
        $this->log($string, 'error');

        if (!empty($this->bot->config['chatId']) && !empty($this->bot->config['sendErrorsToTelegram'])) {
            Request::sendMessage([
                'chat_id' => $this->bot->config['privateChatId'],
                'text'    => "\xF0\x9F\x86\x98 Ошибка парсера: {$string}",
            ]);
        }
    }

    /**
     * @param string $string
     * @param string $level
     */
    public function log($string, $level)
    {
        if (!empty($this->bot->config['logs'])) {
            $string = date('Y-m-d H:i:s') . " [{$level}] {$string}" . PHP_EOL;
            file_put_contents($this->bot->config['logs'], $string, FILE_APPEND | LOCK_EX);
        }
    }
}
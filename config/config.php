<?php
return [

    // URL обработчика сообщений от Telegram
    'hookUrl' => 'https://example.com/ipcamera/hook.php',

    // API-ключ
    'apiKey' => 'enter_bot_api_here',

    // имя бота
    'name' => 'ipcamera_telegram_bot',

    // список разрешённых пользователей для команд (ники без @)
    'users' => [],

    // ID основного чата
    'chatId' => '-100000000',

    // ID чата для логов и debug-информацией
    'privateChatId' => '100000000',

    // URL получения изображения
    'photoUrl' => 'http://user:password@192.168.1.10/jpgimage/1/image.jpg',

    // URL видео-потока
    'videoUrl' => 'rtsp://user:password@192.168.1.10/1/h264major',

    // продолжительность записи видео по команде /video (сек)
    'videoTime' => 10,

    // папка для хранения данных
    'dataDir' => __DIR__ . '/../data',

    // папка для слежения
    'watchFolder' => '/home/local/ipcamera',

    // отправлять ошибки в приватный чат
    'sendErrorsToTelegram' => true,

    // интервал сканирования wi-fi (сек)
    'checkMacsPeriod' => 10,

    // таймаут отсутсвия устройств (сек)
    'checkMacsTimeout' => 180,

    // список wi-fi устройств, определяющих, что дома кто-то есть
    'deviceMacs' => [
        'User1' => [
            'mac' => 'aa:bb:cc:dd:ee:ff',
            'ip'  => '192.168.1.30',
        ],
    ],

    // порог отклонения значения
    'checkImageThreshold' => 30,

    // [x, y, w, h] - регионы для дополнительной проверки открытости двери
    'checkImageRegions' => [
        [513, 20, 10, 10],
        [556, 20, 10, 10],
        [585, 20, 10, 10],
        [705, 20, 10, 10],
        [750, 20, 10, 10],
        [800, 20, 10, 10],
        [840, 20, 10, 10],
        [869, 20, 10, 10],
        [900, 20, 10, 10],
        [913, 20, 10, 10],
    ],

    // логи
    'logs' => [
        'error'  => __DIR__ . '/../log/error.log',
        //'debug'  => __DIR__ . '/../log/debug.log',
        //'update' => __DIR__ . '/../log/update.log',
        'daemon' => __DIR__ . '/../log/daemon.log',
    ],
];
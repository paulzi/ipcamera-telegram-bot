# IPCamera Telegram Bot

## Install

1) Install OS dependencies:
 
```bash
sudo apt-get install avconv arp-scan php-imagick
```

2) Register bot via @BotFather (send SSL-certificate to BotFather if necessary)

3) Clone repository

```bash
git clone https://github.com/paulzi/ipcamera-telegram-bot.git
cd ipcamera-telegram-bot
```

4) Install dependencies via composer:

```bash
composer update
```

5) Change config in /config/config.php (hookUrl and apiKey is required)

6) Register hook:

```bash
php set.php
```

7) Send `/watch` to bot in chat

8) add `* * * * php watch.php` in CRON for auto-recovery daemon
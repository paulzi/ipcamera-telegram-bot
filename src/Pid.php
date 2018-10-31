<?php
namespace paulzi\ipcamera;

class Pid
{
    /**
     * @var string
     */
    protected $file;

    /**
     * @var resource
     */
    protected $fh;

    /**
     * @var bool
     */
    private $_started = false;


    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Проверяет, запущен ли процесс
     * @return bool
     */
    public function isRunning()
    {
        if ($this->_started) {
            return true;
        }
        $this->fh = fopen($this->file, 'c');
        if (!flock($this->fh, LOCK_EX | LOCK_NB)) {
            fclose($this->fh);
            return false;
        }
        fclose($this->fh);
        return true;
    }

    /**
     * Запускает процесс
     * @param bool $wait Ожидать завершения другого процесса
     * @return bool
     */
    public function start($wait = false)
    {
        $flags = $wait ? LOCK_EX : LOCK_EX | LOCK_NB;
        $this->fh = fopen($this->file, 'c');
        if (!flock($this->fh, $flags)) {
            fclose($this->fh);
            return false;
        }
        $this->_started = true;
        $pid = (string)getmypid();
        ftruncate($this->fh, 0);
        fwrite($this->fh, $pid, strlen($pid));
        fflush($this->fh);
        return true;
    }

    /**
     * Завершает текущий запущенный процесс
     * @return bool
     */
    public function stop()
    {
        if (!$this->_started) {
            return false;
        }
        fclose($this->fh);
        @unlink($this->file);
        $this->_started = false;
        return true;
    }

    /**
     * Завершает процесс запущенный в том числе в другом процессе
     * @param int $signal Тип отправляемого сигнала
     * @return bool
     */
    public function kill($signal = SIGTERM)
    {
        if ($this->_started) {
            return $this->stop();
        }
        $pid = $this->getPid();
        if (!$pid) {
            return false;
        }
        return posix_kill($pid, $signal);
    }

    /**
     * Возвращает pid запущенного процесса
     * @return int|false
     */
    public function getPid()
    {
        if ($this->_started) {
            return getmypid();
        }
        $pid = @file_get_contents($this->file);
        return $pid !== false ? (int)$pid : false;
    }
}
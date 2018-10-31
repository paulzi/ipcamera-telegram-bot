<?php
namespace paulzi\ipcamera;

class Pid
{
    /**
     * @var string
     */
    protected $file;


    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * @return bool
     */
    public function check()
    {
        if (!file_exists($this->file)) {
            return false;
        }
        $pid = (int)file_get_contents($this->file);
        if ($pid == getmypid()) {
            return true;
        }
        passthru("ps -p $pid > /dev/null", $result);
        return !$result;
    }

    /**
     */
    public function start()
    {
        file_put_contents($this->file, getmypid());
    }

    /**
     */
    public function stop()
    {
        if (file_exists($this->file)) {
            unlink($this->file);
        }
    }

    /**
     * @return int
     */
    public function getPid()
    {
        return (int)file_get_contents($this->file);
    }
}
<?php

namespace Md\Fs;

class FsException extends \Exception
{
    private string $function;
    private int $errno;
    private string $errstr;
    private string $errfile;
    private int $errline;

    public function __construct(
        $function,
        $errstr,
        $errno,
        $errfile,
        $errline,
    ) {
        $this->function = $function;
        $this->errno = $errno;
        $this->errstr = $errstr;
        $this->errfile = $errfile;
        $this->errline = $errline;

        $this->message = $this->__toString();
        parent::__construct($this, $errno);
    }

    public function getFunction(): string
    {
        return $this->function;
    }
    public function getErrno(): int
    {
        return $this->errno;
    }
    public function getErrstr(): string
    {
        return $this->errstr;
    }
    public function getErrfile(): string
    {
        return $this->errfile;
    }
    public function getErrline(): int
    {
        return $this->errline;
    }


    public function __toString(): string
    {
        return "FsException function " . $this->function . " error " . $this->errno . " : " . $this->errstr . " in " . $this->errfile . " at line " . $this->errline;
    }
}

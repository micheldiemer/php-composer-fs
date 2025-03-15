<?php

namespace Md\Fs;

class FsErrorManager
{
    public static bool $defaultErrorHandler = true;

    public static function setErrorHandler($restore = false)
    {
        if ($restore) {
            self::$defaultErrorHandler = true;
            restore_error_handler();
            return;
        }

        if (self::$defaultErrorHandler) {
            set_error_handler(['FsErrorManager', 'warning_handler'], E_WARNING);
            self::$defaultErrorHandler = false;
        }
    }


    public static function warning_handler(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ) {

        throw new FsException(
            'warning_handler',
            $errstr,
            $errno,
            $errfile,
            $errline
        );
    }

    /*
function warning_handler(
    int $errno,
    string $errstr,
    string $errfile,
    int $errline,
) {
    global $foreach_rm_error;
    $foreach_rm_error = 1;
    echo "<span style='color: white; background-color: red;'>";
    echo "<b>Warning: </b>$errstr";
    echo "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;in <b>$errfile</b> on line <b>$errline</b>";
    echo "</span><br>";
}
*/
}

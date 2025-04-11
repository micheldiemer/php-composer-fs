<?php

namespace Md\Fs;

class FsErrorManager
{
    public static bool $defaultErrorHandler = true;
    public static $prevErrorHandler;
    public static $nb = 0;


    public static function setErrorHandler($restore = false)
    {
        // echo "setErrorHandler: " . ($restore ? "restore" : "set") . " nb= " . self::$nb . PHP_EOL;
        if ($restore) {
            if (self::$nb > 0) {
                self::$nb--;
            }
            if (self::$nb == 0 && !self::$defaultErrorHandler) {
                self::$defaultErrorHandler = restore_error_handler();
                self::$prevErrorHandler = null;
                // set_error_handler(self::$prevErrorHandler);

                // $errstr = var_export(self::$prevErrorHandler, true);
                // $d = substr($errstr, 0, strpos($errstr, "\n"));
                // echo "RESTORED : " . $d . PHP_EOL;
            }
            // echo "setErrorHandler ret: " . ($restore ? "restore" : "set") . " nb= " . self::$nb . PHP_EOL;;
            return;
        } else {
            self::$nb++;
            if (self::$nb == 1 && self::$defaultErrorHandler !== false) {
                self::$prevErrorHandler = \set_error_handler([__NAMESPACE__ . '\FsErrorManager', 'warning_handler'], E_WARNING);
                self::$defaultErrorHandler = false;

                // $errstr = var_export(self::$prevErrorHandler, true);
                // $d = substr($errstr, 0, strpos($errstr, "\n"));
                // echo $d . PHP_EOL;
                // echo "setErrorHandler ret: " . ($restore ? "restore" : "set") . " nb= " . self::$nb . PHP_EOL;;
            }
        }
    }


    public static function warning_handler(
        int $errno,
        string $errstr,
        string $errfile,
        int $errline,
    ) {
        self::setErrorHandler(true);
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

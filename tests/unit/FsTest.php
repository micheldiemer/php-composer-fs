<?php

declare(strict_types=1);

define('PHPUNIT_RUNNING', 1);
define('PHPUNIT_RUNNING_TEST_ERRORS', 0);

require_once __DIR__ . '/../../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Md\Fs\Fs;


final class FsTest extends TestCase
{


    public function testRemoveDir(): void
    {
        $i = 1000;

        $sysTemp = sys_get_temp_dir();
        if (!is_writable($sysTemp)) {
            throw new Exception("Temp directory $sysTemp is not writable");
        }
        $a  = bin2hex(random_bytes(16));
        while (is_readable($temp = "$sysTemp/$a") && $i-- > 0) {
            $a  = bin2hex(random_bytes(16));
        }
        if (is_readable("$sysTemp/$a")) {
            throw new Exception("Failed to create a unique file name");
        }
        mkdir($temp);

        for ($i = 0; $i < 10; $i++) {
            mkdir("$temp/$i");
            for ($j = 0; $j < 10; $j++) {
                mkdir("$temp/$i/$j");
                for ($k = 0; $k < 10; $k++) {
                    touch("$temp/$i/$j/$k");
                    // for ($l = 0; $l < 10; $l++) {
                    //     touch("$temp/$i/$j/$k/$l");
                    // }
                }
            }
        }


        Fs::removeDir($temp);

        $this->assertFalse(is_readable($temp));
    }
}

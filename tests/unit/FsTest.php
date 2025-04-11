<?php

declare(strict_types=1);

define('PHPUNIT_RUNNING', 1);
define('PHPUNIT_RUNNING_TEST_ERRORS', 1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Md\Fs\Fs;
use Md\Fs\FsException;
use PHPUnit\Framework\TestCase;

final class FsTest extends TestCase
{
    private array $dirs;
    private array $files;
    private array $txtFiles;
    const NB_FILES_PER_DIR = 5;

    protected function setUp(): void
    {
        $tmp = sys_get_temp_dir() . "/fsTest";
        $files = [];
        $dirs = [];
        $txtFiles = [];
        $i = 0;
        while (is_dir($tmp) || is_readable($tmp)) {
            $i++;
            $tmp = sys_get_temp_dir() . "/fsTest" . $i;
        }

        $dirs[] = $tmp;
        $dirs[] = $tmp . "/fsTest";
        $dirs[] = $tmp . "/fsTest/dir1";
        $dirs[] = $tmp . "/fsTest/dir1/dir2";

        $nb = 0;
        foreach ($dirs as $dir) {
            mkdir($dir, 0777, true);

            for ($i = 1; $i <= self::NB_FILES_PER_DIR; $i++) {
                $nb++;
                $file = $dir . "/file" . $i;
                $files[] = $file;
                $files[] = $file . '.txt';
                $txtFiles[] = $file . '.txt';
                file_put_contents($file, $nb);
                file_put_contents($file . '.txt', $nb);
            }
        }
        $this->dirs = $dirs;
        $this->files = $files;
        $this->txtFiles = $txtFiles;
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        foreach (array_reverse($this->dirs) as $dir) {
            if (is_dir($dir)) {
                rmdir($dir);
            }
        }
    }

    public function testCheckDir(): void
    {
        $this->expectException(FsException::class);
        $reflection = new ReflectionClass(Fs::class);
        $method = $reflection->getMethod('checkDir');
        $method->setAccessible(true);
        $method->invoke(null, $this->dirs[0] . 'x', __FUNCTION__, __LINE__);
    }

    public function testIn(): void
    {
        // $it = Fs::in($this->dirs[0], false, ['.txt']);
        // echo PHP_EOL;
        // $i = 0;
        // foreach ($it as $file) {
        //     $i++;
        //     if ($file->isDir()) {
        //         echo "d ";
        //     }
        //     echo "$i ";
        //     echo $file->getPathname() . "\n";
        // }

        $it = Fs::in($this->dirs[0], false, []);
        $this->assertInstanceOf(Generator::class, $it);
        $this->assertCount(2 * self::NB_FILES_PER_DIR + 1, iterator_to_array($it));

        $it = Fs::in($this->dirs[0], true, []);
        $this->assertInstanceOf(Generator::class, $it);
        $this->assertCount(count($this->files), iterator_to_array($it));

        $it = Fs::in($this->dirs[0], true, ['.txt']);
        $this->assertInstanceOf(Generator::class, $it);
        $this->assertCount(count($this->files) / 2, iterator_to_array($it));


        $it = Fs::in($this->dirs[0], false, ['.txt']);
        $this->assertInstanceOf(Generator::class, $it);
        $this->assertCount(self::NB_FILES_PER_DIR, iterator_to_array($it));
    }


    // #[WithoutErrorHandler]
    // public function testtestW(): void
    // {
    //     // FS::removeAllFiles($this->dirs[0] . 'x');
    //     // $this->expectWarning();

    //     $testFile = $this->dirs[0] . 'x';
    //     FS::testW($testFile);
    //     $lastError = error_get_last();
    //     $this->assertEquals(2, $lastError['type']);
    //     $this->assertEquals('unlink(' . $testFile . '): No such file or directory', $lastError['message']);
    // }


    #[WithoutErrorHandler]
    public function testRemoteAllFiles()
    {
        $this->expectException(FsException::class);
        $this->expectExceptionMessageMatches("/rmdir.*Directory not empty/");
        Fs::removeAllFiles($this->dirs[0], $this->txtFiles);

        Fs::removeAllFiles($this->dirs[0]);
        $it = Fs::in($this->dirs[0], false, []);
        $this->assertInstanceOf(Generator::class, $it);
        $this->assertCount(1, iterator_to_array($it));
    }

    #[WithoutErrorHandler]
    public function testRemoteDir()
    {
        $this->expectException(FsException::class);
        $this->expectExceptionMessageMatches("/rmdir.*Directory not empty/");
        Fs::removeDir($this->dirs[0], $this->txtFiles);

        Fs::removeDir($this->dirs[0]);
        this->assertFalse(is_dir($this->dirs[0]));
    }
}

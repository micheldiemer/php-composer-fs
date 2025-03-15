<?php

namespace Md\Fs;

//TODO LoggerInterface Dependency Injection

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Fs
{
    private static LoggerInterface|null $logger;

    public static function setLogger(LoggerInterface|null $logger)
    {
        self::$logger = $logger;
    }

    private static function checkDir(string $path, string $function, int $line)
    {
        if (! is_dir($path) || ! is_readable($path)) {
            throw new FsException(
                $function,
                "{$path} is not a readable directory",
                1,
                __FILE__,
                $line
            );
        }
    }


    public static function in(string $path, bool $Recursive = true, array $exts = []): \Generator
    {
        self::checkDir($path, __FUNCTION__, __LINE__);

        $it = new \RecursiveDirectoryIterator($path);
        if ($Recursive) {
            $it = new \RecursiveIteratorIterator($it);
        }

        if (count($exts) > 0):
            $it = new \RegexIterator($it, '/(' . implode('|', $exts) . ')$/i', \RegexIterator::MATCH);
        endif;

        yield from $it;
    }

    public static function removeDir(string $dir, $exclude = []): void
    {

        self::removeAllFiles($dir, $exclude);

        FsErrorManager::setErrorHandler();

        self::$logger?->info(
            "suppression du répertoire $dir {fonction} {ligne}",
            ['fonction' => __FUNCTION__, 'ligne' => __LINE__]
        );
        rmdir($dir);
        FsErrorManager::setErrorHandler(true);
    }

    public static function findOne(string $dir, string $filename)
    {
        self::checkDir($dir, __FUNCTION__, __LINE__);

        $files = \iterator_to_array(self::in($dir, true, [$filename]));

        if (!is_array($files)):
            throw new FsException(
                __FUNCTION__,
                "fichier $filename non trouvé",
                1,
                __FILE__,
                __LINE__
            );
        endif;

        if (count($files) != 1) {
            throw new FsException(
                __FUNCTION__,
                "fichier $filename trouvé " . count($files) . " fois au lieu d'une seule",
                1,
                __FILE__,
                __LINE__
            );
        }

        return array_key_first($files);
    }


    public static function removeAllFiles(string $dir, $exclude = []): void
    {
        $log = self::$logger ?? new NullLogger();
        self::checkDir($dir, __FUNCTION__, __LINE__);

        FsErrorManager::setErrorHandler();

        #echo "Itération sur $dir" . PHP_EOL . '<br>';
        $it = new \RecursiveDirectoryIterator(
            $dir,
            \RecursiveDirectoryIterator::SKIP_DOTS
        );
        $files = new \RecursiveIteratorIterator(
            $it,
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        $log->debug(
            "boucle foreach{fonction} {ligne}",
            ['fonction' => __FUNCTION__, 'ligne' => __LINE__]
        );

        $nb = 0;

        foreach ($files as $file):
            $nb++;
            $letter  = '?';
            if ($file->isFile()) {
                $letter  = 'f';
            }
            if ($file->isLink()) {
                $letter  = 'l';
            }
            if ($file->isDir()) {
                $letter  = 'd';
            }
            if ($file->getBasename() == '.' || $file->getBasename() == '..') {
                $letter .= '.';
            }
            if ($file->getBasename()[0] == '.') {
                $letter =

                    $letter .= "_";
            }
            if ($file->isReadable()) {
                $letter .= 'r';
            }
            if ($file->isWritable()) {
                $letter .= 'w';
            }
            if ($file->isExecutable()) {
                $letter .= 'x';
            }


            if ($letter[0] != 'f' || $nb < 50):
                $log->info(
                    "suppression $letter " . $file->getPathname() . "{fonction} {ligne}",
                    ['fonction' => __FUNCTION__, 'ligne' => __LINE__]
                );
            endif;
            if ($file->isLink()):
                unlink($file->getPathname());
                continue;
            endif;
            if (in_array($file->getPathname(), $exclude)):
                $log->info(
                    "exclusion " . $file->getPathname() . "{fonction} {ligne}",
                    ['fonction' => __FUNCTION__, 'ligne' => __LINE__]
                );
                continue;
            endif;
            if ($file->isDir()):
                // echo $file->getPathname();
                // if($root || $files->hasnext())
                rmdir($file->getPathname());
            else:
                unlink($file->getPathname());

            endif;
        endforeach;
        $log->debug(
            "fin boucle foreach {fonction} {ligne}",
            ['fonction' => __FUNCTION__, 'ligne' => __LINE__]
        );

        FsErrorManager::setErrorHandler(true);
    }


    public static function grep($file, $search, $nb = -1): array
    {
        $matches = [];
        $line = 0;

        $handle = fopen($file, "r");
        if ($handle) {
            while (!feof($handle)) {
                $buffer = fgets($handle);
                $line++;
                if (mb_strpos($buffer, $search) !== false):
                    $matches[] = ['line' => $line, 'text' => $buffer];
                    $nb--;
                    if ($nb == 0) {
                        break;
                    }
                endif;
            }
            fclose($handle);
        }

        return $matches;
    }
}

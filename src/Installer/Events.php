<?php

namespace EnderLab\Installer;

use Composer\IO\IOInterface;
use Composer\Script\Event;

class Events
{
    private static $directories = [
        'app',
        'bin',
        'config',
        'public',
        'public/js',
        'public/css',
        'tests',
        'tmp',
        'tmp/log',
        'tmp/cache'
    ];

    private static $templateFile = [
        'Template/config.php' => 'config/config.php',
        'Template/index.php'  => 'public/index.php',
        'Template/router.php' => 'config/router.php'
    ];

    public static function postCreateProject(Event $event)
    {
        $vendorDir = $event->getComposer()->getConfig()->get('vendor-dir') . '/';
        $rootDir = $vendorDir . '../';

        // Step 1: build directory tree
        self::createDirectories($event->getIO(), $rootDir, $event->isDevMode());

        // Step 2: create config file
        self::createConfigFiles($event->getIO(), $rootDir, $event->isDevMode());

        $event->getIO()->write('Create project down.');
    }

    private static function createDirectories(IOInterface $io, string $rootDir, bool $verbose = true)
    {
        foreach (self::$directories as $directory) {
            mkdir($rootDir . $directory);

            if (true === $verbose) {
                $io->write('Create directory "' . $rootDir . $directory . '".');
            }
        }
    }

    private static function createConfigFiles(IOInterface $io, $rootDir, bool $verbose = true)
    {
        foreach (self::$templateFile as $source => $dest) {
            copy(__DIR__ .'/'. $source, $rootDir . $dest);

            if (true === $verbose) {
                $io->write('Create file "' . $dest . '".');
            }
        }
    }
}

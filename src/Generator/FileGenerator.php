<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Nette\PhpGenerator\PsrPrinter;
use Nette\Neon\Neon;

class FileGenerator
{
    public const FACADES_CONFIG = 'app/config/app/facades.neon';
    public const CONTROLS_CONFIG = 'app/config/app/controls.neon';
    public const FORMS_CONFIG = 'app/config/app/forms.neon';

    public static function writeFile($files) {
        $printer = new PsrPrinter;

        foreach ($files as $file) {
            if ($file->entity) {
                if (!self::folderExist($file->directory)) {
                    mkdir($file->directory);
                }
            }
            file_put_contents($file->directory . $file->filename, $printer->printFile($file->contents));

            if (stripos($file->filename, 'Facade') !== false) {
                self::addService($file->filename, self::FACADES_CONFIG, 'App\Model\Database\Facade\\' . str_replace('.php', '', $file->filename));
            } else if (stripos($file->filename, 'FormControlFactory') !== false) {
                self::addService($file->filename, self::CONTROLS_CONFIG, 'App\UI\\'.($file->entity ?? '').'\\'. str_replace('.php', '', $file->filename));
            } else if (stripos($file->filename, 'FormFactory') !== false) {
                self::addService($file->filename, self::FORMS_CONFIG, 'App\UI\\'.($file->entity ?? '').'\\'. str_replace('.php', '', $file->filename));
            }
        }
    }

    private static function folderExist($folder)
    {
        // Get canonicalized absolute pathname
        $path = realpath($folder);

        // If it exist, check if it's a directory
        return ($path !== false AND is_dir($path)) ? $path : false;
    }

    private static function addService(string $filename, string $config, string $class)
    {
        $neon = Neon::decodeFile($config);
        $neon['services'][lcfirst(str_replace('.php', '', $filename))] = [
            'class' => $class,
            'inject' => true,
        ];
        file_put_contents('nette.safe://'.$config, Neon::encode($neon, Neon::BLOCK));
    }
}

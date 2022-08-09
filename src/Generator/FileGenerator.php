<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Nette\PhpGenerator\PsrPrinter;
use Nette\Neon\Neon;
use Nette\PhpGenerator\Printer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class FileGenerator
{
    public const FACADES_CONFIG = 'app/config/app/facades.neon';
    public const CONTROLS_CONFIG = 'app/config/app/controls.neon';
    public const FORMS_CONFIG = 'app/config/app/forms.neon';

    public static function writeFile(...$files)
    {
        $printer = new PsrPrinter;

        if (is_array($files)) {
            foreach ($files as $file) {
                self::write($file, $printer);
            }
        } else {
            self::write($files, $printer);
        }
    }

    private static function write(FileObject $file, Printer $printer)
    {
        if (isset($file->entity) && $file->entity) {
            if (!self::folderExist($file->directory)) {
                mkdir($file->directory, 0777, true);
            }
        }
        file_put_contents($file->directory . $file->filename, $printer->printFile($file->contents));

        if (stripos($file->filename, 'Facade') !== false) {
            self::addService($file->filename, self::FACADES_CONFIG, 'App\Model\Database\Facade\\' . str_replace('.php', '', $file->filename));
        } else if (stripos($file->filename, 'FormControlFactory') !== false) {
            self::addService($file->filename, self::CONTROLS_CONFIG, 'App\UI\Control\\'.($file->entity ?? '').'\\'. str_replace('.php', '', $file->filename), 'implement');
        } else if (stripos($file->filename, 'FormFactory') !== false) {
            self::addService($file->filename, self::FORMS_CONFIG, 'App\UI\Form\\'.($file->entity ?? '').'\\'. str_replace('.php', '', $file->filename), 'implement');
        }
    }

    public static function folderExist($folder)
    {
        $path = realpath($folder);

        return ($path !== false AND is_dir($path)) ? $path : false;
    }

    private static function addService(string $filename, string $config, string $class, string $type = 'class')
    {
        preg_match('/.+\.(yaml|yml|neon)/', $config, $extension);

        switch ($extension[1]) {
            case 'yaml':
            case 'yml':
                $yaml = Yaml::parseFile($config);
                $yaml['services'][lcfirst(str_replace('.php', '', $filename))] = [
                    $type => $class,
                    'inject' => true,
                ];
                file_put_contents('nette.safe://'.$config, Yaml::dump($yaml));
                break;
            case 'neon':
                $neon = Neon::decodeFile($config);
                $neon['services'][lcfirst(str_replace('.php', '', $filename))] = [
                    $type => $class,
                    'inject' => true,
                ];
                file_put_contents('nette.safe://'.$config, Neon::encode($neon, Neon::BLOCK));
                break;
        }
    }
}

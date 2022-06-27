<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Nette\PhpGenerator\PsrPrinter;
use Nette\Neon\Neon;

class FileGenerator
{
    public const FACADES_CONFIG = 'app/config/app/facades.neon';

    public static function writeFile(FileObject ...$files) {
        $printer = new PsrPrinter;

        foreach ($files as $file) {
            file_put_contents($file->directory . $file->filename, $printer->printFile($file->contents));

            if (stripos($file->filename, 'Facade') !== false) {
                $neon = Neon::decodeFile(self::FACADES_CONFIG);
                $neon['services'][lcfirst($file->filename)] = [
                    'class' => 'App\Model\Database\Facade\\' . $file->filename,
                    'inject' => true,
                ];
                file_put_contents('nette.safe://'.self::FACADES_CONFIG, Neon::encode($neon, Neon::BLOCK));
            }
        }
    }
}

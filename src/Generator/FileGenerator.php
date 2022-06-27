<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Nette\PhpGenerator\PsrPrinter;

class FileGenerator
{
    public static function writeFile(FileObject ...$files) {
        $printer = new PsrPrinter;

        foreach ($files as $file) {
            file_put_contents($file->directory . $file->filename, $printer->printFile($file->contents));
        }
    }
}

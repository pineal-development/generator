<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Nette\PhpGenerator\PhpFile;

class FileObject
{
    public PhpFile $contents;

    public string $filename;

    public string $directory;

    public function __construct(string $directory, string $filename, PhpFile $contents) {
        $this->filename = $filename . '.php';
        $this->contents = $contents;
        $this->directory = $directory;
    }
}

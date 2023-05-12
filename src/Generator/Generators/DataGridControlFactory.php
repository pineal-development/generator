<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class DataGridControlFactory
{
    public const DIR_PATH = 'app/ui/Control/';

    public static function generate(string $name, string $entity): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Control' . "\\$entity");
        $namespace->addUse('App\UI\Control'."\\$entity".ucfirst($name).'DataGridControl');

        $interface = $namespace->addInterface($name.'DataGridControlFactory');

        $interface->addMethod('create')
            ->setPublic()
            ->setReturnType('App\UI\Control'."\\$entity".ucfirst($name).'DataGridControl');

        return new FileObject(self::DIR_PATH . "$entity/", $name.'DataGridControlFactory', $file, $entity);
    }
}

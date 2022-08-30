<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class FormControlFactory
{
    public const DIR_PATH = 'app/ui/Control/';

    public static function generate(string $name, ?string $entity = null): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Control' . ($entity ? "\\$entity" : ''));
        $namespace->addUse('App\UI\Control'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'FormControl');

        $interface = $namespace->addInterface($name.'FormControlFactory');

        $interface->addMethod('create')
            ->setPublic()
            ->setReturnType('App\UI\Control'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'FormControl');

        return new FileObject(self::DIR_PATH . ($entity ? "$entity/" : ''), $name.'FormControlFactory', $file, $entity);
    }
}

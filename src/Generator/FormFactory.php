<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class FormFactory
{
    public const DIR_PATH = 'app/ui/Form/';

    public static function generate(string $name, ?string $entity = null): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Form' . ($entity ? "\\$entity" : ''));
        $namespace->addUse('App\UI\Form'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'Form');

        $interface = $namespace->addInterface($name.'FormFactory');

        $interface->addMethod('create')
            ->setPublic()
            ->setReturnType('App\UI\Form'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'Form');

        return new FileObject(self::DIR_PATH . ($entity ? "$entity/" : ''), $name.'FormFactory', $file, $entity);
    }
}

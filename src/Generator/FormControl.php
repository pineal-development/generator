<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class FormControl
{
    public const DIR_PATH = 'app/ui/Control/';

    public static function generate(string $name, ?string $entity = null): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Control' . ($entity ? "\\$entity" : ''));
        $namespace->addUse('App\Model\Database\Entity\\'.$name);
        $namespace->addUse('App\Model\Database\Repository\\'.$name.'Repository');
        $namespace->addUse(self::getFullClass($name, $entity));
        $namespace->addUse(self::getFullClass($name, $entity, true));
        $namespace->addUse('App\UI\Control\BaseControl');

        $class = $namespace->addClass($name.'FormControl')
            ->setFinal()
            ->setExtends('App\UI\Control\BaseControl');

        $class->addProperty(lcfirst($name.'FormFactory'))
            ->setType(self::getFullClass($name, $entity, true))
            ->addComment("@var {$name}FormFactory @inject");

        $class->addProperty(lcfirst($name.'Form'))
            ->setType(self::getFullClass($name, $entity))
            ->addComment("@var {$name}Form");

        $class->addMethod('createComponent'.$name.'Form')
            ->setReturnType(self::getFullClass($name, $entity))
            ->addBody('$this->'.lcfirst($name).'Form = $this->'.lcfirst($name).'FormFactory->create();')
            ->addBody('$this->'.lcfirst($name).'Form->onValidate[] = [$this, \'validate'.$name.'Form\'];')
            ->addBody('$this->'.lcfirst($name).'Form->onSuccess[] = [$this, \'process'.$name.'Form\'];')
            ->addBody('return $this->'.lcfirst($name).'Form;');

        $validateMethod = $class->addMethod('validate'.$name.'Form')
            ->setReturnType('void');
        $validateMethod->addParameter('form')
            ->setType(self::getFullClass($name, $entity));
        $validateMethod->addParameter('values')
            ->setType('array');

        $successMethod = $class->addMethod('process'.$name.'Form')
            ->setReturnType('void')
            ->addBody('$this->onSuccess();');
        $successMethod->addParameter('form')
            ->setType(self::getFullClass($name, $entity));
        $successMethod->addParameter('values')
            ->setType('array');

        $class->addMethod('render')
            ->setReturnType('void')
            ->addBody('$this->template->setTranslator($this->translator);')
            ->addBody('$this->template->setFile(dirname($this->getReflection()->getFileName()) . \'/templates/'.lcfirst($name).'FormControl.latte\');')
            ->addBody('$this->template->render();');

        return new FileObject(self::DIR_PATH . ($entity ? "$entity/" : ''), $name.'FormControl', $file, $entity);
    }

    private static function getFullClass(string $name, ?string $entity = null, bool $factory = false)
    {
        return 'App\UI\Form'.($entity ? "\\$entity\\" : '\\').ucfirst($name).($factory ? 'FormFactory' : 'Form');
    }
}

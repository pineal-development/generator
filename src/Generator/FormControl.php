<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class FormControl
{
    public const DIR_PATH = 'app/ui/Control/';

    public static function generate(string $name, ?string $entity = null)
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Control' . ($entity ? "\\$entity" : ''));
        $namespace->addUse('App\Model\Database\Entity\\'.$name);
        $namespace->addUse('App\Model\Database\Repository\\'.$name.'Repository');

        $class = $namespace->addClass($name.'FormControl')
            ->setFinal()
            ->setExtends('App\UI\Control\BaseControl');

        $class->addProperty(lcfirst($name.'FormFactory'))
            ->setType('App\UI\Form\\'.ucfirst($name).'FormFactory')
            ->addComment("@var {ucfirst($name)}FormFactory @inject");

        $class->addProperty(lcfirst($name.'Form'))
            ->setType('App\UI\Form\\'.ucfirst($name).'Form')
            ->addComment("@var {ucfirst($name)}Form");

        $class->addMethod('createComponent'.$name.'Form')
            ->setReturnType($name.'Form')
            ->addBody('$this->'.lcfirst($name).'Form = $this->'.lcfirst($name).'FormFactory->create();')
            ->addBody('$this->'.lcfirst($name).'Form->onValidate[] = [$this, \'validate'.$name.'Form\'];')
            ->addBody('$this->'.lcfirst($name).'Form->onSuccess[] = [$this, \'process'.$name.'Form\'];')
            ->addBody('return $this->'.lcfirst($name).'Form;');

        $validateMethod = $class->addMethod('validate'.$name.'Form')
            ->setReturnType('void');
        $validateMethod->addParameter('form')
            ->setType('App\UI\Form'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'Form');
        $validateMethod->addParameter('values')
            ->setType('array');

        $successMethod = $class->addMethod('process'.$name.'Form')
            ->setReturnType('void')
            ->addBody('return $this->onSuccess();');
        $successMethod->addParameter('form')
            ->setType('App\UI\Form'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'Form');
        $successMethod->addParameter('values')
            ->setType('array');

        $class->addMethod('render')
            ->setReturnType('void')
            ->addBody('$this->template->setTranslator($this->translator);')
            ->addBody('$this->template->setFile(dirname($this->getReflection()->getFileName()) . \'/templates/'.lcfirst($name).'FormControl.latte\');')
            ->addBody('$this->template->render();');

        return new FileObject(self::DIR_PATH . ($entity ? "$entity/" : ''), $name.'FormControl', $file);
    }
}

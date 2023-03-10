<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileGenerator;
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
        if ($entity) {
            $namespace->addUse('App\Model\Database\Entity\\'.$entity);
            $namespace->addUse('App\Model\Database\Repository\\'.$entity.'Repository');
            $namespace->addUse('App\Model\Database\Facade\\'.$entity.'Facade');
        }
        $namespace->addUse(self::getFullClass($name, $entity));
        $namespace->addUse(self::getFullClass($name, $entity, true));
        $namespace->addUse('App\UI\Control\BaseControl');
        $namespace->addUse('Ublaboo\DataGrid\DataGrid');

        $class = $namespace->addClass($name.'DataGridControl')
            ->setExtends('App\UI\Control\BaseControl');

        if ($entity) {
            $class->addProperty(lcfirst($entity.'Facade'))
                ->setType('App\Model\Database\Facade\\'.$entity.'Facade')
                ->addComment("@var {$entity}Facade @inject");
        }

        $class->addMethod('createComponent'.$name.'DataGrid')
            ->setReturnType('Ublaboo\DataGrid\DataGrid')
            ->addBody('$this->'.lcfirst($name).'Form = $this->'.lcfirst($name).'FormFactory->create();')
            ->addBody('$this->'.lcfirst($name).'Form->onValidate[] = [$this, \'validate'.$name.'Form\'];')
            ->addBody('$this->'.lcfirst($name).'Form->onSuccess[] = [$this, \'process'.$name.'Form\'];')
            ->addBody('return $this->'.lcfirst($name).'Form;')
            ->addParameter('name')
                ->setType('string');

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

    public static function generateTemplate(string $name, ?string $entity = null): void
    {
        $dir = self::DIR_PATH.($entity ? "$entity/" : '/').'templates/';
        if (!FileGenerator::folderExist($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . lcfirst($name.'FormControl.latte'), '{form ' . lcfirst($name.'Form') . '}'.PHP_EOL.'{/form}'.PHP_EOL);
    }

    private static function getFullClass(string $name, string $entity = null)
    {
        return 'App\UI\Control'.($entity ? "\\$entity\\" : '\\').ucfirst($name).'DataGridControl';
    }
}

<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class DataGridControl
{
    public const DIR_PATH = 'app/ui/Control/';

    public static function generate(string $name, string $entity): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\UI\Control' . "\\$entity");
        $namespace->addUse('App\Model\Database\Facade\\'.$entity.'Facade');
        $namespace->addUse('App\UI\Control\BaseControl');
        $namespace->addUse('Ublaboo\DataGrid\DataGrid');

        $class = $namespace->addClass($name.'DataGridControl')
            ->setExtends('App\UI\Control\BaseControl')
            ->setFinal();

        $class->addProperty(lcfirst($entity.'Facade'))
            ->setType('App\Model\Database\Facade\\'.$entity.'Facade')
            ->addComment("@var {$entity}Facade @inject");

        $class->addMethod('createComponent'.ucfirst($name).'DataGrid')
            ->setReturnType('Ublaboo\DataGrid\DataGrid')
            ->addBody('$dataset = $this->'.lcfirst($entity).'Facade->'.lcfirst($entity).'Repository->findAllForDataGrid();')
            ->addBody('')
            ->addBody('$grid = new DataGrid();')
            ->addBody('$this->addComponent($grid, $name);')
            ->addBody('$grid->setTranslator($this->translator);')
            ->addBody('$grid->setDataSource($dataset);')
            ->addBody('$grid->setStrictSessionFilterValues(false);')
            ->addBody('')
            ->addBody("\$grid->addColumnNumber('id', 'ID', 'id')")
            ->addBody('    ->setSortable()')
            ->addBody('    ->setFilterText();')
            ->addBody('')
            ->addBody('return $grid;')
            ->addParameter('name')
                ->setType('string');

        $class->addMethod('render')
            ->setReturnType('void')
            ->addBody('$this->template->setFile(dirname(self::getReflection()->getFileName()) . \'/templates/'.lcfirst($name).'DataGridControl.latte\');')
            ->addBody('$this->template->render();');

        return new FileObject(self::DIR_PATH . "$entity/", $name.'DataGridControl', $file, $entity);
    }

    public static function generateTemplate(string $name, ?string $entity = null): void
    {
        $dir = self::DIR_PATH."$entity/templates/";
        if (!FileGenerator::folderExist($dir)) {
            mkdir($dir, 0777, true);
        }

        file_put_contents($dir . lcfirst($name.'DataGridControl.latte'), '{control ' . lcfirst($name.'DataGrid') . '}'.PHP_EOL);
    }
}

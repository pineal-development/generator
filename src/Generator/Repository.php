<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class Repository
{
    public const DIR_PATH = 'app/model/Database/Repository/';

    public static function generate(string $name)
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\Model\Database\Repository');
        $namespace->addUse('App\Model\Database\Entity\\'.$name);
        $namespace->addUse('Doctrine\ORM\QueryBuilder');

        $class = $namespace->addClass($name.'Repository')
            ->setExtends('App\Model\Database\Repository\AbstractRepository');

        $class->addMethod('findAllForDataGrid')
            ->setReturnType('Doctrine\ORM\QueryBuilder')
            ->addBody('return $this->findAll();');

        return new FileObject(self::DIR_PATH, $name.'Repository', $file);
    }
}

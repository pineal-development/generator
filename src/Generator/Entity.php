<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class Entity
{
    public const DIR_PATH = 'app/model/Database/Entity/';

    public static function generate(string $name): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\Model\Database\Entity');
        $namespace->addUse('App\Model\Database\Entity\Attributes\TCreatedAt');
        $namespace->addUse('App\Model\Database\Entity\Attributes\TDeletedAt');
        $namespace->addUse('App\Model\Database\Entity\Attributes\TEntity2Array');
        $namespace->addUse('App\Model\Database\Entity\Attributes\TId');
        $namespace->addUse('App\Model\Database\Entity\Attributes\TUpdatedAt');
        $namespace->addUse('Doctrine\ORM\Mapping', 'ORM');
        $namespace->addUse('Gedmo\Mapping\Annotation', 'Gedmo');

        $class = $namespace->addClass($name)
            ->setExtends('App\Model\Database\Entity\AbstractEntity')
            ->addComment('@ORM\Entity(repositoryClass="App\Model\Database\Repository\\'.$name.'Repository")')
            ->addComment('@ORM\Table(name="`'.lcfirst($name).'`")')
            ->addComment('@ORM\HasLifecycleCallbacks')
            ->addComment('@Gedmo\SoftDeleteable(fieldName="deletedAt")');

        $class->addTrait('App\Model\Database\Entity\Attributes\TId');
        $class->addTrait('App\Model\Database\Entity\Attributes\TCreatedAt');
        $class->addTrait('App\Model\Database\Entity\Attributes\TUpdatedAt');
        $class->addTrait('App\Model\Database\Entity\Attributes\TDeletedAt');
        $class->addTrait('App\Model\Database\Entity\Attributes\TEntity2Array');

        return new FileObject(self::DIR_PATH, $name, $file);
    }
}

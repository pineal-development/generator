<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class Facade
{
    public const DIR_PATH = 'app/model/Database/Facade/';

    public static function generate(string $name): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace('App\Model\Database\Facade');
        $namespace->addUse('App\Model\Database\Entity\\'.$name);
        $namespace->addUse('App\Model\Database\Repository\\'.$name.'Repository');

        $class = $namespace->addClass($name.'Facade')
            ->setFinal()
            ->setExtends('App\Model\Database\Facade\AbstractFacade');

        $class->addProperty(lcfirst($name.'Repository'))
            // ->setType('App\Model\Database\Repository\\'.$name.'Repository')
            ->addComment("@var {$name}Repository");

        $class->addMethod('injectRepository')
            ->setReturnType('void')
            ->addBody('$this->?Repository = $this->entityManager->getRepository('.$name.'::class);', [lcfirst($name)]);

        $saveMethod = $class->addMethod('save')
            ->setReturnType('void')
            ->addBody('$this->entityManager->persist($?);', [lcfirst($name)])
            ->addBody('$this->entityManager->flush();');
        $saveMethod->addParameter(lcfirst($name))
            ->setType('App\Model\Database\Entity\\'.$name);

        $class->addMethod('create')
            ->setReturnType('App\Model\Database\Entity\\'.$name)
            ->addBody('return new '.$name.'();');

        $removeMethod = $class->addMethod('remove')
            ->setReturnType('bool')
            ->addBody('$this->entityManager->remove($entity);')
            ->addBody('$this->entityManager->flush();')
            ->addBody('return true;');
        $removeMethod->addParameter('entity')
            ->setType('App\Model\Database\Entity\\'.$name);

        $class->addMethod('findAll')
            ->setReturnType('array')
            ->addBody('return $this->?Repository->findAll()->getQuery()->getArrayResult();', [lcfirst($name)]);

        return new FileObject(self::DIR_PATH, $name.'Facade', $file);
    }
}

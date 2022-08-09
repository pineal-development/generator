<?php

declare(strict_types=1);

namespace Matronator\Generator;

use Matronator\Generator\FileObject;
use Nette\PhpGenerator\PhpFile;

class Presenter
{
    public const DIR_PATH = 'app/modules/';

    protected static string $classname = '';

    public static function generate(string $name, string $folder, string $module = 'admin'): FileObject
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace(self::getClassPath($folder, $module));

        $class = $namespace->addClass($name.'Presenter')
            ->setFinal()
            ->setExtends(self::getClassPath($folder, $module) . '\BasePresenter');

        $class->addMethod('renderDefault');

        return new FileObject(self::DIR_PATH.ucfirst($module).'/'.self::uppercaseFolder($folder).'/', $name.'Presenter', $file);
    }

    public static function generateBase(string $folder, string $module = 'admin')
    {
        $file = new PhpFile;

        $file->setStrictTypes();

        $namespace = $file->addNamespace(self::getClassPath($folder, $module));

        $namespace->addUse('Nette\Application\UI\ComponentReflection');

        $levels = explode('/', $folder);
        array_pop($levels);
        if ($levels === []) {
            $extends = 'App\Modules\\'.ucfirst($module).'\BaseAdminPresenter';
        } else {
            $ucLevels = [];
            foreach ($levels as $level) {
                $ucLevels[] = ucfirst($level);
            }
            $classPath = implode('\\', $ucLevels);
            $extends = 'App\Modules\\'.ucfirst($module).'\\'.$classPath.'\BasePresenter';
        }

        $class = $namespace->addClass('BasePresenter')
            ->setAbstract()
            ->setExtends($extends);

        $checkRequirements = $class->addMethod('checkRequirements')
            ->addComment('@param ComponentReflection|mixed $element')
            ->addComment('@phpcsSuppress SlevomatCodingStandard.TypeHints.TypeHintDeclaration.MissingParameterTypeHint')
            ->setReturnType('void')
            ->addBody('parent::checkRequirements($element);')
            ->addBody("if (!\$this->user->isAllowed('".self::getLink($folder, false, $module)."')) {")
            ->addBody("    \$this->flashError(\$this->trans('default.roles.missingPrivileges'));")
            ->addBody('    $this->redirect(\''.self::getLink(implode('/', $levels), true, $module).'\');')
            ->addBody('}');

        $checkRequirements->addParameter('element');

        return new FileObject(self::DIR_PATH.ucfirst($module).'/'.self::uppercaseFolder($folder).'/', 'BasePresenter', $file);
    }

    public static function generateTemplate(string $folder, string $module = 'admin'): void
    {
        $dir = self::DIR_PATH.ucfirst($module).'/'.self::uppercaseFolder($folder);
        if (!FileGenerator::folderExist($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!FileGenerator::folderExist($dir.'/templates')) {
            mkdir($dir.'/templates', 0777, true);
        }

        file_put_contents($dir.'/templates/default.latte', '{block #content}'.PHP_EOL);
    }

    public static function createFolder(string $folder, string $module = 'admin'): void
    {
        $dir = self::DIR_PATH.ucfirst($module).'/'.self::uppercaseFolder($folder);
        if (!FileGenerator::folderExist($dir)) {
            mkdir($dir, 0777, true);
        }
    }

    private static function getFullClass(string $name, string $folder, string $module = 'admin')
    {
        if (static::$classname !== '') {
            return static::$classname;
        }

        $levels = explode('/', $folder);
        $levels = array_map(function($item) {
            return ucfirst($item);
        }, $levels);
        $classPath = implode('\\', $levels);

        static::$classname = 'App\Modules\\'.ucfirst($module).'\\'.$classPath.'\\'.ucfirst($name).'Presenter';
        return static::$classname;
    }

    private static function folderToClass(string $folder)
    {
        $levels = explode('/', $folder);
        $levels = array_map(function($item) {
            return ucfirst($item);
        }, $levels);
        return implode('\\', $levels);
    }

    private static function getClassPath(string $folder, string $module = 'admin')
    {
        return 'App\Modules\\'.ucfirst($module).'\\'.self::folderToClass($folder);
    }

    private static function uppercaseFolder(string $folder)
    {
        $levels = explode('/', $folder);
        $levels = array_map(function($item) {
            return ucfirst($item);
        }, $levels);
        return implode('/', $levels);
    }

    private static function getLink(string $folder, bool $absolute = false, string $module = 'admin')
    {
        $levels = explode('/', $folder);
        $levels = array_map(function($item) {
            return ucfirst($item);
        }, $levels);

        if ($absolute) {
            return ':' . ucfirst($module) . ':' . implode(':', $levels) . ':';
        }

        return ucfirst($module) . ':' . implode(':', $levels);
    }
}

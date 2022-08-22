<?php

declare(strict_types=1);

namespace Matronator\Generator\Template;

use Matronator\Generator\FileObject;
use Nette\FileNotFoundException;
use Nette\InvalidArgumentException;
use Nette\Neon\Neon;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use Nette\PhpGenerator\Property;
use Nette\Utils\Json;
use SplFileObject;
use Symfony\Component\Yaml\Yaml;

class Parser
{
    public static function parseFile(string $path, array $arguments)
    {
        $object = self::parseByExtension($path);

        $filename = MtrYml::parse($object->filename, $arguments);
        $outDir = MtrYml::parse($object->path, $arguments);

        $file = self::generate($object->file, $arguments);

        return new FileObject($outDir, $filename, $file);
    }

    public static function getName(string $path)
    {
        $object = self::parseByExtension($path);

        return $object->name;
    }

    public static function generate(object $body, array $args): PhpFile
    {
        $file = new PhpFile;

        if (self::is($body->strict) && $body->strict === true) $file->setStrictTypes();

        if (isset($body->namespace)) {
            self::namespace($body->namespace, $file, $args);
        }

        if (isset($body->class)) {
            self::class($body->class, $args, $file);
        }

        return $file;
    }

    /**
     * @return ClassType
     * @param object $object
     * @param array $args
     * @param PhpFile|PhpNamespace|null $parent
     */
    private static function class(object $object, array $args, mixed &$parent = null): ClassType
    {
        $class = !$parent ? new ClassType(MtrYml::parse($object->name, $args)) : $parent->addClass(MtrYml::parse($object->name, $args));

        if (self::is($object->modifier)) {
            if ($object->modifier === 'final') $class->setFinal();
            if ($object->modifier === 'abstract') $class->setAbstract();
        }
        if (self::is($object->extends)) $class->setExtends(MtrYml::parse($object->extends, $args));
        if (self::is($object->implements)) {
            foreach ($object->implements as $implement) {
                $class->addImplements(MtrYml::parse($implement, $args));
            }
        }
        if (self::is($object->traits)) {
            foreach ($object->traits as $trait) {
                $class->addTrait(MtrYml::parse($trait, $args));
            }
        }
        if (self::is($object->constants)) {
            foreach ($object->constants as $const) {
                $constant = $class->addConstant(MtrYml::parse($const->name, $args), MtrYml::parse($const->value, $args));
                if (self::is($const->visibility)) $constant->setVisibility($const->visibility);
                if (self::is($const->comments)) {
                    foreach ($const->comments as $comment) {
                        $constant->addComment(MtrYml::parse($comment, $args));
                    }
                }
            }
        }
        if (self::is($object->props)) {
            foreach ($object->props as $prop) {
                $property = self::property($prop, $args);
                $class->addMember($property);
            }
        }
        if (self::is($object->methods)) {
            foreach ($object->methods as $method) {
                $classMethod = self::method($method, $args);
                $class->addMember($classMethod);
            }
        }

        return $class;
    }

    private static function namespace(object $object, PhpFile &$file, array $args): PhpNamespace
    {
        $namespace = $file->addNamespace(MtrYml::parse($object->name, $args));

        if (self::is($object->use)) {
            foreach ($object->use as $use) {
                $namespace->addUse(MtrYml::parse($use, $args));
            }
        }
        if (isset($object->class)) {
            $class = self::class($object->class, $args);
            $namespace->add($class);
        }

        return $namespace;
    }

    private static function property(object $prop, array $args): Property
    {
        $property = new Property(MtrYml::parse($prop->name, $args));

        if (self::is($prop->visibility)) $property->setVisibility($prop->visibility);
        if (self::is($prop->static)) $property->setStatic($prop->static);
        if (self::is($prop->nullable) && $prop->nullable) $property->setNullable($prop->nullable);
        if (self::is($prop->type)) $property->setType(MtrYml::parse($prop->type, $args));
        if (self::is($prop->value)) $property->setValue(MtrYml::parse($prop->value, $args));
        if (self::is($prop->init) && $prop->init) $property->setInitialized($prop->init);
        if (self::is($prop->comments)) {
            foreach ($prop->comments as $comment) {
                $property->addComment(MtrYml::parse($comment, $args));
            }
        }

        return $property;
    }

    private static function method(object $object, array $args): Method
    {
        $method = new Method(MtrYml::parse($object->name, $args));

        if (self::is($object->modifier)) {
            if ($object->modifier === 'final') $method->setFinal();
            if ($object->modifier === 'abstract') $method->setAbstract();
        }
        if (self::is($object->visibility)) $method->setVisibility($object->visibility);
        if (self::is($object->static)) $method->setStatic($object->static);
        if (self::is($object->nullable) && $object->nullable) $method->setReturnNullable($object->nullable);
        if (self::is($object->ref) && $object->ref) $method->setReturnReference($object->ref);
        if (self::is($object->return)) $method->setReturnType(MtrYml::parse($object->return, $args));
        if (self::is($object->comments)) {
            foreach ($object->comments as $comment) {
                $method->addComment(MtrYml::parse($comment, $args));
            }
        }
        if (self::is($object->params)) {
            foreach ($object->params as $param) {
                if (isset($param->promoted) && $param->promoted) {
                    $promotedParam = $method->addPromotedParameter(MtrYml::parse($param->name, $args));
                    if (self::is($param->nullable) && $param->nullable) $promotedParam->setNullable($param->nullable);
                    if (self::is($param->type)) $promotedParam->setType(MtrYml::parse($param->type, $args));
                    if (self::is($param->value)) $promotedParam->setDefaultValue(MtrYml::parse($param->value, $args));
                    if (self::is($param->ref) && $param->ref) $promotedParam->setReference($param->ref);
                    if (self::is($param->visibility)) $promotedParam->setVisibility($param->visibility);
                } else {
                    $parameter = $method->addParameter(MtrYml::parse($param->name, $args));
                    if (self::is($param->nullable) && $param->nullable) $parameter->setNullable($param->nullable);
                    if (self::is($param->type)) $parameter->setType(MtrYml::parse($param->type, $args));
                    if (self::is($param->value)) $parameter->setDefaultValue(MtrYml::parse($param->value, $args));
                    if (self::is($param->ref) && $param->ref) $parameter->setReference($param->ref);
                }
            }
        }
        if (self::is($object->body)) {
            foreach ($object->body as $body) {
                $method->addBody(MtrYml::parse($body, $args));
            }
        }

        return $method;
    }

    public static function parseByExtension(string $filename, ?string $contents = null)
    {
        if (!file_exists($filename))
            throw new FileNotFoundException("File '$filename' does not exist.");

        $file = new SplFileObject($filename);

        $extension = $file->getExtension();

        switch ($extension) {
            case 'yml':
            case 'yaml':
            case 'yamlt':
            case 'mtryml':
            case 'ymtr':
                $parsed = $contents ? Yaml::parse($contents, Yaml::PARSE_OBJECT_FOR_MAP) : Yaml::parseFile($filename, Yaml::PARSE_OBJECT_FOR_MAP);
                break;
            case 'neon':
                $parsed = $contents ? Neon::decode($contents) : Neon::decodeFile($filename);
                break;
            case 'json':
                $parsed = $contents ? Json::decode($contents) : Json::decode(file_get_contents($filename));
                break;
            default:
                throw new InvalidArgumentException("Unsupported extension value '{$extension[0]}'.");
        }

        return $parsed;
    }

    public static function is(mixed &$subject): bool
    {
        return is_array($subject) ? isset($subject) && count($subject) > 0 : isset($subject);
    }
}

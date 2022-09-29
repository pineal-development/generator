<?php

declare(strict_types=1);

namespace Matronator\Generator\Generators;

use Matronator\Generator\FileObject;
use Matronator\Generator\Generators\Form\FormField;
use Matronator\Generator\Generators\Form\FormFieldOptions;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method;
use Nette\PhpGenerator\PhpFile;
use Nette\PhpGenerator\PhpNamespace;
use function Matronator\Generator\array_key_last;

class Form
{
    public const DIR_PATH = 'app/ui/Form/';

    public const FIELD_TYPE_CHECKBOX = 'Checkbox';
    public const FIELD_TYPE_EMAIL = 'Email';
    public const FIELD_TYPE_FLOAT = 'Float';
    public const FIELD_TYPE_INTEGER = 'Integer';
    public const FIELD_TYPE_PASSWORD = 'Password';
    public const FIELD_TYPE_SELECT = 'Select';
    public const FIELD_TYPE_SUBMIT = 'Submit';
    public const FIELD_TYPE_TEXT = 'Text';

    private string $name;
    private ?string $entity;
    private array $fields = [];
    private ClassType $class;
    private PhpFile $file;
    private PhpNamespace $namespace;
    private Method $injectMethod;

    /**
     * @param string $name
     * @param string|null $entity
     * @param FormField[] $fields
     */
    public function __construct(string $name, ?string $entity = null, array $fields = [])
    {
        $this->name = $name;
        $this->entity = $entity;
        $this->fields = $fields;

        $this->file = new PhpFile;

        $this->file->setStrictTypes();

        $this->namespace = $this->file->addNamespace('App\UI\Form' . ($this->entity ? "\\$entity" : ''));
        $this->namespace->addUse('App\UI\Form\BaseForm');

        $this->class = $this->namespace->addClass($this->name.'Form')
            ->setFinal()
            ->setExtends('App\UI\Form\BaseForm');

        $this->injectMethod = $this->class->addMethod('inject'.$name.'Form')
            ->setReturnType('void');

        foreach ($this->fields as $field) {
            $this->addField($field->type, $field->name, $field->label, $field->options);
        }

        $this->addFormField(new FormField(self::FIELD_TYPE_SUBMIT, 'save', 'default.form.submit'));
    }

    public function output(): FileObject
    {
        return new FileObject(self::DIR_PATH . ($this->entity ? "{$this->entity}/" : ''), $this->name.'Form', $this->file, $this->entity);
    }

    /**
     * @return static
     */
    public function addFormField(FormField $field): self
    {
        return $this->addField($field->type, $field->name, $field->label, $field->options);
    }

    /**
     * @return static
     */
    public function addField(string $type, string $name, string $label, ?FormFieldOptions $options = null): self
    {
        $optionsArray = $options !== null ? get_object_vars($options) : [];
        if ($optionsArray === []) {
            $this->injectMethod->addBody('$this->add'.$type.'(\''.$name.'\', $this->translator->translate(\''.$label.'\'));'.PHP_EOL);
        } else {
            $this->injectMethod->addBody('$this->add'.$type.'(\''.$name.'\', $this->translator->translate(\''.$label.'\'))');
        }
        foreach ($optionsArray as $key => $value) {
            $body = '    ->set';
            switch ($key) {
                case 'required':
                case 'ommited':
                case 'disabled':
                case 'nullable':
                    $body .= ucfirst($key).'()';
                    break;
                default:
                    if (is_string($value)) {
                        $body .= ucfirst($key).'(\''.$value.'\')';
                    } else {
                        $body .= ucfirst($key).'('.$value.')';
                    }
                    break;
            }
            if ($key === array_key_last($optionsArray)) {
                $body .= ';'.PHP_EOL;
            }
            $this->injectMethod->addBody($body);
        }

        return $this;
    }

    // public static function generate(string $name, string $entity = null)
    // {
    //     $file = new PhpFile;

    //     $file->setStrictTypes();

    //     $namespace = $file->addNamespace('App\UI\Form' . ($entity ? "\\$entity" : ''));
    //     $namespace->addUse('App\UI\Form\BaseForm');
    //     if ($entity) {
    //         $namespace->addUse('App\Model\Database\Facade\\'.ucfirst($entity).'Facade');
    //     }

    //     $class = $namespace->addClass($name.'Form')
    //         ->setFinal()
    //         ->setExtends('App\UI\Form\BaseForm');

    //     if ($entity) {
    //         $class->addProperty(lcfirst($entity).'Facade')
    //             ->setType('App\Model\Database\Facade\\'.ucfirst($entity).'Facade')
    //             ->addComment("@var {$entity}Facade");
    //     }
    //     $injectMethod = $class->addMethod('inject'.$name.'Form')
    //         ->setReturnType('void');

    //     if ($entity) {
    //         $injectMethod->addParameter(lcfirst($entity).'Facade')
    //             ->setType('App\Model\Database\Facade\\'.ucfirst($entity).'Facade');

    //         $injectMethod->addBody('$this->'.lcfirst($entity).'Facade = $'.lcfirst($entity).'Facade;');
    //     }

    //     return new FileObject(self::DIR_PATH, $name.'Form', $file);
    // }
}

<?php

declare(strict_types=1);

namespace Matronator\Generator\Config;

use Matronator\Generator\Form\FormFieldOptions;

class Configuration
{
    public ?Model $model = null;
    public ?UI $ui = null;

    public function __construct(mixed $config)
    {
        $this->model = new Model($config['model']['name'], $config['model']['generate']);
        $this->ui = new UI($config['ui']['name'], $config['ui']['entity'], $config['ui']['generate'], $config['ui']['form']);
    }
}

class Model
{
    public string $name;

    /** @var string|array */
    public mixed $generate;

    public function __construct(string $name, mixed $generate)
    {
        $this->name = $name;
        $this->generate = $generate;
    }
}

class UI
{
    public string $name;
    public ?string $entity = null;
    public string $generate = 'form';
    public Form $form;

    public function __construct(string $name, ?string $entity = null, string $generate, Form $form)
    {
        $this->name = $name;
        $this->entity = $entity;
        $this->generate = $generate;
        $this->form = $form;
    }
}

class Form
{
    /** @var FormField[] */
    public array $fields = [];

    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }
}

// Prepare for version 2 with PHP 8.1 support for enums
// enum ModelGenerate
// {
//     case entity;
//     case repository;
//     case facade;
// }

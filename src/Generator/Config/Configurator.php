<?php

declare(strict_types=1);

namespace Matronator\Generator\Config;

use Matronator\Generator\Debug;
use Matronator\Generator\Entity;
use Matronator\Generator\Facade;
use Matronator\Generator\FileObject;
use Matronator\Generator\Form;
use Matronator\Generator\Form\FormField;
use Matronator\Generator\Form\FormFieldOptions;
use Matronator\Generator\FormControl;
use Matronator\Generator\FormControlFactory;
use Matronator\Generator\FormFactory;
use Matronator\Generator\Repository;
use Symfony\Component\Yaml\Yaml;

final class Configurator
{
    /**
     * @var Configuration
     */
    public mixed $config;

    public $model;
    public $ui;

    public function __construct(string $path)
    {
        /** @var Configuration */
        $this->config = Yaml::parseFile($path, Yaml::PARSE_OBJECT_FOR_MAP);

        $this->model = $this->config->model;
        $this->ui = $this->config->ui;
    }

    public function generateModel(mixed $model)
    {
        $files = [];
        $filename = $model->name;
        if ($model->generate === 'all') {
            $model->generate = ['Entity', 'Repository', 'Facade'];
        }
        $model->generate = (array) $model->generate;

        foreach ($model->generate as $type) {
            echo "Generating $type..." . PHP_EOL;
            switch ($type) {
                case 'Entity':
                    $files[] = Entity::generate($filename);
                    break;
                case 'Repository':
                    $files[] = Repository::generate($filename);
                    break;
                case 'Facade':
                    $files[] = Facade::generate($filename);
                    break;
            }
        }

        return $files;
    }

    public function generateUI(mixed $ui)
    {
        $files = [];
        $filename = $ui->name;
        $entity = (!isset($ui->entity) || !$ui->entity) ? null : $ui->entity;
        if ($ui->generate === 'control') {
            echo "Generating FormControl..." . PHP_EOL;
            $files[] = FormControl::generate($filename, $entity);
            $files[] = FormControlFactory::generate($filename, $entity);
        }
        if (isset($ui->form->fields) && $ui->form->fields) {
            foreach ($ui->form->fields as $field) {
                $field->options = new FormFieldOptions((isset($field->options) && $field->options) ? $field->options : []);
            }
        }
        $fields = $ui->form->fields ?? [];
        $form = new Form($filename, $entity, $fields);
        $files[] = $form->output();

        $files[] = FormFactory::generate($filename, $entity);
        echo "Generating Form..." . PHP_EOL;

        return $files;
    }

    // /**
    //  * @return FileObject[]
    //  */
    // public function getFiles(): array
    // {
    //     $files = [];

    //     if (isset($this->config->model) && $this->config->model) {
    //         $files[] = $this->generateModel($this->config->model);
    //     }

    //     if (isset($this->config->ui) && $this->config->ui) {
    //         $files[] = $this->generateUI($this->config->ui);
    //     }

    //     return $files;
    // }
}

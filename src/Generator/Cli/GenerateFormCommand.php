<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\Form;
use Matronator\Generator\Generators\Form\FormField;
use Matronator\Generator\Generators\Form\FormFieldOptions;
use Matronator\Generator\Generators\FormFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:form', 'Generates a Form file', ['gen:form'])]
class GenerateFormCommand extends Command
{
    protected static $defaultName = 'generate:form';
    protected static $defaultDescription = 'Generates a Form file.';

    public function configure(): void
    {
        $this->setAliases(['gen:f', 'gen:form']);
        $this->addArgument('name', InputArgument::REQUIRED, 'Form name without the `Form` suffix.')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the form belongs.')
            ->addOption('fields', 'f', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_OPTIONAL, 'Input fields the form should contain. Syntax for declaring a field: <options=bold>--fields="{<fg=yellow>type</>:text,<fg=yellow>name</>:username,<fg=yellow>label</>:general.form.username,<fg=yellow>options</>:[required,default:user]}"</>');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $entity = $input->getOption('entity') ?? null;
        $fields = $input->getOption('fields') ?? [];

        $output->writeln($entity ? "Generating <options=bold>{$name}Form</> to entity {$entity}..." : "Generating <options=bold>{$name}Form</>...");

        $form = new Form($name, $entity);

        foreach ($fields as $field) {
            $parsed = $this->parseField($field);
            $form->addFormField($parsed);
            $output->writeln("Adding field <options=bold>{$parsed->name}</>...");
        }

        $output->writeln($entity ? "Generating <options=bold>{$name}FormFactory</> to entity {$entity}..." : "Generating <options=bold>{$name}FormFactory</>...");

        FileGenerator::writeFile([$form->output(), FormFactory::generate($name, $entity)]);

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }

    private function parseField(string $input): FormField
    {
        preg_match('/{type:(.+),name:(.+),label:(.+),options:\[(.+)\]}/', $input, $matches);

        $type = $matches[1];
        $name = $matches[2];
        $label = $matches[3];
        $optionString = explode(',', $matches[4]);

        $options = [];
        foreach ($optionString as $option) {
            if (stripos($option, ':') !== false) {
                $exploded = explode(':', $option);
                if ($exploded[0] === 'default') {
                    $options['defaultValue'] = $exploded[1];
                } else {
                    $options[$exploded[0]] = $exploded[1];
                }
            } else {
                $options[$option] = true;
            }
        }

        $formFieldOptions = new FormFieldOptions($options);

        return new FormField($type, $name, $label, $formFieldOptions);
    }
}

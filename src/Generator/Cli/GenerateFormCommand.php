<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Form;
use Matronator\Generator\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:form', 'Generates a Form file', ['gen:form'])]
class GenerateFormCommand extends Command
{
    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Form name without the `Form` suffix.')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the form belongs.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $entity = $input->getOption('entity') ?? null;

        $output->writeln("Generating {$name}Form");

        $form = new Form($name, $entity);

        FileGenerator::writeFile([$form->output()]);

        $output->writeln('Done!');

        return self::SUCCESS;
    }
}

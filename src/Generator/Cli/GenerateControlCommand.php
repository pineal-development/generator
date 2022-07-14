<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Form;
use Matronator\Generator\FormControl;
use Matronator\Generator\Repository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:control', 'Generates a Control file', ['gen:control'])]
class GenerateControlCommand extends Command
{
    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Control name without the `FormControl` suffix.')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the control belongs.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $entity = $input->getOption('entity') ?? null;

        $output->writeln("Generating {$name}FormControl");

        FileGenerator::writeFile([FormControl::generate($name, $entity)]);

        $output->writeln('Done!');

        return self::SUCCESS;
    }
}

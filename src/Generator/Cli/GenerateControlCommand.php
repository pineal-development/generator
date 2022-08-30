<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\FormControl;
use Matronator\Generator\Generators\FormControlFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:control', 'Generates a Control file', ['gen:control'])]
class GenerateControlCommand extends Command
{
    protected static $defaultName = 'generate:control';
    protected static $defaultDescription = 'Generates a FormControl file.';

    public function configure(): void
    {
        $this->setAliases(['gen:c', 'gen:control']);

        $this->addArgument('name', InputArgument::REQUIRED, 'Control name without the `FormControl` suffix.')
            ->addOption('entity', 'e', InputOption::VALUE_REQUIRED, 'Entity to which the control belongs.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $entity = $input->getOption('entity') ?? null;

        $output->writeln($entity ? "Generating <options=bold>{$name}FormControl</> to entity {$entity}..." : "Generating <options=bold>{$name}FormControl</>...");
        $output->writeln($entity ? "Generating <options=bold>{$name}FormControlFactory</> to entity {$entity}..." : "Generating <options=bold>{$name}FormControlFactory</>...");
        FileGenerator::writeFile([FormControl::generate($name, $entity), FormControlFactory::generate($name, $entity)]);

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }
}

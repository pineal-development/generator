<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Generators\Facade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:facade', 'Generates a Facade file', ['gen:facade'])]
class GenerateFacadeCommand extends Command
{
    protected static $defaultName = 'generate:facade';
    protected static $defaultDescription = 'Generates a Facade file.';

    public function configure(): void
    {
        $this->setAliases(['gen:fa', 'gen:facade']);
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln("Generating <options=bold>{$name}Facade</>...");

        FileGenerator::writeFile(Facade::generate($name));

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }
}

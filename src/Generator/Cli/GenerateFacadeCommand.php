<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\Facade;
use Matronator\Generator\FileGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:facade', 'Generates a Facade file', ['gen:facade'])]
class GenerateFacadeCommand extends Command
{
    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $output->writeln("Generating {$name}Facade");

        FileGenerator::writeFile(Facade::generate($name));

        $output->writeln('Done!');

        return self::SUCCESS;
    }
}

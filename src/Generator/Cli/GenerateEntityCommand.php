<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\Entity;
use Matronator\Generator\FileGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:entity', 'Generates an Entity file', ['gen:entity'])]
class GenerateEntityCommand extends Command
{
    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Generating {$name}Entity");

        FileGenerator::writeFile([Entity::generate($name)]);

        $output->writeln('Done!');

        return self::SUCCESS;
    }
}

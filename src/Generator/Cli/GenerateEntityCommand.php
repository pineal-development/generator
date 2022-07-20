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
    protected static $defaultName = 'generate:entity';
    protected static $defaultDescription = 'Generates an Entity file.';

    public function configure(): void
    {
        $this->setAliases(['gen:e', 'gen:entity']);
        $this->addArgument('name', InputArgument::REQUIRED, 'Class name.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $output->writeln("Generating <options=bold>{$name}Entity</>");

        FileGenerator::writeFile([Entity::generate($name)]);

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }
}

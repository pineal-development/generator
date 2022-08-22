<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:entity', 'Generates an Entity file', ['gen:entity'])]
class GenerateFromTemplateCommand extends Command
{
    protected static $defaultName = 'generate:template';
    protected static $defaultDescription = 'Generates an Entity file.';

    public function configure(): void
    {
        $this->setAliases(['gen:t', 'gen:template']);
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to the template file.');
        $this->addArgument('args', InputArgument::IS_ARRAY, 'Arguments to pass to the template (key=value seperated by space).');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $arguments = $this->getArguments($input->getArgument('args'));
        $path = $input->getOption('path');

        $name = Parser::getName($path);

        $output->writeln("Generating file from template <options=bold>{$name}</>...");

        FileGenerator::writeFile(Parser::parseFile($path, $arguments));

        $output->writeln('<fg=green>Done!</>');

        return self::SUCCESS;
    }

    private function getArguments(array $args): array
    {
        $arguments = [];
        foreach ($args as $arg) {
            $exploded = explode('=', $arg);
            $arguments[$exploded[0]] = $exploded[1];
        }

        return $arguments;
    }
}

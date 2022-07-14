<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\Config\Configurator;
use Matronator\Generator\FileGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate', 'Generate files', ['gen'])]
class GenerateCommand extends Command
{
    protected static $defaultName = 'generate';
    protected static $defaultDescription = 'Generates files';

    protected function configure(): void
    {
        $this->setHelp('Generate files...')
            ->addArgument('name', InputArgument::OPTIONAL, 'Class name.')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'File type', 'database')
            ->addOption('entity', 'e', InputOption::VALUE_OPTIONAL, 'Entity to which the file belongs')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');
        if ($configPath) {
            $config = new Configurator($configPath);
            $output->writeln('Generating from config: ' . $configPath . '...');
            if (isset($config->model) && $config->model) {
                $modelFiles = $config->generateModel($config->model);
                FileGenerator::writeFile($modelFiles);
            }
            if (isset($config->ui) && $config->ui) {
                $uiFiles = $config->generateUI($config->ui);
                FileGenerator::writeFile($uiFiles);
            }
        }

        $output->writeln('Done!');
        // ... put here the code to create the user

        // this method must return an integer number with the "exit status code"
        // of the command. You can also use these constants to make code more readable

        // return this if there was no problem running the command
        // (it's equivalent to returning int(0))
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}

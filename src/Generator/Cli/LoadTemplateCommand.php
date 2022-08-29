<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\Entity;
use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\Parser;
use Matronator\Generator\Template\Storage;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// #[AsCommand('generate:entity', 'Generates an Entity file', ['gen:entity'])]
class LoadTemplateCommand extends Command
{
    use TRunCommand;

    protected static $defaultName = 'load';
    protected static $defaultDescription = 'Loads template from global store and generates it.';

    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name the template is saved under in the store.');
        $this->addArgument('args', InputArgument::IS_ARRAY, 'Arguments to pass to the template (\'key=value\' items seperated by space).');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');

        $storage = new Storage;
        $path = $storage->getFullPath($name);

        return $this->runCommand('generate:template', [
            '--path' => $path,
            'args' => $input->getArgument('args'),
        ], $output);
    }
}

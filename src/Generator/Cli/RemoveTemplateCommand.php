<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\MtrYml;
use Matronator\Generator\Template\Parser;
use Matronator\Generator\Template\Storage;
use SplFileObject;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;

// #[AsCommand('generate:entity', 'Generates an Entity file', ['gen:entity'])]
class RemoveTemplateCommand extends Command
{
    protected static $defaultName = 'remove';
    protected static $defaultDescription = 'Removes a template from the global storage.';

    public function configure(): void
    {
        $this->addArgument('name', InputArgument::REQUIRED, 'Name of the template to remove.');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $name = $input->getArgument('name') ?? null;

        $storage = new Storage;
        if ($storage->remove($name)) {
            $output->writeln("<fg=red>Template '$name' removed!</>");
            $io->newLine();

            return self::SUCCESS;
        }

        $io->error("Couldn't find template with name '$name'.");
        return self::FAILURE;
    }
}

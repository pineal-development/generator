<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\MtrYml;
use Matronator\Generator\Template\Parser;
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
class GenerateFromTemplateCommand extends Command
{
    protected static $defaultName = 'generate:template';
    protected static $defaultDescription = 'Generates an Entity file.';

    public function configure(): void
    {
        $this->setAliases(['gen:t', 'gen:template']);
        $this->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Path to the template file.');
        $this->addArgument('args', InputArgument::IS_ARRAY, 'Arguments to pass to the template (\'key=value\' items seperated by space).');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $path = $input->getOption('path') ?? null;
        $arguments = $this->getArguments($input->getArgument('args')) ?? null;

        if (!$path) {
            $helper = $this->getHelper('question');
            $io->newLine();
            $pathQuestion = new Question('<comment><options=bold>Enter the path to your template</>:</comment> ');
            $validatePath = Validation::createCallable(new Regex([
                'pattern' => '/^(?![\/])(?![.+?\/]*[\/]$)[.+?\/]*/',
                'message' => 'Value must be a valid path without leading or trailing slashes.',
            ]));
            $pathQuestion->setValidator($validatePath);
            $path = $helper->ask($input, $output, $pathQuestion);
            $io->newLine();
        }

        if (!$arguments) {
            $template = $this->getTemplate($path, $io);
            if (!$template)
                return Command::FAILURE;

            $output->writeln('<fg=green>Template found!</>');
            $output->writeln('Looking for template parameters...');

            $args = MtrYml::getArguments($template);
            if ($args !== []) {
                $io->writeln('<fg=green>Template parameters found!</>');
                $io->newLine();
                $arguments = [];
                foreach ($args as $arg) {
                    $argQuestion = new Question("<comment><options=bold>Enter the value for parameter '$arg'</>:</comment> ");
                    $arguments[$arg] = $helper->ask($input, $output, $argQuestion);
                    $io->newLine();
                }
            }
        }

        $name = Parser::getName($path);

        $output->writeln("Generating file from template <options=bold>{$name}</>...");
        $io->newLine();

        FileGenerator::writeFile(Parser::parseFile($path, $arguments));

        $output->writeln('<fg=green>Done!</>');
        $io->newLine();

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

    private function getTemplate(string $path, SymfonyStyle $io): ?string
    {
        if (!file_exists($path)) {
            $io->error("File '$path' doesn't exists.");
            return null;
        }
        $file = new SplFileObject($path);

        if (!in_array($file->getExtension(), ['yml', 'yaml', 'json', 'neon'])) {
            $io->error("File '$path' isn't of a valid type (supported extensions are: yml, yaml, json, neon).");
            return null;
        }

        return $file->fread($file->getSize());
    }
}

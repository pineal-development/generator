<?php

declare(strict_types=1);

namespace Matronator\Generator\Cli;

class Application
{
    public CustomApplication $app;

    public function __construct()
    {
        $this->app = new CustomApplication('MTRGen', '2.1.0');
        $this->app->addCommands([
            new GenerateCommand(),
            new GenerateEntityCommand(),
            new GenerateFacadeCommand(),
            new GenerateRepositoryCommand(),
            new GenerateFormCommand(),
            new GenerateControlCommand(),
            new GeneratePresenterCommand(),
            new GenerateFromTemplateCommand(),
            new GenerateDataGridCommand(),
            new SaveTemplateCommand(),
            new LoadTemplateCommand(),
            new RemoveTemplateCommand(),
        ]);
        $this->app->setDefaultCommand('generate');
    }
}

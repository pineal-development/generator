<?php

declare(strict_types=1);

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\Parser;
use Tester\Assert;

require __DIR__.'/bootstrap.php';

Assert::noError(function() {
    FileGenerator::writeFile(Parser::parseFile(__DIR__.'/../templates/Entity.yaml', ['name' => 'Test', 'entity' => 'Entity']));
});

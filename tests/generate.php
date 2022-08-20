<?php

use Matronator\Generator\FileGenerator;
use Matronator\Generator\Template\Parser;

require __DIR__ . '/../vendor/autoload.php';

FileGenerator::writeFile(Parser::parseFile(__DIR__.'/../templates/Entity.yaml', ['name' => 'Test', 'entity' => 'Entity']));

<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use Matronator\Generator\Store\Path;
use Tester\Assert;
use Tester\TestCase;

class PathTest extends TestCase
{
    public function testNormalize()
    {
        $expected = 'this/is/canonical/path';
        $win = 'this\\is\\canonical\\path\\';
        $relative = 'relative/../this/is/canonical/path';

        Assert::equal($expected, Path::canonicalize($win));
        Assert::equal($expected, Path::canonicalize($relative));
    }
}

(new PathTest)->run();

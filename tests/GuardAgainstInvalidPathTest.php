<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

/**
 * Class SetInBagTest
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class GuardAgainstInvalidPathTest extends TestCase
{

    /**
     * @dataProvider pathProvider
     * @param $path
     * @param $expectException
     * @throws \ReflectionException
     */
    public function testGuard($path, $expectException = true)
    {
        $method = new \ReflectionMethod(DataBag::class, 'GuardAgainstInvalidPath');
        $method->setAccessible(true);

        if ($expectException) {
            $this->expectException(InvalidDataBagPathException::class);
        }
        $databag = DataBag::fromEntityData('person', ['firstName' => 'John']);
        $method->invoke($databag, $path);
    }

    public function pathProvider()
    {
        return [
            'perfectly fine' => ['person.firstName', false],
            'starting with .' => ['.person.firstName'],
            'ends with .' => ['person.firstName.'],
            'has ..' => ['person..firstName'],
            'has .. and .' => ['person..firstName.'],
        ];
    }

}

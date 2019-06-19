<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class GetFromDataBagTest
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
final class GetFromDataBagTest extends TestCase
{
    /**
     * @var DataBag
     */
    private $dataBag;

    protected function setUp()
    {
        $personData = [
            'firstName' => 'John',
            'emailAddresses' => [
                [
                    'emailAddress' => 'john@doe.com',
                    'type' => 'private',
                ],
                [
                    'emailAddress' => 'john@doe2.com',
                    'type' => 'privateGsm',
                ]
            ],
        ];
        $this->dataBag = DataBag::create();
        $this->dataBag->addEntityData('person', $personData);
    }

    protected function tearDown()
    {
        unset($this->dataBag);
    }

    /**
     * @return array
     */
    public function invalidPahtProvider()
    {
        return [
            'empty' => [''],
            'non-existant' => ['invalidPath'],
            'ends-with-.' => ['person.'],
            'starting with .' => ['.person.firstName'],
            'ends with .' => ['person.firstName.'],
            'has ..' => ['person..firstName'],
            'has .. and .' => ['person..firstName.'],
        ];
    }

    /**
     * @dataProvider invalidPahtProvider
     * @expectedException \Communibase\InvalidDataBagPathException
     */
    public function testInvalidPath($path)
    {
        $this->dataBag->get($path);
    }

    /**
     * @return array
     */
    public function provider()
    {
        return [
            ['not.existing', 'default'],
            ['person.firstName', 'John'],
            ['person.emailAddresses.0', ['emailAddress' => 'john@doe.com', 'type' => 'private']],
            ['person.emailAddresses.0.emailAddress', 'john@doe.com'],
            ['person.emailAddresses.privateGsm.emailAddress', 'john@doe2.com'],
            ['person.addresses.test', 'default'],
            ['person.emailAddresses.test', 'default'],
        ];
    }

    /**
     * @dataProvider provider
     *
     * @param string $path
     * @param string $expected
     */
    public function testDataBagGet($path, $expected)
    {
        $this->assertEquals($expected, $this->dataBag->get($path, 'default'));
    }
}

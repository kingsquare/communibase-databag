<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

/**
 * Class GetFromDataBagTest
 * @package Communibase\Tests
 */
final class GetFromDataBagTest extends TestCase
{
    private $dataBag;

    protected function setUp(): void
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
        $this->dataBag = DataBag::fromEntityData('person', $personData);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }

    public function invalidPathProvider(): array
    {
        return [
            'empty' => [''],
            'non-existant' => ['invalidPath'],
            'ends with .' => ['person.'],
            'ends with . on subpath' => ['person.firstName.'],
            'starting with .' => ['.person.firstName'],
            'has ..' => ['person..firstName'],
            'has .. and .' => ['person..firstName.'],
        ];
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function testInvalidPath(string $path): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->dataBag->get($path);
    }

    public function provider(): array
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
     * @param array|string $expected
     */
    public function testDataBagGet(string $path, $expected): void
    {
        self::assertEquals($expected, $this->dataBag->get($path, 'default'));
    }
}

<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

final class GetFromDataBagTest extends TestCase
{
    /**
     * @var DataBag
     */
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
            'metadata' => ['path' => 'Foo']
        ];
        $this->dataBag = DataBag::fromEntityData('person', $personData);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }

    /**
     * @return array<string,array>
     */
    public function invalidPaths(): array
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
     * @dataProvider invalidPaths
     */
    public function test_it_throws_exception_if_invalid_path_is_used_when_getting_data(string $path): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->dataBag->get($path);
    }

    /**
     * @return array<array>
     */
    public function validPaths(): array
    {
        return [
            ['not.existing', 'default'],
            ['not.existing.subPath', 'default'],
            ['person.firstName', 'John'],
            ['person.emailAddresses.0', ['emailAddress' => 'john@doe.com', 'type' => 'private']],
            ['person.emailAddresses.0.emailAddress', 'john@doe.com'],
            ['person.emailAddresses.privateGsm.emailAddress', 'john@doe2.com'],
            ['person.addresses.test', 'default'],
            ['person.emailAddresses.test', 'default'],
            ['person.metadata.path', 'Foo'],
        ];
    }

    /**
     * @dataProvider validPaths
     * @param array<string,string>|string $expected
     */
    public function test_it_can_get_data_using_a_path(string $path, $expected): void
    {
        self::assertEquals($expected, $this->dataBag->get($path, 'default'));
    }
}

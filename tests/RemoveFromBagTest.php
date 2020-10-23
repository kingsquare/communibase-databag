<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveFromBagTest
 * @package Communibase\Tests
 */
class RemoveFromBagTest extends TestCase
{
    private $dataBag;
    private static $data = ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3], ['type' => 't']]];

    protected function setUp(): void
    {
        $this->dataBag = DataBag::fromEntityData('foo', self::$data);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }

    public function testInvalidPath(): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->dataBag->remove('invalidPath');
    }

    /**
     * @return array
     */
    public function provider(): array
    {
        return [
            ['baz.bar', ['foo' => self::$data]],
            ['baz.bar.0', ['foo' => self::$data]],
            [
                'foo.a',
                ['foo' => ['a' => null, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3], ['type' => 't']]]]
            ],
            ['foo.b.0', ['foo' => ['a' => 1, 'b' => null]]],
            ['foo.b.1', ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2]]]]],
            ['foo.b.s', ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 't']]]]],
            ['foo.b.0.c', ['foo' => ['a' => 1, 'b' => null]]],
            ['foo.b.s.c', ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 't']]]]],
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testDataBagRemove(string $path, array $expected): void
    {
        $this->dataBag->remove($path);
        self::assertEquals($expected, $this->dataBag->getState());
    }

    public function testDoNotRemoveAllOnNumericIndex(): void
    {
        $this->dataBag->remove('foo.b.0', false);
        self::assertEquals(
            ['foo' => ['a' => 1, 'b' => [['type' => 's', 'c' => 3], ['type' => 't']]]],
            $this->dataBag->getState()
        );
    }

    public function test_property_becomes_null_if_empty(): void
    {
        $dataBag = DataBag::fromEntityData(
            'foo',
            [
                'a' => [['type' => 'b'], ['type' => 'b']]
            ]
        );
        $dataBag->remove('foo.a.b');
        self::assertEquals(['a' => null], $dataBag->getState('foo'));
    }
}

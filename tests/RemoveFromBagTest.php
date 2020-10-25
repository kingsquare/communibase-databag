<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

class RemoveFromBagTest extends TestCase
{
    /**
     * @var DataBag
     */
    private $dataBag;

    /**
     * @var array<string,mixed>
     */
    private static $data = ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3], ['type' => 't']]];

    protected function setUp(): void
    {
        $this->dataBag = DataBag::fromEntityData('foo', self::$data);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }

    public function test_it_will_throw_exception_if_using_invalid_path(): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->dataBag->set('invalidPath', null);
    }

    /**
     * @return array<array>
     */
    public function removePaths(): array
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
     * @dataProvider removePaths
     * @param array<array> $expected
     */
    public function test_it_will_remove_items(string $path, array $expected): void
    {
        $this->dataBag->set($path, null);
        self::assertEquals($expected, $this->dataBag->getState());
    }

    public function test_property_becomes_null_if_empty(): void
    {
        $dataBag = DataBag::fromEntityData(
            'foo',
            [
                'a' => [['type' => 'b'], ['type' => 'b']]
            ]
        );
        $dataBag->set('foo.a.b', null);
        self::assertEquals(['a' => null], $dataBag->getState('foo'));
    }

    public function test_it_can_remove_a_sub_path(): void
    {
        $dataBag = DataBag::fromEntityData(
            'foo',
            [
                'a' => 1,
                'b' => ['c' => 2],
                'd' => ['e' => [1, 2]]
            ]
        );
        $dataBag->set('foo.b.c', null);
        $dataBag->set('foo.d.e', null);
        self::assertSame(['a' => 1, 'b' => ['c' => null], 'd' => ['e' => null]], $dataBag->getState('foo'));
    }
}

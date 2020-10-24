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
    private static $data = ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3], ['type' => 't']]];
    private $dataBag;

    /**
     * @test
     */
    public function it_will_throw_exception_if_using_invalid_path(): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->dataBag->remove('invalidPath');
    }

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
     * @test
     * @dataProvider provider
     */
    public function it_will_remove_items(string $path, array $expected): void
    {
        $this->dataBag->remove($path);
        self::assertEquals($expected, $this->dataBag->getState());
    }

    /**
     * @test
     */
    public function it_will_not_remove_all_items_if_numerically_indexed(): void
    {
        $this->dataBag->remove('foo.b.0', false);
        self::assertEquals(
            ['foo' => ['a' => 1, 'b' => [['type' => 's', 'c' => 3], ['type' => 't']]]],
            $this->dataBag->getState()
        );
    }

    /**
     * @test
     */
    public function property_becomes_null_if_empty(): void
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

    protected function setUp(): void
    {
        $this->dataBag = DataBag::create();
        $this->dataBag->addEntityData('foo', self::$data);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }
}

<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use Communibase\InvalidDataBagPathException;
use PHPUnit\Framework\TestCase;

/**
 * Class SetInBagTest
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class SetInBagTest extends TestCase
{
    private $emptyDataBag;
    private $filledDataBag;

    protected function setUp(): void
    {
        $this->emptyDataBag = DataBag::create();
        $this->filledDataBag = DataBag::fromEntityData(
            'foo',
            ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3]]]
        );
    }

    protected function tearDown(): void
    {
        unset($this->emptyDataBag, $this->filledDataBag);
    }

    public function testRemoveBySettingNull(): void
    {
        $this->filledDataBag->set('foo.a', null);
        self::assertNull($this->filledDataBag->getState('foo')['a']);
    }

    public function testInvalidPath(): void
    {
        $this->expectException(InvalidDataBagPathException::class);
        $this->emptyDataBag->set('invalidPath', 1);
    }

    public function emptyDataBagProvider(): array
    {
        return [
            ['foo.bar', 1, ['foo' => ['bar' => 1]]],
            ['foo.bar.0', 1, ['foo' => ['bar' => [1]]]],
            ['foo.bar.test', 1, ['foo' => ['bar' => [1]]]],
            ['foo.bar.test', ['baz' => 3], ['foo' => ['bar' => [['baz' => 3, 'type' => 'test']]]]],
            ['foo.bar.0.baz', 1, ['foo' => ['bar' => [['baz' => 1]]]]],
            ['foo.bar.test.baz', 1, ['foo' => ['bar' => [['baz' => 1, 'type' => 'test']]]]],
        ];
    }

    /**
     * @dataProvider emptyDataBagProvider
     * @param int|array $value
     */
    public function testEmptyDataBagSet(string $path, $value, array $expected): void
    {
        $this->emptyDataBag->set($path, $value);
        self::assertEquals($expected, $this->emptyDataBag->getState());
    }

    public function filledDataBagProvider(): array
    {
        return [
            [
                'n.v',
                1,
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3]]], 'n' => ['v' => 1]],
            ],
            [
                'foo.new',
                1,
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3]], 'new' => 1]],
            ],
            [
                'foo.a',
                2,
                ['foo' => ['a' => 2, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3]]]],
            ],
            [
                'foo.b.0',
                2,
                ['foo' => ['a' => 1, 'b' => [2, ['type' => 's', 'c' => 3]]]]
            ],
            [
                'foo.b.s',
                2,
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], 2]]]
            ],
            [
                'foo.b.s',
                ['x' => 3],
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['x' => 3, 'type' => 's']]]]
            ],
            [
                'foo.b.0.c',
                4,
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 4], ['type' => 's', 'c' => 3]]]]
            ],
            [
                'foo.b.f.c',
                5,
                ['foo' => ['a' => 1, 'b' => [['type' => 'f', 'c' => 5], ['type' => 's', 'c' => 3]]]]
            ],
        ];
    }

    /**
     * @dataProvider filledDataBagProvider
     * @param array|string $value
     */
    public function testFilledDataBagSet(string $path, $value, array $expected): void
    {
        $this->filledDataBag->set($path, $value);
        self::assertEquals($expected, $this->filledDataBag->getState());
    }

    /**
     * @test
     */
    public function it_can_handle_new_type_if_target_is_an_array_with_an_empty_array(): void
    {
        $dataBag = DataBag::fromEntityData('foo', ['addresses' => [[]]]);
        $dataBag->set('foo.addresses.private.street', 'bar');
        self::assertEquals(
            ['addresses' => [[], ['type' => 'private', 'street' => 'bar']]],
            $dataBag->getState('foo')
        );
    }
}

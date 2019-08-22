<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class SetInBagTest
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class SetInBagTest extends TestCase
{
    /**
     * @var DataBag
     */
    private $emptyDataBag;

    /**
     * @var DataBag
     */
    private $filledDataBag;

    /**
     *
     */
    protected function setUp()
    {
        $this->emptyDataBag = DataBag::create();
        $this->filledDataBag = DataBag::create();
        $this->filledDataBag->addEntityData(
            'foo',
            ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3]]]
        );
    }

    /**
     *
     */
    protected function tearDown()
    {
        unset($this->emptyDataBag, $this->filledDataBag);
    }

    /**
     *
     */
    public function testRemoveBySettingNull()
    {
        $this->filledDataBag->set('foo.a', null);
        $this->assertNull($this->filledDataBag->getState('foo')['a']);
    }

    /**
     * @expectedException \Communibase\InvalidDataBagPathException
     */
    public function testInvalidPath()
    {
        $this->emptyDataBag->set('invalidPath', 1);
    }

    /**
     * @return array
     */
    public function emptyDataBagProvider()
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
     *
     * @param string $path
     * @param string $value
     * @param array $expected
     */
    public function testEmptyDataBagSet($path, $value, $expected)
    {
        $this->emptyDataBag->set($path, $value);
        $this->assertEquals($expected, $this->emptyDataBag->getState());
    }

    /**
     * @return array
     */
    public function filledDataBagProvider()
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
     *
     * @param string $path
     * @param string $value
     * @param array $expected
     */
    public function testFilledDataBagSet($path, $value, $expected)
    {
        $this->filledDataBag->set($path, $value);
        $this->assertEquals($expected, $this->filledDataBag->getState());
    }

    /**
     * @test
     */
    public function it_can_handle_new_type_if_target_is_an_array_with_an_empty_array()
    {
        $dataBag = DataBag::fromEntityData('foo', ['addresses' => [[]]]);
        $dataBag->set('foo.addresses.private.street', 'bar');
        $this->assertEquals(
            ['addresses' => [[], ['type' => 'private', 'street' => 'bar']]],
            $dataBag->getState('foo')
        );
    }
}

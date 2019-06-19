<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveFromBag
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class RemoveFromBagTest extends TestCase
{
    /**
     * @var DataBag
     */
    private $dataBag;

    private static $data = ['a' => 1, 'b' => [['type' => 'f', 'c' => 2], ['type' => 's', 'c' => 3], ['type' => 't']]];

    protected function setUp()
    {
        $this->dataBag = DataBag::create();
        $this->dataBag->addEntityData('foo', self::$data);
    }

    protected function tearDown()
    {
        unset($this->dataBag);
    }

    /**
     * @expectedException \Communibase\InvalidDataBagPathException
     */
    public function testInvalidPath()
    {
        $this->dataBag->remove('invalidPath');
    }

    /**
     * @return array
     */
    public function provider()
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
     *
     * @param string $path
     * @param array $expected
     */
    public function testDataBagRemove($path, $expected)
    {
        $this->dataBag->remove($path);
        $this->assertEquals($expected, $this->dataBag->getState());
    }

    public function testDoNotRemoveAllOnNumericIndex()
    {
        $this->dataBag->remove('foo.b.0', false);
        $this->assertEquals(
            ['foo' => ['a' => 1, 'b' => [['type' => 's', 'c' => 3], ['type' => 't']]]],
            $this->dataBag->getState()
        );
    }

    public function test_property_becomes_null_if_empty()
    {
        $dataBag = DataBag::fromEntityData('foo', [
            'a' => [['type' => 'b'], ['type' => 'b']]
        ]);
        $dataBag->remove('foo.a.b');
        $this->assertEquals(['a' => null], $dataBag->getState('foo'));
    }
}

<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class FactoryTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_be_created()
    {
        $databag = DataBag::create();
        $this->assertInstanceOf(DataBag::class, $databag);
    }

    /**
     * @test
     */
    public function it_can_be_statically_constructed()
    {
        $databag = DataBag::fromEntityData('Foo', ['bar' => 'baz']);
        $this->assertSame('baz', $databag->get('Foo.bar'));
    }
}

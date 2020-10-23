<?php

declare(strict_types=1);

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
    public function it_can_be_statically_constructed(): void
    {
        $databag = DataBag::fromEntityData('Foo', ['bar' => 'baz']);
        self::assertSame('baz', $databag->get('Foo.bar'));
    }

    /**
     * @test
     */
    public function it_can_be_statically_created_and_filled(): void
    {
        $databag = DataBag::create();
        $databag->addEntityData('Foo', ['bar' => 'baz']);
        self::assertSame('baz', $databag->get('Foo.bar'));
    }

    /**
     * @test
     */
    public function it_can_be_statically_created_and_filled_with_multiple_entities(): void
    {
        $databag = DataBag::create();
        $databag->addEntityData('Foo', ['bar' => 'baz']);
        $databag->addEntityData('Bar', ['bar' => 'baz']);
        self::assertSame('baz', $databag->get('Foo.bar'));
        self::assertSame('baz', $databag->get('Bar.bar'));
    }
}

<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class FactoryTest
 * @package Communibase\Tests
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
}

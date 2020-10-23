<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class HasEntityDataTest
 * @package Communibase\Tests
 */
class HasEntityDataTest extends TestCase
{
    public function testHasEntityData(): void
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
        ];
        $dataBag = DataBag::fromEntityData('person', $personData);

        self::assertTrue($dataBag->hasEntityData('person'));
        self::assertFalse($dataBag->hasEntityData('company'));
    }
}

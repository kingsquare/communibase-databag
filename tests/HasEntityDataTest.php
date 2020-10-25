<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

class HasEntityDataTest extends TestCase
{
    /**
     * @var DataBag
     */
    private $dataBag;

    protected function setUp(): void
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
        $this->dataBag = DataBag::fromEntityData('person', $personData);
    }

    protected function tearDown(): void
    {
        unset($this->dataBag);
    }

    public function test_it_has_data_for_known_path(): void
    {
        self::assertTrue($this->dataBag->hasEntityData('person'));
    }

    public function test_it_does_not_have_data_for_known_path(): void
    {
        self::assertFalse($this->dataBag->hasEntityData('company'));
    }
}

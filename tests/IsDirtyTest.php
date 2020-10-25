<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

class IsDirtyTest extends TestCase
{
    /**
     * @test
     */
    public function it_will_be_dirty_if_path_is_changed(): void
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
        $dataBag->set('person.firstName', 'Darko');
        self::assertTrue((bool)$dataBag->isDirty('person'));
    }

    /**
     * @test
     */
    public function it_will_not_be_dirty_if_path_is_unchanged(): void
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
        $dataBag->set('person.firstName', 'John');
        self::assertFalse((bool)$dataBag->isDirty('person'));
    }

    /**
     * @test
     */
    public function it_will_be_dirty_if_path_property_is_removed(): void
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
        $dataBag->remove('person.firstName', true);
        self::assertTrue((bool)$dataBag->isDirty('person'));
    }

    /**
     * @test
     */
    public function it_will_be_dirty_when_checking_unknown_path(): void
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
        self::assertNull($dataBag->isDirty('company'));
    }

    /**
     * @test
     */
    public function it_will_be_dirty_when_adding_new_path(): void
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
        $dataBag->set('company.title', 'Kingsquare BV');
        self::assertTrue((bool)$dataBag->isDirty('company'));
    }

    /**
     * @test
     */
    public function generated_ids_are_ignored(): void
    {
        $dataBag = DataBag::fromEntityData(
            'person',
            [
                'emailAddresses' => [
                    [
                        'emailAddress' => 'john@doe.com',
                        'type' => 'private',
                    ]
                ],
            ]
        );

        $dataBag->set(
            'person.emailAddresses.0',
            [
                'emailAddress' => 'john@doe.com',
                'type' => 'private',
                '_id' => '8b912a04eac44fc453294c31f7ae98da',
            ]
        );

        self::assertFalse((bool)$dataBag->isDirty('person'));
    }
}

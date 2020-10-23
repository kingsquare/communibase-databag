<?php

declare(strict_types=1);

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveFromBag
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class IsDirtyTest extends TestCase
{
    public function testIsDirtyWhenFieldIsChanged(): void
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

    public function testIsDirtyWhenFieldIsUnchanged(): void
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

    public function testIsDirtyWhenRemovedFromBag(): void
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

    public function testIsDirtyWithUnknownPath(): void
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

    public function testIsDirtyWitNewPath(): void
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

    public function test_generated_ids_are_ignored(): void
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

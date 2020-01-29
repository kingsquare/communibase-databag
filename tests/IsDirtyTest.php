<?php

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
    public function testIsDirtyWhenFieldIsChanged()
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
        $dataBag = DataBag::create();
        $dataBag->addEntityData('person', $personData);
        $dataBag->set('person.firstName', 'Darko');
        $this->assertTrue((bool)$dataBag->isDirty('person'));
    }

    public function testIsDirtyWhenFieldIsUnchanged()
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
        $dataBag = DataBag::create();
        $dataBag->addEntityData('person', $personData);
        $dataBag->set('person.firstName', 'John');
        $this->assertFalse((bool)$dataBag->isDirty('person'));
    }

    public function testIsDirtyWhenRemovedFromBag()
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
        $dataBag = DataBag::create();
        $dataBag->addEntityData('person', $personData);
        $dataBag->remove('person.firstName', true);
        $this->assertTrue((bool)$dataBag->isDirty('person'));
    }

    public function testIsDirtyWithUnknownPath()
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
        $dataBag = DataBag::create();
        $dataBag->addEntityData('person', $personData);
        $this->assertNull($dataBag->isDirty('company'));
    }

    public function testIsDirtyWitNewPath()
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
        $dataBag = DataBag::create();
        $dataBag->addEntityData('person', $personData);
        $dataBag->set('company.title', 'Kingsquare BV');
        $this->assertTrue((bool)$dataBag->isDirty('company'));
    }

    public function test_generated_ids_are_ignored()
    {
        $dataBag = DataBag::create();
        $dataBag->addEntityData(
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

        $this->assertFalse((bool)$dataBag->isDirty('person'));
    }
}

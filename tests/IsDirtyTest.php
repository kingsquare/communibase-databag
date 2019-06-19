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
        $this->assertSame(true, $dataBag->isDirty('person'));
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
        $this->assertSame(false, $dataBag->isDirty('person'));
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
        $this->assertSame(true, $dataBag->isDirty('person'));
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
        $this->assertSame(true, $dataBag->isDirty('company'));
    }
}

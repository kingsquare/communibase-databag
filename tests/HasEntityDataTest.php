<?php

namespace Communibase\Tests;

use Communibase\DataBag;
use PHPUnit\Framework\TestCase;

/**
 * Class RemoveFromBag
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class HasEntityDataTest extends TestCase
{
    public function testHasEntityData()
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

        $this->assertTrue($dataBag->hasEntityData('person'));
        $this->assertFalse($dataBag->hasEntityData('company'));
    }
}

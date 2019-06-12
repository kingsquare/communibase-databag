# Communibase DataBag

It's a bag, for CB data. If we need to create a CB object from CB data (array) we can use this dataBag object as
a private entity class property. The dataBag can contain one or more entities. For each entity we can get/set
properties by path. If we need to persist the entity back into CB use getState to fetch the (updated) data array.

```php

namespace Communibase;

/**
 * Class ExamplePerson
 * @package Communibase\DataBag
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
class ExamplePerson
{
    /**
     * @var DataBag
     */
    private $databag;

    /**
     * ExamplePerson constructor.
     */
    private function __construct()
    {
        $this->databag = DataBag::create();
    }

    /**
     * @param array $data
     */
    public static function fromPersonData(array $data)
    {
        $person = new self();
        $person->databag->addEntityData('person', $data);
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return (string)$this->databag->get('person.firstName');
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->databag->set('person.firstName', $firstName);
    }

    /**
     * @return array
     */
    public function getState()
    {
        return $this->databag->getState('person');
    }
}

// $person = $personRepository->findById($communibasePersonId); // returns ExamplePerson::fromPersonData($fetchedCbData)
// $person->setFirstName('John');
// $personRepository->persist($person); // does $cbConnector->update('Person', $person->getState('person'));
```

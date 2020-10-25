# Communibase DataBag

[![Build Status](https://travis-ci.org/kingsquare/communibase-databag.svg?branch=master)](https://travis-ci.org/kingsquare/communibase-databag)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/kingsquare/communibase-databag/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/kingsquare/communibase-databag/?branch=master)

It's a bag, for CB data. If we need to create a CB object from CB data (array) we can use this dataBag object as
a private entity class property. The dataBag can contain one or more entities. For each entity we can get/set
properties by path. If we need to persist the entity back into CB use getState to fetch the (updated) data array.

```php

namespace Communibase;

/**
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
     * @param array<string,mixed> $personData
     */
    private function __construct(array $personData)
    {
        $this->databag = DataBag::fromEntityData('person', $personData);
    }

    /**
     * @param array<string,mixed> $personData
     */
    public static function fromPersonData(array $personData): ExamplePerson
    {
        return new self($personData);
    }

    public function getFirstName(): string
    {
        return (string)$this->databag->get('person.firstName');
    }

    public function setFirstName(string $firstName): void
    {
        $this->databag->set('person.firstName', $firstName);
    }

    /**
     * @return array<string,mixed>
     */
    public function getState(): array
    {
        return $this->databag->getState('person');
    }
}

// $person = $personRepository->findById($communibasePersonId); // returns ExamplePerson::fromPersonData($fetchedCbData)
// $person->setFirstName('John');
// $personRepository->persist($person); // does $cbConnector->update('Person', $person->getState('person'));
```

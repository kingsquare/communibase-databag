<?php

declare(strict_types=1);

namespace Communibase;

use Communibase\DataBag\DataMutator;
use Communibase\DataBag\DataRemover;
use Communibase\DataBag\DataRetriever;

/**
 * It's a bag, for CB data. If we need to create a CB object from CB data (array) we can use this dataBag object as a
 * private entity class property. The dataBag can contain one or more entities. For each entity we can get/set
 * properties by path. If we need to persist the entity back into CB use getState to fetch the (updated) data array.
 *
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
final class DataBag
{
    /**
     * The bag!
     * @var array<string, array>
     */
    private $data = [];

    /**
     * @var DataMutator
     */
    private $dataMutator;

    /**
     * @var DataRetriever
     */
    private $dataRetriever;

    /**
     * @var DataRemover;
     */
    private $dataRemover;

    /**
     * Original data hash for isDirty check
     *
     * @var array<string,string>
     */
    private $hashes;

    /**
     * If we have multiple identical get calls in the same request use the cached result
     *
     * @var array<string, mixed>
     */
    private $cache = [];

    /**
     * Private constructor, use the named constructors below
     */
    private function __construct()
    {
        $this->dataMutator = new DataMutator();
        $this->dataRetriever = new DataRetriever();
        $this->dataRemover = new DataRemover();
    }

    public static function create(): DataBag
    {
        return new self();
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromEntityData(string $entityType, array $data): DataBag
    {
        $dataBag = self::create();
        $dataBag->addEntityData($entityType, $data);
        return $dataBag;
    }

    /**
     * Add additional entities
     * @param array<string, mixed> $data
     */
    public function addEntityData(string $entityType, array $data): DataBag
    {
        $this->data[$entityType] = $data;
        $this->hashes[$entityType] = $this->generateHash($data);
        return $this;
    }

    /**
     * Fetch a value from the databag.
     *
     * $path can be:
     * - person.firstName               direct property
     * - person.emailAddresses.0        indexed by numeric position
     * - person.addresses.visit         indexed by 'type' property
     * - person.addresses.visit.street  indexed by 'type' property + get specific property
     *
     * @param string $path path to the target
     * @param mixed $default return value if there's no data
     *
     * @return mixed|null
     * @throws InvalidDataBagPathException
     */
    public function get(string $path, $default = null)
    {
        $this->guardAgainstInvalidPath($path);

        if (!\array_key_exists($path, $this->cache)) {
            $this->cache[$path] = $this->dataRetriever->getByPath($this->data, $path, $default);
        }
        return $this->cache[$path];
    }

    /**
     * Set a value in the bag.
     *
     * @param string $path path to the target (see get() for examples)
     * @param mixed $value new value
     *
     * @throws InvalidDataBagPathException
     */
    public function set(string $path, $value): void
    {
        $this->guardAgainstInvalidPath($path);

        unset($this->cache[$path]);

        if ($value === null) {
            $this->guardAgainstInvalidPath($path);
            $this->dataRemover->removeByPath($this->data, $path);
            return;
        }

        $this->dataMutator->setByPath($this->data, $path, $value);
    }

    /**
     * Check if a certain entity type exists in the dataBag
     *
     * @return bool true if the entity type exists
     */
    public function hasEntityData(string $entityType): bool
    {
        return isset($this->data[$entityType]);
    }

    /**
     * Check if the initial data has changed
     *
     * @param string $entityType entity type to check
     *
     * @return bool|null true if changed, false if not and null if the entity type is not set
     */
    public function isDirty(string $entityType): ?bool
    {
        if (!isset($this->data[$entityType])) {
            return null;
        }
        if (empty($this->hashes[$entityType])) {
            return true;
        }
        return $this->hashes[$entityType] !== $this->generateHash($this->getState($entityType));
    }

    /**
     * @param array<string,mixed> $data
     */
    private function generateHash(array $data): string
    {
        return md5(serialize($this->filterIds($data)));
    }

    /**
     * @param array<string,mixed> $data
     * @return array<string,mixed>
     */
    private function filterIds(array $data): array
    {
        array_walk(
            $data,
            function (&$value) {
                if (\is_array($value)) {
                    $value = $this->filterIds($value);
                }
            }
        );
        return array_diff_key($data, ['_id' => null]);
    }

    /**
     * Get the raw data array
     *
     * @param string|null $entityType only get the specified type (optional)
     * @return array<string,mixed>
     */
    public function getState(string $entityType = null): array
    {
        if ($entityType === null) {
            return $this->data;
        }
        return $this->data[$entityType] ?? [];
    }

    /**
     * @throws InvalidDataBagPathException
     */
    private function guardAgainstInvalidPath(string $path): void
    {
        if ($path === '' // empty
            || strpos($path, '..') !== false // has .. somewhere
            || substr($path, -1) === '.' // ends with .
            || \in_array(strpos($path, '.'), [false, 0], true) // starts with or doesnt have any .
        ) {
            throw new InvalidDataBagPathException('Invalid path provided: ' . $path);
        }
    }
}

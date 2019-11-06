<?php

namespace Communibase;

/**
 * Class DataBag
 *
 * It's a bag, for CB data. If we need to create a CB object from CB data (array) we can use this dataBag object as a
 * private entity class property. The dataBag can contain one or more entities. For each entity we can get/set
 * properties by path. If we need to persist the entity back into CB use getState to fetch the (updated) data array.
 *
 * @package Communibase\DataBag
 * @author Kingsquare (source@kingsquare.nl)
 * @copyright Copyright (c) Kingsquare BV (http://www.kingsquare.nl)
 */
final class DataBag
{
    /**
     * The bag!
     *
     * @var array
     */
    private $data;

    /**
     * Original data hash for isDirty check
     *
     * @var string[]
     */
    private $hashes;

    /**
     * If we have multiple identical get calls in the same request use the cached result
     *
     * @var array
     */
    private $cache = [];

    /**
     * DataBag constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return DataBag
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Static constructor
     *
     * @param string $entityType
     * @param array $data
     *
     * @return DataBag
     */
    public static function fromEntityData($entityType, array $data)
    {
        $dataBag = new self();
        $dataBag->addEntityData($entityType, $data);
        return $dataBag;
    }

    /**
     * Add additional entities
     *
     * @param string $entityType
     * @param array $data
     *
     * @return DataBag
     */
    public function addEntityData($entityType, array $data)
    {
        $this->data[$entityType] = $data;
        $this->hashes[$entityType] = md5(serialize($data));
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
     * @return mixed
     * @throws InvalidDataBagPathException
     */
    private function getByPath($path, $default = null)
    {
        list($entityType, $path) = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            return isset($this->data[$entityType][$path]) ? $this->data[$entityType][$path] : $default;
        }

        // Indexed
        list($path, $index) = explode('.', $path, 2);

        if (empty($this->data[$entityType][$path])) {
            return $default;
        }

        return $this->getIndexed((array)$this->data[$entityType][$path], $index, $default);
    }

    /**
     * @param array $nodes
     * @param string $index
     * @param mixed $default
     *
     * @return mixed
     */
    private function getIndexed(array $nodes, $index, $default)
    {
        $field = null;
        if (strpos($index, '.') > 0) {
            list($index, $field) = explode('.', $index, 2);
        }

        $translatedIndex = $index;

        if (!is_numeric($index)) {
            $translatedIndex = null;
            foreach ($nodes as $nodeIndex => $node) {
                if (isset($node['type']) && $node['type'] === $index) {
                    $translatedIndex = $nodeIndex;
                    break;
                }
            }
        }

        if ($translatedIndex === null) {
            return $default;
        }

        if ($field === null) {
            return isset($nodes[$translatedIndex]) ? $nodes[$translatedIndex] : $default;
        }

        return isset($nodes[$translatedIndex][$field]) ? $nodes[$translatedIndex][$field] : $default;
    }

    /**
     * Fetch a cached value from the databag.
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
    public function get($path, $default = null)
    {
        $path = (string)$path;
        $this->guardAgainstInvalidPath($path);

        if (!array_key_exists($path, $this->cache)) {
            $this->cache[$path] = $this->getByPath($path, $default);
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
    public function set($path, $value)
    {
        $path = (string)$path;
        $this->guardAgainstInvalidPath($path);

        unset($this->cache[$path]);

        if ($value === null) {
            $this->remove($path);
            return;
        }

        $this->setByPath($path, $value);
    }

    /**
     * @param string $path
     * @param mixed $value
     */
    private function setByPath($path, $value)
    {
        list($entityType, $path) = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            $this->data[$entityType][$path] = $value;
            return;
        }

        // Indexed
        $this->setIndexed($entityType, $path, $value);
    }

    /**
     * @param string $entityType
     * @param string $path
     * @param mixed $value
     */
    private function setIndexed($entityType, $path, $value)
    {
        list($path, $index) = explode('.', $path, 2);

        $field = null;
        if (strpos($index, '.') > 0) {
            list($index, $field) = explode('.', $index, 2);
        }

        $target = $index;
        if (!is_numeric($index)) {
            if (is_array($value)) {
                $value['type'] = $index;
            }
            $index = null;
            if (isset($this->data[$entityType][$path])) {
                foreach ((array)$this->data[$entityType][$path] as $nodeIndex => $node) {
                    if (isset($node['type']) && $node['type'] === $target) {
                        $index = $nodeIndex;
                        break;
                    }
                }
            }
        }

        // No index found, new entry
        if ($index === null) {
            $this->addNewEntry($entityType, $path, $field, $target, $value);
            return;
        }

        // Use found index
        if ($field === null) {
            $this->data[$entityType][$path][$index] = $value;
            return;
        }
        $this->data[$entityType][$path][$index][$field] = $value;
    }

    /**
     * @param string $entityType
     * @param string $path
     * @param string|null $field
     * @param string $target
     * @param mixed $value
     */
    private function addNewEntry($entityType, $path, $field, $target, $value)
    {
        if ($field === null) {
            $this->data[$entityType][$path][] = $value;
            return;
        }
        $value = [
            $field => $value
        ];
        if (!is_numeric($target)) {
            $value['type'] = $target;
        }
        $this->data[$entityType][$path][] = $value;
    }

    /**
     * Check if a certain entity type exists in the dataBag
     *
     * @param string $entityType
     *
     * @return bool true if the entity type exists
     */
    public function hasEntityData($entityType)
    {
        return isset($this->data[$entityType]);
    }

    /**
     * Remove a property from the bag.
     *
     * @param string $path path to the target (see get() for examples)
     * @param bool $removeAll remove all when the index is numeric (to prevent a new value after re-indexing)
     *
     * @throws InvalidDataBagPathException
     */
    public function remove($path, $removeAll = true)
    {
        $path = (string)$path;
        $this->guardAgainstInvalidPath($path);

        list($entityType, $path) = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            if (!isset($this->data[$entityType][$path])) {
                return;
            }
            $this->data[$entityType][$path] = null;
            return;
        }

        $this->removeIndexed($path, $entityType, $removeAll);
    }

    /**
     * @param string $path
     * @param string $entityType
     * @param bool $removeAll
     */
    private function removeIndexed($path, $entityType, $removeAll)
    {
        list($path, $index) = explode('.', $path);

        // Target doesn't exist, nothing to remove
        if (empty($this->data[$entityType][$path])) {
            return;
        }

        if (is_numeric($index)) {
            $index = (int)$index;
            if ($removeAll) {
                // Remove all (higher) values to prevent a new value after re-indexing
                if ($index === 0) {
                    $this->data[$entityType][$path] = null;
                    return;
                }
                $this->data[$entityType][$path] = array_slice($this->data[$entityType][$path], 0, $index);
                return;
            }
            unset($this->data[$entityType][$path][$index]);
        } else {
            // Filter out all nodes of the specified type
            $this->data[$entityType][$path] = array_filter(
                $this->data[$entityType][$path],
                static function ($node) use ($index) {
                    return empty($node['type']) || $node['type'] !== $index;
                }
            );
        }

        // If we end up with an empty array make it NULL
        if (empty($this->data[$entityType][$path])) {
            $this->data[$entityType][$path] = null;
            return;
        }

        // Re-index
        $this->data[$entityType][$path] = array_values($this->data[$entityType][$path]);
    }

    /**
     * Check if the initial data has changed
     *
     * @param string $entityType entity type to check
     *
     * @return bool|null true if changed, false if not and null if the entity type is not set
     */
    public function isDirty($entityType)
    {
        if (!isset($this->data[$entityType])) {
            return null;
        }
        if (empty($this->hashes[$entityType])) {
            return true;
        }
        return $this->hashes[$entityType] !== md5(serialize($this->getState($entityType)));
    }

    /**
     * Get the raw data array
     *
     * @param string|null $entityType only get the specified type (optional)
     *
     * @return array
     */
    public function getState($entityType = null)
    {
        if ($entityType === null) {
            return $this->data;
        }
        return isset($this->data[$entityType]) ? $this->data[$entityType] : [];
    }

    /**
     * @param string $path
     */
    private function guardAgainstInvalidPath($path)
    {
        if ($path === '' // empty
            || strpos($path, '..') !== false // has .. somewhere
            || substr($path, -1) === '.' // ends with .
            || in_array(strpos($path, '.'), [false, 0], true) // starts with or doesnt have any .
        ) {
            throw new InvalidDataBagPathException('Invalid path provided: ' . $path);
        }
    }

}

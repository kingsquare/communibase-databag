<?php

declare(strict_types=1);

namespace Communibase\DataBag;

final class DataRemover
{
    /**
     * @var array<string,array>
     */
    private $data;

    /**
     * Remove a property from the bag.
     *
     * @param string $path path to the target (see get() for examples)
     *
     * @throws InvalidDataBagPathException
     */
    public function removeByPath(array &$data, string $path): void
    {
        $this->data = &$data;

        [$entityType, $path] = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            if (!isset($this->data[$entityType][$path])) {
                return;
            }
            $data[$entityType][$path] = null;
            return;
        }

        [$path, $index] = explode('.', $path);

        // Target doesn't exist, nothing to remove
        if (empty($this->data[$entityType][$path])) {
            return;
        }

        // Sub-path
        if (isset($this->data[$entityType][$path][$index]) && !is_numeric($index) && strpos($index, '.') === false) {
            $this->data[$entityType][$path][$index] = null;
            return;
        }

        $this->removeIndexed($entityType, $path, $index);
    }

    private function removeIndexed(string $entityType, string $path, string $index): void
    {
        if (is_numeric($index)) {
            // Remove all (higher) values to prevent a new value after re-indexing
            if ((int)$index === 0) {
                $this->data[$entityType][$path] = null;
                return;
            }
            $this->data[$entityType][$path] = \array_slice($this->data[$entityType][$path], 0, (int)$index);
            return;
        }

        // Filter out all nodes of the specified type
        $this->data[$entityType][$path] = array_filter(
            $this->data[$entityType][$path],
            static function ($node) use ($index) {
                return empty($node['type']) || $node['type'] !== $index;
            }
        );

        // If we end up with an empty array make it NULL
        if (empty($this->data[$entityType][$path])) {
            $this->data[$entityType][$path] = null;
            return;
        }

        // Re-index
        $this->data[$entityType][$path] = array_values($this->data[$entityType][$path]);
    }
}

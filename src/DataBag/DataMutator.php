<?php

declare(strict_types=1);

namespace Communibase\DataBag;

final class DataMutator
{
    /**
     * @var array<string,array>
     */
    private $data;

    /**
     * @param mixed $value
     */
    public function setByPath(array &$data, string $path, $value): void
    {
        $this->data = &$data;

        [$entityType, $path] = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            $data[$entityType][$path] = $value;
            return;
        }

        // Indexed
        $this->setIndexed($entityType, $path, $value);
    }

    /**
     * @param mixed $value
     */
    private function setIndexed(string $entityType, string $path, $value): void
    {
        [$path, $index] = explode('.', $path, 2);

        $field = null;
        if (strpos($index, '.') > 0) {
            [$index, $field] = explode('.', $index, 2);
        }

        $target = $index;
        if (!is_numeric($index)) {
            if (\is_array($value)) {
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
     * @param mixed $value
     */
    private function addNewEntry(string $entityType, string $path, ?string $field, string $target, $value): void
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
}

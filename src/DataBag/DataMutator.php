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

        [$path, $index] = explode('.', $path, 2);

        // Sub-path
        if (!is_numeric($index) && strpos($index, '.') === false && !$this->isAssocativeArray($value)) {
            $this->data[$entityType][$path][$index] = $value;
            return;
        }

        $this->setIndexed($entityType, $path, $index, $value);
    }

    /**
     * @param mixed $value
     */
    private function setIndexed(string $entityType, string $path, string $index, $value): void
    {
        $field = null;
        if (strpos($index, '.') > 0) {
            [$index, $field] = explode('.', $index, 2);
        }

        $targetIndex = $index;
        if (!is_numeric($targetIndex)) {
            if (\is_array($value)) {
                $value['type'] = $targetIndex;
            }
            $targetIndex = null;
            if (isset($this->data[$entityType][$path])) {
                foreach ((array)$this->data[$entityType][$path] as $nodeIndex => $node) {
                    if (isset($node['type']) && $node['type'] === $index) {
                        $targetIndex = $nodeIndex;
                        break;
                    }
                }
            }
        }

        // No index found, new entry
        if ($targetIndex === null) {
            $this->addNewEntry($entityType, $path, $field, $index, $value);
            return;
        }

        // Use found index
        if ($field === null) {
            $this->data[$entityType][$path][$targetIndex] = $value;
            return;
        }
        $this->data[$entityType][$path][$targetIndex][$field] = $value;
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

    /**
     * @param mixed $value
     */
    private function isAssocativeArray($value): bool
    {
        return \is_array($value) && (array_keys($value) !== range(0, \count($value) - 1));
    }
}

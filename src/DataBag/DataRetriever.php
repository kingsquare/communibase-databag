<?php

declare(strict_types=1);

namespace Communibase\DataBag;

final class DataRetriever
{
    /**
     * @param array<string,array> $data
     * @param mixed $default
     *
     * @return mixed
     */
    public function getByPath(array $data, string $path, $default = null)
    {
        [$entityType, $path] = explode('.', $path, 2);

        // Direct property
        if (strpos($path, '.') === false) {
            return $data[$entityType][$path] ?? $default;
        }

        [$property, $index] = explode('.', $path, 2);

        if (empty($data[$entityType][$property])) {
            return $default;
        }

        // Sub-path
        if (\is_string($index) && \array_key_exists($index, $data[$entityType][$property])) {
            return $data[$entityType][$property][$index] ?? $default;
        }

        // Indexed
        return $this->getIndexedValue((array)$data[$entityType][$property], $index, $default);
    }

    /**
     * @param array<string,array> $properties
     * @param mixed $default
     *
     * @return mixed
     */
    private function getIndexedValue(array $properties, string $index, $default)
    {
        $targetProperty = null;
        if (strpos($index, '.') > 0) {
            [$index, $targetProperty] = explode('.', $index, 2);
        }

        $translatedIndex = $index;

        if (!is_numeric($index)) {
            $translatedIndex = null;
            foreach ($properties as $nodeIndex => $node) {
                if (isset($node['type']) && $node['type'] === $index) {
                    $translatedIndex = $nodeIndex;
                    break;
                }
            }
        }

        if ($translatedIndex === null) {
            return $default;
        }

        if ($targetProperty === null) {
            return $properties[$translatedIndex] ?? $default;
        }

        return $properties[$translatedIndex][$targetProperty] ?? $default;
    }
}

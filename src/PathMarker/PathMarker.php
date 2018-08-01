<?php

namespace uuf6429\DockerEtl\PathMarker;

class PathMarker
{
    protected $paths = [];

    /**
     * @param object|array $value
     */
    public function mapToPaths($value)
    {
        if (!is_object($value) && !is_array($value)) {
            throw new \InvalidArgumentException(
                'Value to map must be an object or array, got ' . gettype($value) . ' instead.'
            );
        }

        $this->doMapToPaths('', $value);
    }

    /**
     * @param string $basePath
     * @param object|array $value
     */
    protected function doMapToPaths($basePath, $value)
    {
        if (is_array($value)) {
            $scalarsOnly = true;
            foreach ($value as $key => $subValue) {
                if (is_array($subValue) || is_object($subValue)) {
                    $this->doMapToPaths("{$basePath}[{$key}]", $subValue);
                    $scalarsOnly = false;
                }
            }
            if (!$scalarsOnly) {
                return;
            }
        } elseif (is_object($value)) {
            foreach (get_object_vars($value) as $prp => $subValue) {
                $this->doMapToPaths($basePath ? "{$basePath}.{$prp}" : $prp, $subValue);
            }
            return;
        }

        if ($basePath !== '') {
            $this->paths[$basePath] = false;
        }
    }

    /**
     * @param string[] $paths
     */
    public function addPaths(array $paths)
    {
        foreach ($paths as $path) {
            $this->paths[$path] = false;
        }
    }

    /**
     * @param string[] $paths
     */
    public function removePaths(array $paths)
    {
        foreach ($paths as $path) {
            unset($this->paths[$path]);
        }
    }

    public function removeAllPaths()
    {
        $this->paths = [];
    }

    /**
     * @param string $path
     */
    public function markPath($path)
    {
        if (!array_key_exists($path, $this->paths)) {
            throw new \InvalidArgumentException("Cannot mark path \"$path\" since it does not exist.");
        }

        $this->paths[$path] = true;
    }

    public function unmarkAllPaths()
    {
        $this->paths = array_fill_keys(array_keys($this->paths), false);
    }

    /**
     * @return bool
     */
    public function hasUnmarkedPaths()
    {
        foreach ($this->paths as $marked) {
            if (!$marked) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    public function getUnmarkedPaths()
    {
        return array_keys(array_filter(
            $this->paths,
            function ($marked) {
                return !$marked;
            }
        ));
    }

    /**
     * @return string[]
     */
    public function getUniqueUnmarkedPaths()
    {
        return array_keys(
            array_flip(
                array_map(
                    function ($path) {
                        return preg_replace('/\\[[^\\]]*\\]/', '[*]', $path);
                    },
                    $this->getUnmarkedPaths()
                )
            )
        );
    }
}

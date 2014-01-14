<?php

namespace Encore\Config;

use Illuminate\Filesystem\Filesystem;

class Loader extends \Illuminate\Config\FileLoader
{
    protected $os;

    public function __construct(Filesystem $files, $defaultPath, $os)
    {
        $this->os = $os;

        parent::__construct($files, $defaultPath);
    }

    public function load($mode, $group, $namespace = null)
    {
        $items = array();

        // First we'll get the root configuration path for the mode which is
        // where all of the configuration files live for that namespace, as well
        // as any mode folders with their specific configuration items.
        $path = $this->getPath($namespace);

        if (is_null($path)) {
            return $items;
        }

        // First we'll get the main configuration file for the groups. Once we have
        // that we can check for any mode specific files, which will get
        // merged on top of the main arrays to make the environments cascade.
        $file = "{$path}/{$group}.php";

        if ($this->files->exists($file)) {
            $items = $this->files->getRequire($file);
        }

        // Now we'll check if we have an OS specific config file
        $file = "{$path}/{$this->os}/{$group}.php";

        if ($this->files->exists($file)) {
            $items = $this->mergeMode($items, $file);
        }

        // Check for a global mode config file (not OS specific)
        $file = "{$path}/{$mode}/{$group}.php";

        if ($this->files->exists($file)) {
            $items = $this->mergeMode($items, $file);
        }

        // And lastly we'll check if we have an mode and OS specific
        // config file and merge.
        $file = "{$path}/{$this->os}/{$mode}/{$group}.php";

        if ($this->files->exists($file)) {
            $items = $this->mergeMode($items, $file);
        }

        return $items;
    }

    protected function mergeMode(array $items, $file)
    {
        return array_merge_recursive($items, $this->files->getRequire($file));
    }
}
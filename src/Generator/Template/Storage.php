<?php

declare(strict_types=1);

namespace Matronator\Generator\Template;

use Matronator\Generator\FileGenerator;

class Storage
{
    public string $homeDir;
    public string $templateDir;
    public string $store;

    public function __construct()
    {
        $this->homeDir = Path::canonicalize('~/.mtrgen');
        $this->templateDir = Path::canonicalize('~/.mtrgen/templates');
        $this->store = Path::canonicalize('~/.mtrgen/templates.json');

        if (!FileGenerator::folderExist($this->homeDir)) {
            mkdir($this->homeDir, 0777, true);
        }
        if (!FileGenerator::folderExist($this->templateDir)) {
            mkdir($this->templateDir, 0777, true);
        }
        if (!file_exists($this->store)) {
            $this->createStore();
        }
    }

    /**
     * Save template to global store
     * @return boolean True if save is successful, false otherwise
     * @param string $filename
     * @param string|null $alias Alias to save the template under instead of the name defined inside the template
     */
    public function save(string $filename, ?string $alias = null): bool
    {
        $file = Path::canonicalize($filename);
        $basename = basename($file);

        if (!file_exists($file))
            return false;

        $this->saveEntry($alias ?? Parser::getName($file), $basename);
        copy($file, Path::canonicalize($this->templateDir . DIRECTORY_SEPARATOR . $basename));

        return true;
    }

    /**
     * Remove template from global store
     * @return boolean True if removed successfully, false otherwise
     * @param string $name Name under which the template is stored
     */
    public function remove(string $name): bool
    {
        $store = $this->loadStore();

        if (!isset($store->templates->{$name}))
            return false;

        $filename = $this->removeEntry($name);
        unlink(Path::canonicalize($this->templateDir . DIRECTORY_SEPARATOR . $filename));

        return true;
    }

    /**
     * Returns the template contents or false
     * @return string|false
     * @param string $name Name under which the template is stored
     */
    public function getContent(string $name): string
    {
        return file_get_contents($this->getFilename($name));
    }

    /**
     * Returns an object with properties `filename` (filename of the template)
     * and `contents` (contents of the template) or `null` if not found.
     * @return object|null
     * @param string $name Name under which the template is stored
     */
    public function load(string $name): ?object
    {
        $store = $this->loadStore();

        if (!isset($store->templates->{$name}))
            return null;

        return (object) [
            'filename' => $this->getFilename($name),
            'contents' => $this->getContent($name),
        ];
    }

    /**
     * Returns the filename of the template
     * @return string|null
     * @param string $name Name under which the template is stored
     */
    public function getFilename(string $name): ?string
    {
        $store = $this->loadStore();

        if (!isset($store->templates->{$name}))
            return null;

        $filename = $store->templates->{$name};

        return $filename;
    }

    /**
     * Returns the full canonicalized path of the template
     * @return string|null
     * @param string $name Name under which the template is stored
     */
    public function getFullPath(string $name): ?string
    {
        return Path::canonicalize($this->templateDir . DIRECTORY_SEPARATOR . $this->getFilename($name));
    }

    private function createStore(): void
    {
        $store = (object) [
            'templates' => (object)[],
        ];

        $this->saveStore($store);
    }

    private function loadStore(): object
    {
        return json_decode(file_get_contents($this->store));
    }

    private function saveStore(object $store): void
    {
        file_put_contents($this->store, json_encode($store));
    }

    private function saveEntry(string $name, string $filename): void
    {
        $store = $this->loadStore();
        $store->templates->{$name} = $filename;
        $this->saveStore($store);
    }

    /**
     * Removes an entry from the store
     * @return string The filename of the removed template.
     * @param string $name
     */
    private function removeEntry(string $name): string
    {
        $store = $this->loadStore();

        $filename = $store->templates->{$name};
        unset($store->templates->{$name});
        $this->saveStore($store);

        return $filename;
    }
}

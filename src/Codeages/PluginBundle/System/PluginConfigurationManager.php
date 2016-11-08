<?php

namespace Codeages\PluginBundle\System;

class PluginConfigurationManager implements PluginConfigurationManagerInterface
{
    protected $filepath;

    protected $config;

    protected $rootDir;

    public function __construct($rootDir)
    {
        $this->rootDir = rtrim($rootDir, "\/");
        $this->filepath = $this->rootDir . '/config/plugin.php';
        if (!file_exists($this->filepath)) {
            $this->config = array();
        } else {
            $this->config = require $this->filepath;
        }

    }

    public function getActiveThemeName()
    {
        return empty($this->config['active_theme_name']) ? null : $this->config['active_theme_name'];
    }

    public function getActiveThemeDirectory()
    {
        $name = $this->getActiveThemeName();
        if (empty($name)) {
            return null;
        }

        return sprintf('%s/web/themes/%s', dirname($this->rootDir), $name);
    }

    public function setActiveThemeName($name)
    {
        $this->config['active_theme_name'] = $name;
        return $this;
    }

    public function getInstalledPlugins()
    {
        return empty($this->config['installed_plugins']) ? array() : $this->config['installed_plugins'];
    }

    public function setInstalledPlugins($plugins)
    {
        $this->config['installed_plugins'] = $plugins;
        return $this;
    }

    public function getInstalledPluginBundles()
    {
        $bundlues = array();
        $plugins = $this->getInstalledPlugins();

        foreach ($plugins as $plugin) {
            $code = ucfirst($plugin['code']);
            $class = "{$code}Plugin\\{$code}Plugin";
            $bundlues[] = new $class();
        }

        return $bundlues;
    }

    public function save()
    {
        $content = "<?php \n return " . var_export($this->config, true) . ";";
        $saved = file_put_contents($this->filepath, $content);

        if ($saved === false) {
            throw new \RuntimeException("Save plugin configuration ({$this->filepath}) failed, may be this file is not writeable.");
        }

        return $this;
    }

}
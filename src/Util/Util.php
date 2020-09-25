<?php

namespace WPTest\Util;

class Util
{
    const WP_PHPUNIT_PACKAGE = 'wp-phpunit/wp-phpunit';
    const DEFAULT_THEME = 'twentytwenty';

    protected $project_directory;
    protected $composer_data;

    public function getProjectDirectory()
    {
        if (!isset($this->project_directory)) {
            $project_directory = false;
            if (is_file(getcwd() . '/composer.json')) {
                $project_directory = getcwd();
            } else {
                $olddir = false;
                $dir = __DIR__;
                while ($dir != '/' && $dir != $olddir) {
                    $olddir = $dir;
                    $dir = dirname($dir);
                    if (is_file($dir . '/composer.json')) {
                        $project_directory = $dir;
                        break;
                    }
                }
            }
            $this->project_directory = $project_directory;
        }
        return $this->project_directory;
    }

    public function getComposerData()
    {
        if (!isset($this->composer_data)) {
            $project_directory = $this->getProjectDirectory();
            $this->composer_data = $project_directory ?
                json_decode(file_get_contents($project_directory . '/composer.json'), true) : [];


        }
        return $this->composer_data;
    }

    public function getVendorDirectory()
    {
        $composer_data = $this->getComposerData();
        return isset($composer_data['config']['vendor-dir']) ?
            $composer_data['config']['vendor-dir'] : 'vendor';
    }

    public function getPSR4Namespace()
    {
        return trim($this->getAutoloadKey(), '\\');
    }

    public function getPSR4Source()
    {
        return trim($this->getAutoloadPath(), '/');
    }

    public function getWPCoreDirectory()
    {
        $composer_data = $this->getComposerData();

        $wp_path = isset($composer_data['extra']['wordpress-install-dir']) ?
            $composer_data['extra']['wordpress-install-dir'] : 'wordpress';

        return sprintf('%s/%s', $this->getProjectDirectory(), $wp_path);
    }

    public function getTestsIncludesDirectory()
    {
        return sprintf('%s/%s/includes', $this->getVendorDirectory(), static::WP_PHPUNIT_PACKAGE);
    }

    public function getWPContentDirectory()
    {
        $project_directory = $this->getProjectDirectory();
        if (is_dir($project_directory . '/wp-content')) {
            return 'wp-content';
        }
        return sprintf('%s/%s', $this->getWPDevelopDirectory(), '/wp-content');
    }

    public function getWPActiveTheme()
    {
        return preg_match('@/themes/(.*?)/.*?@', $this->getAutoloadPath(), $matches) ?
            $matches[1] : static::DEFAULT_THEME;
    }

    protected function getAutoloadKey()
    {
        $composer_data = $this->getComposerData();
        return isset($composer_data['autoload']['psr-4']) ? key($composer_data['autoload']['psr-4']) : '';
    }

    protected function getAutoloadPath()
    {
        $composer_data = $this->getComposerData();
        return isset($composer_data['autoload']['psr-4']) ? reset($composer_data['autoload']['psr-4']) : '';
    }

}
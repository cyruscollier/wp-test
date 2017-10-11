<?php

namespace WPTest\Util;

class Util
{
    const PATH_WP_DEVELOP = 'cyruscollier/wordpress-develop';

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
        $composer_data = $this->getComposerData();
        return isset($composer_data['autoload']['psr-4']) ?
            trim(key($composer_data['autoload']['psr-4']), '\\') : '';
    }

    public function getWPDevelopDirectory()
    {
        return sprintf('%s/%s', $this->getVendorDirectory(), static::PATH_WP_DEVELOP);
    }

    public function getWPContentDirectory()
    {
        $project_directory = $this->getProjectDirectory();
        if (is_dir($project_directory . '/wp-content')) {
            return 'wp-content';
        }
        return sprintf('%s/%s', $this->getWPDevelopDirectory(), 'src/wp-content');
    }

    public function getWPActiveTheme()
    {
        return $this->getWPContentDirectory() . '/themes/twentyseventeen';
    }

}
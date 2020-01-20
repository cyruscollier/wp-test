<?php

namespace WPTest\Test;

class PHPUnitBootstrap
{
    protected $install_plugins = [];
    protected $options;

    public function __construct()
    {
        $this->options =& $GLOBALS['wp_tests_options'];
    }

    public function load() {
        require_once WP_INCLUDE_PATH . '/functions.php';
        if (defined('WP_TESTS_ACTIVATE_PLUGINS') && WP_TESTS_ACTIVATE_PLUGINS) {
            $this->options['active_plugins'] = [];
            tests_add_filter('muplugins_loaded', [$this, 'setActivePlugins']);
            tests_add_filter('plugins_loaded', [$this, 'installSelectedPlugins'], 99);
        }
        if (defined('WP_TESTS_ACTIVATE_THEME') && WP_TESTS_ACTIVATE_THEME) {
            $this->options['stylesheet'] = $this->options['template'] = [];
            tests_add_filter('plugins_loaded', [$this, 'setActiveTheme']);
        }
    }

    public function setActivePlugins() {
        global $wp_tests_options;
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = get_plugins();
        echo "Loading plugins:\n";
        $plugins_to_activate = defined('WP_TESTS_INSTALL_PLUGINS') ? explode(',', WP_TESTS_INSTALL_PLUGINS) : [];
        foreach ($plugins as $name => $plugin_data) {
            echo "--{$plugin_data['Name']} {$plugin_data['Version']}\n";
            if (in_array(dirname($name), $plugins_to_activate)) {
                $this->install_plugins[] = $name;
            }
        }
        $this->options['active_plugins'] = array_keys($plugins);
    }

    public function installSelectedPlugins()
    {
        foreach ($this->install_plugins as $plugin) {
            do_action('activate_' . $plugin);
        }
    }

    public function setActiveTheme() {
        $themes = wp_get_themes();
        echo "Loading theme:\n";
        $theme = $themes[WP_TESTS_ACTIVATE_THEME];
        $this->options['stylesheet'] = $theme->stylesheet;
        $this->options['template'] = $theme->template;
        echo "--{$theme->name} {$theme->version}\n";
    }
}
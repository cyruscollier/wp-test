<?php

namespace WPTest\Test;

use WPTest\Util\Util;

class PHPUnitBootstrap extends TestRunnerBootstrap
{
    protected $install_plugins = [];
    protected $options;
    protected $is_integration_group;

    public function __construct()
    {
        parent::__construct();
        $this->options =& $GLOBALS['wp_tests_options'];
        $this->require('functions.php');
        $this->is_integration_group = in_array('integration', getopt('',['group::']));
    }

    protected function getIncludePath()
    {
        return $this->util->getWPDevelopDirectory() . '/tests/phpunit/includes';
    }

    public function beforePluginsLoaded(callable $callable, $priority = 10)
    {
        tests_add_filter('muplugins_loaded', $callable, $priority);
    }

    public function afterPluginsLoaded(callable $callable, $priority = 10)
    {
        tests_add_filter('plugins_loaded', $callable, $priority);
    }

    public function load() {

        if (defined('WP_TESTS_ACTIVATE_PLUGINS') && WP_TESTS_ACTIVATE_PLUGINS) {
            $this->options['active_plugins'] = [];
            $this->beforePluginsLoaded([$this, 'setActivePlugins']);
            $this->afterPluginsLoaded([$this, 'installSelectedPlugins'], 99);
        }
        if (defined('WP_TESTS_ACTIVATE_THEME') && WP_TESTS_ACTIVATE_THEME) {
            $this->options['stylesheet'] = $this->options['template'] = WP_TESTS_ACTIVATE_THEME;
            $this->afterPluginsLoaded([$this, 'setActiveTheme']);
        }
        if (defined('WP_TESTS_STUB_EXTERNAL_HTTP') && WP_TESTS_STUB_EXTERNAL_HTTP) {
            tests_add_filter('pre_http_request', function($pre, $parsed_args, $url) {
                if ($this->is_integration_group && did_action('init')) {
                    return $pre;
                }
                $response = ['code' => 200];
                $body = '';
                return compact('parsed_args', 'url', 'response', 'body');
            }, 10, 3);
        }
        $this->require('bootstrap.php');
    }

    public function setActivePlugins() {
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
            $network_wide = is_multisite() ;
            do_action('activate_plugin', $plugin, $network_wide);
            do_action('activate_' . $plugin, $network_wide);
            do_action('activated_plugin', $plugin, $network_wide);
        }
    }

    public function setActiveTheme() {
        $themes = wp_get_themes();
        echo "Loading theme:\n";
        $theme = $themes[WP_TESTS_ACTIVATE_THEME];
        echo "--{$theme->name} {$theme->version}\n";
    }
}
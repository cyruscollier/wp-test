<?php

namespace WPTest\Test;

class IntegrationBootstrap
{
    public function load() {
        require_once WP_INCLUDE_PATH . '/functions.php';
        tests_add_filter('muplugins_loaded', [$this, 'setActivePlugins']);
        tests_add_filter('plugins_loaded', [$this, 'setActiveTheme']);
        $GLOBALS['wp_tests_options'] = ['active_plugins' => [], 'stylesheet' => '', 'template' => ''];
    }

    public function setActivePlugins() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $plugins = get_plugins();
        echo "Loading plugins:\n";
        foreach ($plugins as $name => $plugin_data) {
            echo "--{$plugin_data['Name']} {$plugin_data['Version']}\n";
        }
        $GLOBALS['wp_tests_options']['active_plugins'] = array_keys($plugins);
    }

    public function setActiveTheme() {
        $themes = wp_get_themes();
        echo "Loading theme:\n";
        $theme = $themes[WP_TESTS_ACTIVE_THEME];
        $GLOBALS['wp_tests_options']['stylesheet'] = $theme->stylesheet;
        $GLOBALS['wp_tests_options']['template'] = $theme->template;
        echo "--{$theme->name} {$theme->version}\n";
    }
}
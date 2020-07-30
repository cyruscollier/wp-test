<?php

namespace WPTest\Test;

class PHPUnitBootstrap extends TestRunnerBootstrap
{
    protected $install_plugins = [];
    protected $options;
    protected $is_integration_group;
    protected $wp_tests_includes_path;

    public function __construct()
    {
        parent::__construct();
        $this->wp_tests_includes_path = $this->util->getWPDevelopDirectory() . '/tests/phpunit/includes';
        $this->options =& $GLOBALS['wp_tests_options'];
        $this->requireTestsIncludes('functions.php');
        $this->is_integration_group = in_array('integration', getopt('',['group::']));
    }

    public function requireTestsIncludes($filename)
    {
        require_once $this->wp_tests_includes_path . DIRECTORY_SEPARATOR. $filename;
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
        $this->requireTestsIncludes('bootstrap.php');
    }

    public function setActivePlugins() {
        $this->requireAdmin('includes/plugin.php');
        /* Include symlink if project is a plugin */
        $project_plugins = array_filter(glob($this->util->getProjectDirectory() . '/*.php'), function($file) {
            $plugin_data = get_plugin_data( $file, false, false );
            return !empty($plugin_data['Name']);
        });
        $project_plugin = reset($project_plugins);
        if ($project_plugin && ($link = WP_PLUGIN_DIR . '/' . basename($project_plugin)) && !file_exists($link)) {
            symlink($project_plugin, $link);
        }
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
        /* Register parent directory if project is a theme */
        $theme_data = get_file_data($this->util->getProjectDirectory() . '/style.css', ['Name' => 'Theme Name'], 'theme');
        if (!empty($theme_data['Name'])) {
            register_theme_directory(dirname($this->util->getProjectDirectory()));
        }
        $themes = wp_get_themes(['errors' => null]);
        $theme = $themes[WP_TESTS_ACTIVATE_THEME];

        if ($theme->errors()) {
            echo "Theme Activation Error: " . $theme->errors()->get_error_message();
        }
        echo "Loading theme:\n";
        echo "--{$theme->name} {$theme->version}\n";
    }
}
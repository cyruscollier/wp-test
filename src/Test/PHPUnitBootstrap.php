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
        $this->requireTestsIncludes('bootstrap.php');
    }

    public function setActivePlugins() {
        $plugin_paths = defined('WP_TESTS_ADDITIONAL_PLUGINS') && !empty(WP_TESTS_ADDITIONAL_PLUGINS) ?
            array_map('trailingslashit', explode(',', WP_TESTS_ADDITIONAL_PLUGINS)) : [];
        $plugin_paths[] = '';
        $this->requireAdmin('includes/plugin.php');
        /* Include symlink if project is a plugin */
        foreach ($plugin_paths as $path) {
            $glob = sprintf('%s/%s*.php',$this->util->getProjectDirectory(), ltrim($path, '/'));
            $entry_files = array_filter(glob($glob), function ($file) {
                $plugin_data = get_plugin_data($file, false, false);
                return !(empty($plugin_data['Name']) || is_link(WP_PLUGIN_DIR . '/' . basename($file)));
            });
            array_walk($entry_files, function($file) {
                symlink($file, WP_PLUGIN_DIR . '/' . basename($file));
            });
        }
        wp_cache_delete( 'plugins', 'plugins' );
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
        if (is_readable($this->util->getProjectDirectory() . '/style.css')) {
            $theme_data = get_file_data($this->util->getProjectDirectory() . '/style.css', ['Name' => 'Theme Name'], 'theme');
            if (!empty($theme_data['Name'])) {
                register_theme_directory(dirname($this->util->getProjectDirectory()));
            }
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

<phpunit
        backupGlobals="false"
        backupStaticAttributes="false"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        bootstrap="{phpunit_bootstrap_path}/phpunit.php"
        beStrictAboutTestsThatDoNotTestAnything="true"
>
    <testsuites>
        <testsuite name="default">
            <directory suffix=".php">{phpunit_path}</directory>
        </testsuite>
    </testsuites>
    <groups>
        <exclude>
            <group>integration</group>
        </exclude>
    </groups>
    <php>
        <includePath>.</includePath>
        <const name="WP_TESTS_MULTISITE" value="0" />
        <const name="WP_RUN_CORE_TESTS" value="0" />
        <const name="WP_TESTS_ACTIVATE_THEME" value="{active_theme}" />
        <const name="WP_TESTS_ACTIVATE_PLUGINS" value="1" />
        <const name="WP_TESTS_INSTALL_PLUGINS" value="" />
        <const name="WP_TESTS_CONFIG_FILE_PATH" value="wp-tests-config.php" />
        <const name="WP_TESTS_STUB_EXTERNAL_HTTP" value="1" />
    </php>
    <listeners>
        <listener class="SpeedTrapListener" file="{path_wp_develop}/tests/phpunit/includes/listener-loader.php">
            <arguments>
                <array>
                    <element key="slow_threshold">
                        <integer>150</integer>
                    </element>
                </array>
            </arguments>
        </listener>
    </listeners>
</phpunit>
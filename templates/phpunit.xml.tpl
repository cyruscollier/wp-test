<phpunit
        backupGlobals="false"
        backupStaticAttributes="false"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        bootstrap="{path_wp_tests}/bootstrap/phpunit.php"
        beStrictAboutTestsThatDoNotTestAnything="true"
>
    <testsuites>
        <testsuite name="default">
            <directory suffix=".php">./{path_unit_tests}</directory>
        </testsuite>
        <testsuite name="integration">
            <directory suffix=".php">./{path_integration_tests}</directory>
        </testsuite>
    </testsuites>
    <groups>
        <exclude>
            <group>ajax</group>
            <group>ms-files</group>
            <group>ms-required</group>
            <group>external-http</group>
        </exclude>
    </groups>
    <php>
        <includePath>.</includePath>
        <const name="WP_RUN_CORE_TESTS" value="0" />
        <const name="WP_TESTS_ACTIVATE_THEME" value="{active_theme}" />
        <const name="WP_TESTS_ACTIVATE_PLUGINS" value="0" />
        <const name="WP_TESTS_INSTALL_PLUGINS" value="" />
        <const name="WP_TESTS_CONFIG_FILE_PATH" value="./wp-tests-config.php" />

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
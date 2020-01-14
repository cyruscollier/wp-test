<phpunit
        backupGlobals="false"
        backupStaticAttributes="false"
        colors="true"
        convertErrorsToExceptions="true"
        convertNoticesToExceptions="true"
        convertWarningsToExceptions="true"
        processIsolation="false"
        stopOnFailure="false"
        bootstrap="{path_wp_tests}/bootstrap/integration.php"
        beStrictAboutTestsThatDoNotTestAnything="true"
>
    <testsuites>
        <testsuite name="default">
            <directory suffix=".php">./{path_integration_tests}</directory>
            <exclude>./{path_integration_tests}/wp-tests-config.php</exclude>
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
        <const name="WP_TESTS_BOOTSTRAP_FILE" value="{path_wp_tests}/bootstrap/integration.php" />
        <const name="WP_TESTS_ACTIVE_THEME" value="{active_theme}" />
        <const name="WP_TESTS_ACTIVATE_PLUGINS" value="0" />
        <const name="WP_TESTS_INSTALL_PLUGINS" value="" />


    </php>
    <listeners>
        <listener class="SpeedTrapListener" file="vendor/wordpress/wordpress/tests/phpunit/includes/listener-loader.php">
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
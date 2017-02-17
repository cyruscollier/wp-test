<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false"
         bootstrap="{path_wp_develop}/tests/phpunit/includes/bootstrap.php"
>
    <php>
        <includePath>.</includePath>
        <const name="WP_RUN_CORE_TESTS" value="0" />
        <const name="WP_CONFIG_FILE_PATH" value="wp-tests-config.php" />
        <const name="WP_TEST_BOOTSTRAP_FILE" value="{path_wp_develop}/tests/phpunit/includes/bootstrap.php" />
    </php>
    <testsuites>
        <testsuite>
            <directory suffix=".php">./{path_integration_tests}</directory>
            <exclude>./{path_integration_tests}/wp-tests-config.php</exclude>
        </testsuite>
    </testsuites>
    <groups>
        <exclude>
            <group>ajax</group>
            <group>external-http</group>
        </exclude>
    </groups>
</phpunit>
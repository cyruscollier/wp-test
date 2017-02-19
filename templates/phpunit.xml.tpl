<phpunit backupGlobals="false"
         backupStaticAttributes="false"
         colors="true"
         convertErrorsToExceptions="true"
         convertNoticesToExceptions="true"
         convertWarningsToExceptions="true"
         processIsolation="false"
         stopOnFailure="false"
         syntaxCheck="false"
         bootstrap="{path_wp_tests}/bootstrap/integration.php"
>
    <php>
        <includePath>.</includePath>
        <const name="WP_RUN_CORE_TESTS" value="0" />
        <const name="WP_TESTS_BOOTSTRAP_FILE" value="{path_wp_tests}/bootstrap/integration.php" />
        <const name="WP_TESTS_ACTIVE_THEME" value="{active_theme}" />
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
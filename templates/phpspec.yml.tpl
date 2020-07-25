suites:
    {suite}:
        namespace: {namespace}
        psr4_prefix: {namespace}
        src_path: {source_path}
        spec_prefix: {unit_tests_prefix}
        spec_path: {path_unit_tests}

bootstrap: {path_wp_tests}/bootstrap/phpspec.php

extensions:
    PhpSpec\PhpMock\Extension\PhpMockExtension: ~

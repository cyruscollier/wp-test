suites:
    {suite}:
        namespace: {namespace}
        psr4_prefix: {namespace}
        spec_prefix: {spec_prefix}
        spec_path: {spec_path}

bootstrap: {path_wp_tests}/bootstrap/unit.php

extensions:
    - PhpSpec\PhpMock\Extension\PhpMockExtension

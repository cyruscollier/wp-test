suites:
    {suite}:
        namespace: {namespace}
        psr4_prefix: {namespace}
        spec_prefix: {spec_prefix}
        spec_path: {spec_path}

bootstrap: {path_unit_tests}/bootstrap.php

extensions:
    - PhpSpec\PhpMock\Extension\PhpMockExtension

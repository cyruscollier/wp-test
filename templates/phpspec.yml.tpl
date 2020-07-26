suites:
    {suite}:
        namespace: {namespace}
        psr4_prefix: {namespace}
        src_path: {source_path}
        spec_prefix: {spec_prefix}
        spec_path: {spec_path}

bootstrap: {spec_path}/phpspec.php

extensions:
    PhpSpec\PhpMock\Extension\PhpMockExtension: ~

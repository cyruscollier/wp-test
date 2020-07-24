watch:
    directories:
        - {source_path}
        - {path_unit_tests}
    fileMask: '*.php'
notifications:
    passingTests: false
    failingTests: false
phpunit:
    binaryPath: {vendor_path}/bin/phpunit
    arguments: '--stop-on-failure'
    timeout: 180

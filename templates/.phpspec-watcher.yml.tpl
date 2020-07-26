fileMask: '*.php'

checkInterval: 1

directories:
    - {source_path}
    - {path_unit_tests}

phpspec:
    binary: {vendor_path}/bin/phpspec
    arguments: []

notifications:
    onError: true
    onSuccess: true
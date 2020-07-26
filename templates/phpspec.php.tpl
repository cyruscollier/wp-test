<?php

use WPTest\Test\PHPSpecBootstrap;

$bootstrap = new PHPSpecBootstrap();
$bootstrap->load();
/*
Require project-specific, dependency-free classes from wp-includes or wp-admin.
Remember that WordPress itself is not loaded within phpspec.
*/
/*
$bootstrap->require('');
$bootstrap->requireAdmin('');
*/
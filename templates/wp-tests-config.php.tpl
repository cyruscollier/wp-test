<?php

define( 'WP_PHP_BINARY', 'php' );

define( 'DB_NAME', 'wordpress_develop' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );

define( 'ABSPATH', dirname( __FILE__ ) . '/{path_wp_develop}/src/' );
define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/{path_wp_content}' );
define( 'WP_DEBUG', true );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );
define( 'WPLANG', '' );

$table_prefix  = 'wptests_';   // Only numbers, letters, and underscores please!

define( 'WP_TESTS_DOMAIN', 'example.org' );
define( 'WP_TESTS_EMAIL', 'admin@example.org' );
define( 'WP_TESTS_TITLE', 'Test Blog' );


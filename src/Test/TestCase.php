<?php

namespace WPTest\Test;

use WPTest\Test\Traits;

class TestCase extends \WP_UnitTestCase {

    use Traits\UrlUtilsTrait;
    use Traits\AssertsFilters;
    use Traits\AssertsHtml;

}

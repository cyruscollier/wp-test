<?php

namespace WPTest\Test;

class TestCase extends \WP_UnitTestCase {

    /**
     *
     * @param string $hook
     * @param mixed $call either function name or object instance
     * @param string $method_name
     */
    public static function assertHasFilter( $hook, $call, $method_name = null ) {

        self::assertThat( $hook, new HasFilterConstraint( $call, $method_name ) );
    }

    public static function assertHasAction( $hook, $call, $method_name = null ) {

        self::assertHasFilter( $hook, $call, $method_name );
    }

    /**
     *
     * @param string $hook
     * @param int $count
     */
    public static function assertDidAction( $hook, $count = 1 ) {

        self::assertThat( $hook, new DidActionConstraint( $count ) );
    }

    /**
     *
     * @param string $hook
     */
    public static function assertDidNotDoAction( $hook ) {

        self::assertDidAction( $hook, 0 );
    }

    /**
     * Asserts that two HTML strings are equal.
     *
     * @param string $expectedHTML
     * @param string $actualHTML
     * @param string $message
     */
    public function assertHTMLEquals($expectedHTML, $actualHTML, string $message = ''): void
    {

        $expected = sprintf('<xml-root>%s</xml-root>', trim($expectedHTML));
        $actual = sprintf('<xml-root>%s</xml-root>', trim($actualHTML));

        $this->assertXmlStringEqualsXmlString($expected, $actual, $message);
    }

    public function get_object_url( $object ) {
        if ( !empty( $object->user_login ) )
            return get_author_posts_url( $object->ID );
        if ( !empty( $object->term_id ) )
            return get_term_link( $object->term_id, $object->taxonomy );
        return get_permalink( $object->ID );
    }

    public function go_to( $url ) {
        if ( false === strpos( $url, 'http' ) ) {
            $url = sprintf( 'http://%s/%s', WP_TESTS_DOMAIN, ltrim( $url, '/' ) );
        }
        parent::go_to( $url );
    }

    public function go_to_search( $query = [] ) {
        $query = array_merge( ['s' => ''], $query );
        $url = '?' . http_build_query( $query );
        $this->go_to( $url );
    }

    public function go_to_object_url( $object ) {
        $this->go_to( $this->get_object_link( $object ) );
    }

}

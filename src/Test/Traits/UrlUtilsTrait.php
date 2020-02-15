<?php

namespace WPTest\Test\Traits;

trait UrlUtilsTrait
{
    public function getObjectUrl($object) {
        if (!empty($object->user_login))
            return get_author_posts_url($object->ID);
        if (!empty($object->term_id))
            return get_term_link($object->term_id, $object->taxonomy);
        return get_permalink($object->ID);
    }

    public function goToPath($url) {
        if (false === strpos($url, 'http')) {
            $url = sprintf('http://%s/%s', WP_TESTS_DOMAIN, ltrim($url, '/'));
        }
        $this->go_to($url);
    }

    public function goToSearch($query = []) {
        $query = array_merge(['s' => ''], $query);
        $url = '?' . http_build_query($query);
        $this->goToPath($url);
    }

    public function goToObjectUrl($object) {
        $this->goToPath($this->getObjectUrl($object));
    }
}
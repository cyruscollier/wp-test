<?php

namespace WPTest\Test\Traits;

use DOMDocument;
use PHPUnit\Framework\Exception;

trait AssertsHtml
{
    /**
     * Asserts that two HTML strings are equal.
     *
     * @param string $expectedHTML
     * @param string $actualHTML
     * @param string $message
     */
    public function assertHTMLEquals($expectedHTML, $actualHTML, string $message = ''): void
    {
        $this->assertEquals($this->createHTMLDocument($expectedHTML), $this->createHTMLDocument($actualHTML), $message);
    }

    protected function createHTMLDocument($html)
    {
        $document = new DOMDocument;
        $document->preserveWhiteSpace = false;
        $internal  = \libxml_use_internal_errors(true);
        $message   = '';
        $reporting = \error_reporting(0);
        $html = str_replace(["\n", "\t", "\r"], ['', '', ''], $html);
        $loaded = $document->loadHTML($html, LIBXML_NOBLANKS);

        foreach (\libxml_get_errors() as $error) {
            $message .= "\n" . $error->message;
        }

        \libxml_use_internal_errors($internal);
        \error_reporting($reporting);

        if ($loaded === false) {

            if ($message === '') {
                $message = 'Could not load XML for unknown reason';
            }

            throw new Exception($message);
        }

        return $document;
    }
}
<?php

require_once dirname(__FILE__) . '/../index.php';

/**
 * Test class for Indexer.
 * Generated by PHPUnit on 2012-06-08 at 23:16:27.
 */
class IndexerTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Indexer
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp() {
        $this->object = new Indexer;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown() {
        
    }

    /**
     * @covers Indexer::info
     * @todo Implement testInfo().
     */
    public function testInfo() {
        $this->assertFalse(TRUE);
    }

}

?>

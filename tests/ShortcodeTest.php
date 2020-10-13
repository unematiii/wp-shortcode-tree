<?php
use \Wordpress\Shortcode;

class ShortcodeTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void {
        // make a [test] shortcode available in all tests
        add_shortcode('test', null);
    }

    public function test_constructorShouldWork() {
        $attributes = array(
            'lorem' => 'ipsum dolor',
            'foo' => 'bar',
        );
        $tag = new Shortcode('test', $attributes, 'Some content');

        $expected_string = '[test lorem="ipsum dolor" foo="bar"]Some content[/test]';

        $this->assertEquals($expected_string, (string)$tag);

        $this->assertEquals('test', $tag->getName());
        $this->assertEquals($attributes, $tag->atts());
        $this->assertEquals('Some content', $tag->getContent());
        $this->assertFalse($tag->getClosed());
        $this->assertCount(0, $tag->shortcodes());
        $this->assertNull($tag->getparent());
    }

    public function test_constructorWithAllArgumentsShouldWork() {

        $outer = new Shortcode('outer');
        $inner = new Shortcode('inner');
        $inner->setClosed();
        $another_inner = new Shortcode('another_inner');

        $tag = new Shortcode('test', array(), '', array($inner, $another_inner), $outer);

        $expected_string = '[test][inner][/inner][another_inner][/test]';
        $this->assertEquals($expected_string, (string)$tag);

        $this->assertEquals($outer, $tag->getParent());
    }
}
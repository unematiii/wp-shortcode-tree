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

    public function test_fromStringShouldWork() {
        add_shortcode('foobar_baz', null);
        add_shortcode('bazbar_foo', null);
        add_shortcode('lorem_ipsum', null);
        $string_in = '
            [foobar_baz][/foobar_baz]
            [bazbar_foo]
                [lorem_ipsum dolor="sit amet"]
                    consectetuer adipiscing
                [/lorem_ipsum]
            [/bazbar_foo]';

        $tags = Shortcode::fromString($string_in);

        $this->assertCount(2, $tags);
        $this->assertEquals('bazbar_foo', $tags[1]->getName());
        $this->assertCount(1, $tags[1]->shortcodes());

        $expected_string = '[lorem_ipsum dolor="sit amet"]
                    consectetuer adipiscing
                [/lorem_ipsum]';
        $this->assertEquals($expected_string, (string)$tags[1]->shortcodes()[0]);
    }

    public function test_setNameShouldWork() {
        $tag = new Shortcode('before');
        $tag->setClosed(true);
        $this->assertEquals('[before][/before]', $tag->__toString());
        $tag->setName('after');
        $this->assertEquals('[after][/after]', $tag->__toString());
    }

    public function test_findNthOccurrenceShouldWork() {
        add_shortcode('outer', null);
        add_shortcode('inner_one', null);
        add_shortcode('inner_two', null);
        add_shortcode('another_inner', null);
        add_shortcode('even_deeper', null);
        add_shortcode('this_is_it', null);


        $tags = Shortcode::fromString('
            [outer]
                [inner_one][/inner_one]
                [inner_two][another_inner][this_is_it n="1"][/inner_two]
                [this_is_it n="2"][/this_is_it]
                [another_inner]
                    [even_deeper]
                        [this_is_it n="3"]Found me![/this_is_it]
                    [/even_deeper]
                [/another_inner]
            [/outer]
        ');
        
        $this->assertCount(1, $tags);
        $tag = $tags[0];
        
        $first = $tag->findNthOccurrence ('this_is_it', 0);
        $this->assertEquals('1', $first->atts()['n']);

        $second = $tag->findNthOccurrence ('this_is_it', 1);
        $this->assertEquals('2', $second->atts()['n']);

        $third = $tag->findNthOccurrence ('this_is_it', 2);
        $this->assertEquals('3', $third->atts()['n']);
        $this->assertEquals('Found me!', $third->getContent());
    }
}
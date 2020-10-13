<?php
use \Wordpress\ShortcodeTree;

class ShortcodeTreeTest extends \PHPUnit\Framework\TestCase
{
    public function setUp(): void {
        // make a [test] shortcode available in all tests
        add_shortcode('test', null);
    }

    public function test_readmeExampleShouldWork()
    {
        add_shortcode('folder', null);
        add_shortcode('document', null);

        $post_content = '
        [folder name="SampleFolder"]
            [document type="image/png" size="64000" path="/path/to/image.png" name="File 1"]
            [document type="image/png" size="64000" path="/path/to/image.png" name="File 2"]
            [folder name="SampleFolder2"]
            [document type="image/png" size="64000" path="/path/to/image.png" name="File 3"]
            [document type="image/png" size="64000" path="/path/to/image.png" name="File 4"]
            [/folder]
        [/folder]
        ';

        $expected_content = '[folder name="SampleFolder"][document type="image/png" size="64000" path="/path/to/image.png" name="My File 1"][document type="image/png" size="64000" path="/path/to/image.png" name="My File 2"][folder name="SampleFolder2"][document type="image/png" size="64000" path="/path/to/image.png" name="My File 3"][document type="image/png" size="64000" path="/path/to/image.png" name="My File 4"][/folder]';

        $content = ShortcodeTree::fromString ( $post_content );
        $folder = $content->getRoot ();

        $documents = $folder->findAll('document');

        // Prepend 'My ' to filename
        foreach($documents as $doc) {
            $doc->attr('name', 'My ' . $doc->attr('name'));
        }
        
        $this->assertEquals($expected_content, $content);
    }

    public function test_immediatelyClosedEmptyTagsShouldWork() {
        add_shortcode('av_tab_section', null);
        add_shortcode('av_tab_sub_section', null);
        add_shortcode('custom_sc', null);

        $post_content = "[av_tab_section][av_tab_sub_section][custom_sc foo='bar'][/custom_sc][custom_sc][/custom_sc][/av_tab_sub_section][av_tab_section]";
        $expected_content = '[av_tab_section][av_tab_sub_section][custom_sc foo="bar"][/custom_sc][custom_sc][/custom_sc][/av_tab_sub_section][av_tab_section]';

        $content = ShortcodeTree::fromString ( $post_content );

        $this->assertEquals($expected_content, (string)$content);   
    }

    public function test_keyOnlyAttributesShouldWork() {
        add_shortcode('av_one_third', null);

        $post_content = "[av_one_third first min_height=''][test]";
        $expected_content = '[av_one_third first min_height=""][test]';

        $content = ShortcodeTree::fromString ( $post_content );

        $this->assertEquals($expected_content, (string)$content);   
    }

    public function test_constructorShouldBeAbleToSetRoot() {
        add_shortcode('outer', null);

        $post_content = "[test][outer][inner][/outer][/test]";
        $content = ShortcodeTree::fromString ( $post_content );
        $outer = $content->getRoot()->findAll('outer');
        $this->assertCount(1, $outer);

        $outerNode = $outer[0];
        $content = new ShortcodeTree($outerNode);
        $this->assertEquals($outerNode, $content->getRoot());
    }
}
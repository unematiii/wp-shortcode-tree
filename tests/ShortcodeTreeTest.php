<?php
use \Wordpress\ShortcodeTree;

class ShortcodeTreeTest extends \PHPUnit\Framework\TestCase
{
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
}
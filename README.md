## WordPress Shortcode Tree

[![Build Status](https://travis-ci.org/unematiii/wp-shortcode-tree.svg)](https://travis-ci.org/unematiii/wp-shortcode-tree)
[![Coverage Status](https://coveralls.io/repos/github/unematiii/wp-shortcode-tree/badge.svg)](https://coveralls.io/github/unematiii/wp-shortcode-tree)

Parses (nested) [shortcodes](https://codex.wordpress.org/Shortcode_API) into tree hierarchy. Find nodes, manipulate and re-serialize into string. Convenient for processing VisualComposer generated content in the backend.

### Basic Usage

Consider a WordPress post with the following content:

```
[folder name="SampleFolder"]
  [document type="image/png" size="64000" path="/path/to/image.png" name="File 1"]
  [document type="image/png" size="64000" path="/path/to/image.png" name="File 2"]
  [folder name="SampleFolder2"]
    [document type="image/png" size="64000" path="/path/to/image.png" name="File 3"]
    [document type="image/png" size="64000" path="/path/to/image.png" name="File 4"]
  [/folder]
[/folder]
```

Parse:

```php
$content = \WordPress\ShortcodeTree::fromString ( $page->post_content );
$folder = $content->getRoot ();
```

Get all documents & modify:

```php
$documents = $folder->findAll('document');

// Prepend 'My ' to filename
foreach($documents as $doc)
	$doc->attr('name', 'My ' . $doc->attr('name'));
```

Serialize and save:

```php
// Write content
wp_update_post ( array (
	'ID' => $post_id,
	'post_content' => $content // $content is automatically serialized
	                           // from ShortcodeTree to a string when
	                           // __toString() is called automagically
) );
```

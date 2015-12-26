## WordPress Shortcode Tree

Parses (nested) shortcodes into tree hierarcy. Find nodes, manipulate and re-serialize into string. Convenient for processing VisualComposer generated content in the backend.

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
	'post_content' => $content 
) );
```

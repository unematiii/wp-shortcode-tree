# WordPress Shortcode Tree

[![Build Status](https://travis-ci.org/unematiii/wp-shortcode-tree.svg?branch=develop)](https://travis-ci.org/unematiii/wp-shortcode-tree)
[![Coverage Status](https://coveralls.io/repos/github/unematiii/wp-shortcode-tree/badge.svg?branch=develop)](https://coveralls.io/github/unematiii/wp-shortcode-tree)

Parses (nested) [shortcodes](https://codex.wordpress.org/Shortcode_API) into tree hierarchy. Find nodes, manipulate and re-serialize into string. Convenient for processing Visual Composer generated content in the backend.

## Basic Usage

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
$content = \WordPress\ShortcodeTree::fromString( $page->post_content );
$folder  = $content->getRoot();
```

Get all documents & modify:

```php
$documents = $folder->findAll( 'document' );

// Prepend 'My ' to filename
foreach ($documents as $doc) {
    $doc->attr( 'name', 'My ' . $doc->attr( 'name' ) );
}
```

Serialize and save:

```php
// Write content
wp_update_post(
    array(
        'ID' => $post_id,
        // $content is automatically serialized from ShortcodeTree to a string
        // when __toString() is called automagically
        'post_content' => $content,
    )
);
```

### Custom (unregistered shortcodes)

If the content you want to parse contains unregistered shortcodes, pass them as an additional array to `(ShortcodeTree|Shortcode)::fromString` method:

```
[registered_shortcode some_att="0"]
    [unregistered_shortcode some_att="0"]
[/registered_shortcode]
```

```php
$content = \WordPress\ShortcodeTree::fromString( $page->post_content, array( 'unregistered_shortcode' ) );
$custom  = $content->findAll( 'unregistered_shortcode' );
```

## Development

### Requirements

[Composer](https://getcomposer.org/) needs to be available. If you don't have it, you can get it [here](https://getcomposer.org/download/).

### Install dev dependencies

```
composer i
```

### Running unit tests

```
./vendor/bin/phpunit
```

### Code style

This project uses slightly modified [WordPress coding standards](https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/) with [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer) built into CI pipeline.

#### Detecting problems

```
./vendor/bin/phpcs .
```

#### Auto-fixing problems

```
./vendor/bin/phpcbf .
```

#### VS Code integration

You need to install and enable [phpcs](https://marketplace.visualstudio.com/items?itemName=ikappas.phpcs) plugin. Configuration for it is provided by this project and it should work out of the box.

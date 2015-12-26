## Wordpress Shortcode Tree

Parses (nested) shortcodes into tree hierarcy. Find nodes, manipulate and re-serialize into string. Convenient for processing VisualComposer generated content in the backend.

### Basic Usage

Consider a nested shortcode:

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

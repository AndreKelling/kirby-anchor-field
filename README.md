# Anchor Field

A plugin for [Kirby CMS](https://getkirby.com/docs/reference).

Provides an anchor and anchorpicker field for the panel in pages.


## Requirements

- Kirby 6.0+ (for Kirby 5.1+ there is release version 1.0.0)

## Installation

### Download

Download and copy this repository to `/site/plugins/kirby-anchor-field`.

### Composer

```
composer require andrekelling/kirby-anchor-field
```

## Usage

Add an `anchor` field to each block blueprint that should be a link target. 
- validates a CSS/URL-safe ID 
- suggests one of a block's fields named `title`, `headline` or `topline` (quite custom hopefully you use one of these field names. works without but better for reference in the anchorNav) when focused while empty
- validates for duplicate IDs on the same page (just the anchor field. not over all anchors used on a page)

```yaml
fields:
  anchor:
    type: anchor
```

Add an `anchorpicker` field to the blueprint where editors should create navigation entries.
- lists anchors from the current page 
  - from all anchor fields
  - from all links that are anchors (hopefully all)
- save the page after adding or changing anchors to refresh the available choices

```yaml
fields:
  anchorNavigation:
    type: anchorpicker
    colors: true
```

The optional `colors: true` setting requires that [Kirby Color Palette plugin](https://packagist.org/packages/tobimori/kirby-color-palette).
Omit it if you don't need color selection.

Read the entries in a template with `toAnchorArray()`. Each entry has `label`, `anchor` (for example, `#about`) and `color` properties:

```php
$entries = $page->anchorNavigation()->toAnchorArray();
```

## License

[MIT License](./LICENSE)
Copyright © 2026 André Kelling

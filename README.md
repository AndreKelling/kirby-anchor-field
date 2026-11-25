# Anchor Field

A plugin for [Kirby CMS](https://getkirby.com/docs/reference).

- provides an anchor field for the panel

## Requirements

- Kirby 5.1+ (just tested with 5.1 probably works on previous versions too) – not compatible with Kirby 6

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
- suggests one of a block's fields named `title`, `headline` or `topline` (quite custom probably not your used field names) when focused while empty
- validates for duplicate IDs on the same page

```yaml
fields:
  anchor:
    type: anchor
```

Add an `anchorpicker` field to the blueprint where editors should create navigation entries.
- lists anchors from the current page 
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

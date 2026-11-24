<?php
declare(strict_types=1);

use KirbyAnchorField\AnchorField;
use KirbyAnchorField\AnchorPickerField;

load([
  'KirbyAnchorField\\AnchorField'       => 'classes/AnchorField.php',
  'KirbyAnchorField\\AnchorPickerField' => 'classes/AnchorPickerField.php',
], __DIR__);

Kirby::plugin('andrekelling/kirby-anchor-field', [
  'fields' => [
    'anchor' => AnchorField::class,
    'anchorpicker' => AnchorPickerField::class,
  ],
  'api' => [
    'routes' => [
      [
        'pattern' => 'anchor-options/(:any)',
        'action' => function (string $pageId) {
          $page = kirby()->page(str_replace('+', '/', $pageId));
          $options = [];

          if (!$page) {
            return $options;
          }

          foreach ($page->content()->data() as $value) {
            if (!is_string($value)) {
              continue;
            }
            $blocks = json_decode($value, true);
            if (!is_array($blocks)) {
              continue;
            }
            foreach ($blocks as $block) {
              $anchor = trim($block['content']['anchor'] ?? '');
              if (empty($anchor)) {
                continue;
              }
              // TODO hold possible title fields as option default
              $title = strip_tags($block['content']['title'] ?? $block['content']['headline'] ?? $block['content']['topline'] ?? '');
              $label = $block['type'] .' - '. $title . ' (#' . $anchor . ')';
              $options[] = ['value' => '#' . $anchor, 'text' => $label];
            }
          }

          return $options;
        }
      ]
    ]
  ],
  'fieldMethods' => [
    'toAnchorArray' => function ($field): array {
      if ($field->isEmpty()) {
        return [];
      }
      $anchorWat = $field->json();
      $anchorAgain = json_decode($anchorWat->value());
      return $anchorAgain;
    },
  ],
  'translations' => [
    'en' => [
      'anchor.cssUrlSafe' => 'Anchor must be a CSS/URL safe string (letters, numbers, hyphens only).',
      'anchor.unique' => 'This anchor ID is already used by another block on this page.',
      'anchor-picker.notallfilled' => 'Please fill in all fields. At least one label or anchor is not filled.',
      'anchor-picker.label' => 'Label',
      'anchor-picker.anchor' => 'Anchor',
      'anchor-picker.color' => 'Color',
      'anchor-picker.help' => 'When filled it is active.',
      'anchor-picker.save-hint' => '(save once to refresh your choices!)',
      'anchor-picker.add' => 'Add entry',
    ],
    'de' => [
      'anchor.cssUrlSafe' => 'Der Anker muss eine CSS/URL-sichere Zeichenfolge sein (nur Buchstaben, Zahlen und Bindestriche).',
      'anchor.unique' => 'Diese Anker-ID wird bereits von einem anderen Block auf dieser Seite verwendet.',
      'anchor-picker.notallfilled' => 'Bitte füllen Sie alle Felder aus. Mindestens eine Beschriftung oder ein Anker ist nicht ausgefüllt.',
      'anchor-picker.label' => 'Label',
      'anchor-picker.anchor' => 'Anker',
      'anchor-picker.color' => 'Farbe',
      'anchor-picker.help' => 'Wenn befüllt, dann aktiv.',
      'anchor-picker.save-hint' => '(einmal Speichern für Aktualisierung der Auswahl!)',
      'anchor-picker.add' => 'weiterer Eintrag',
    ]
  ]
]);

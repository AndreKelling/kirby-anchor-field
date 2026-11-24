<?php
use Kirby\Exception\Exception as KirbyException;

Kirby::plugin('andrekelling/kirby-anchor-field', [
  'fields' => [
    'anchor' => [
      'extends' => 'text',
      'validations' => [
        'css-url-safe' => function ($value) {
          if (empty($value)) {
            return; // skip validation for optional empty fields
          }
          $pattern = '^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$';
          if (!preg_match('~' . $pattern . '~', $value)) {
            throw new \Kirby\Exception\InvalidArgumentException(t('anchor.cssUrlSafe'));
          }
        },
        'unique' => function ($value) {
          if (empty($value)) {
            return; // skip validation for optional empty fields
          }
          $count = 0;
          // Use submitted request data, not saved model data
          $blocks = kirby()->request()->data()['blocks'];
          if (!is_array($blocks) || empty($blocks) || !isset($blocks[0]['type'], $blocks[0]['content'])) {
            return;
          }
          foreach ($blocks as $block) {
            if (trim($block['content']['anchor'] ?? '') === $value) {
              $count++;
            }
          }
          if ($count > 1) {
            throw new \Kirby\Exception\InvalidArgumentException(t('anchor.unique'));
          }
        }
      ]
    ],
    'anchorpicker' => [
      'props' => [
        'value' => function ($value = null) {
          if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
          }
          return is_array($value) ? $value : [];
        },
        'help' => function ($help = null) {
          if ($help === null) {
            return t('anchor-picker.help');
          }
          return $help;
        },
        'colors' => function ($colors = false) {
          if ($colors === true) {
            $plugins = array_keys(kirby()->plugins());

            if (!in_array('tobimori/color-palette', $plugins)) {
              throw new KirbyException(
                'andrekelling/kirby-anchor-field plugin requires the "tobimori/color-palette" plugin to be installed. When using the colors field. '
              );
            }

            return [
              'type' => 'color-palette',
              'size' => 'small',
              'unselect' => true,
              'options' => kirby()->extension('fields', 'color-palette')['props']['options'] ?? [],
            ];
          }

          return $colors;
        }
      ],
      'save' => function ($value) {
        return is_array($value) ? json_encode(array_values($value)) : ($value ?? '');
      },
      'validations' => [
        'all-filled' => function ($value) {
          if (empty($value)) {
            return; // skip validation for optional empty fields
          }
          foreach ($value as $item) {
            if (empty($item['label'])||  empty($item['anchor'])) {
              throw new \Kirby\Exception\InvalidArgumentException(t('anchor-picker.notallfilled'));
            }
          }
        },
      ]
    ],
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

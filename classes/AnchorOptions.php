<?php
declare(strict_types=1);

namespace KirbyAnchorField;

use Kirby\Cms\Page;
use Kirby\Content\Field;
use Kirby\Toolkit\Dom;

class AnchorOptions
{
    /**
     * @return list<array{value: string, text: string}>
     */
    public static function fromPage(Page $page): array
    {
        $options = [];
        $seen = [];

        $addOption = static function (string $value, string $text) use (&$options, &$seen): void {
            if (isset($seen[$value])) {
                return;
            }

            $seen[$value] = true;
            $options[] = ['value' => $value, 'text' => $text];
        };

        $addTextLinks = static function (string $html, string $source) use ($addOption): void {
            if ($html === '') {
                return;
            }

            $dom = new Dom($html);

            foreach ($dom->query('//a[@href]') as $link) {
                if (!$link instanceof \DOMElement) {
                    continue;
                }

                $href = trim($link->getAttribute('href'));

                if (!str_starts_with($href, '#') || $href === '#') {
                    continue;
                }

                $label = trim($link->textContent);
                $text = $source . ($label !== '' ? ' - ' . $label : '') . ' (' . $href . ')';
                $addOption($href, $text);
            }
        };

        foreach ($page->content()->data() as $value) {
            if (!is_string($value)) {
                continue;
            }

            $blocks = json_decode($value, true);

            if (!is_array($blocks)) {
                continue;
            }

            foreach ($blocks as $block) {
                if (!is_array($block) || !is_array($block['content'] ?? null)) {
                    continue;
                }

                $content = $block['content'];
                $anchor = $content['anchor'] ?? '';
                $anchor = is_string($anchor) ? trim($anchor) : '';
                $title = $content['title'] ?? $content['headline'] ?? $content['topline'] ?? '';
                $title = is_string($title) ? strip_tags($title) : '';
                $type = is_string($block['type'] ?? null) ? $block['type'] : '';

                if ($anchor !== '') {
                    $label = $type . ' - ' . $title . ' (#' . $anchor . ')';
                    $addOption('#' . $anchor, $label);
                }

                $contentText = $content['contenttext'] ?? $content['contentText'] ?? null;

                if ($type !== 'wysiwyg' || !is_string($contentText)) {
                    continue;
                }

                $source = 'Wysiwyg' . ($title !== '' ? ' - ' . $title : '');
                $field = new Field($page, 'contentText', $contentText);
                $addTextLinks((string)$field->kirbytext(), $source);
            }
        }

        $addTextLinks((string)$page->text()->kirbytext(), 'Text');

        return $options;
    }
}
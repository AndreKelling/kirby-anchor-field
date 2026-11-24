<?php
declare(strict_types=1);

use Kirby\Cms\Page;
use KirbyAnchorField\AnchorOptions;
use PHPUnit\Framework\TestCase;

final class AnchorOptionsTest extends TestCase
{
    public function testIncludesKirbyTagAndMarkdownLinksFromPageText(): void
    {
        $options = $this->options(
            '(link: #intro text: Intro) [Markdown](#markdown)'
        );

        self::assertSame([
            ['value' => '#intro', 'text' => 'Text - Intro (#intro)'],
            ['value' => '#markdown', 'text' => 'Text - Markdown (#markdown)'],
        ], $options);
    }

    public function testIncludesWysiwygLinksAndDeclaredAnchors(): void
    {
        $options = $this->options('', [
            [
                'type' => 'wysiwyg',
                'content' => [
                    'anchor' => 'hero',
                    'title' => 'Hero',
                    'contentText' => '(link: #nested text: Nested link) [Body](#body)',
                ],
            ],
            [
                'type' => 'text',
                'content' => [
                    'anchor' => 'section',
                    'title' => 'Section',
                ],
            ],
        ]);

        self::assertSame([
            ['value' => '#hero', 'text' => 'wysiwyg - Hero (#hero)'],
            ['value' => '#nested', 'text' => 'Wysiwyg - Hero - Nested link (#nested)'],
            ['value' => '#body', 'text' => 'Wysiwyg - Hero - Body (#body)'],
            ['value' => '#section', 'text' => 'text - Section (#section)'],
        ], $options);
    }

    public function testIncludesWysiwygLinksFromNormalizedFieldKey(): void
    {
        $options = $this->options('', [
            [
                'type' => 'wysiwyg',
                'content' => [
                    'contenttext' => '(link: #nested text: Nested link)',
                ],
            ],
        ]);

        self::assertSame([
            ['value' => '#nested', 'text' => 'Wysiwyg - Nested link (#nested)'],
        ], $options);
    }

    public function testDeduplicatesFragmentsAndExcludesExternalAndPathLinks(): void
    {
        $options = $this->options(
            '[Same again](#same) [Page](#page) [External](https://example.com/#external) [Path](/other/#path)',
            [
                [
                    'type' => 'wysiwyg',
                    'content' => [
                        'anchor' => 'same',
                        'title' => 'Same',
                        'contentText' => '(link: #same text: Repeated) [Nested](#nested) [Path](other/#nested-path)',
                    ],
                ],
            ]
        );

        self::assertSame(['#same', '#nested', '#page'], array_column($options, 'value'));
        self::assertSame('wysiwyg - Same (#same)', $options[0]['text']);
    }

    public function testEmptyOrMalformedContentReturnsNoOptions(): void
    {
        $page = new Page([
            'slug' => 'fixture',
            'content' => [
                'text' => '(link: #unclosed text: Broken',
                'blocks' => '{malformed json',
            ],
        ]);

        self::assertSame([], AnchorOptions::fromPage($page));
    }

    /**
     * @param list<array{type: string, content: array<string, string>}> $blocks
     * @return list<array{value: string, text: string}>
     */
    private function options(string $text, array $blocks = []): array
    {
        $content = ['text' => $text];

        if ($blocks !== []) {
            $content['blocks'] = json_encode($blocks, JSON_THROW_ON_ERROR);
        }

        return AnchorOptions::fromPage(new Page([
            'slug' => 'fixture',
            'content' => $content,
        ]));
    }
}
<?php
declare(strict_types=1);

use Kirby\Form\Field;
use KirbyAnchorField\AnchorPickerField;
use PHPUnit\Framework\TestCase;

final class AnchorPickerFieldTest extends TestCase
{
    public function testFieldTypeIsRegistered(): void
    {
        self::assertSame(AnchorPickerField::class, Field::resolve('anchorpicker'));
    }

    public function testDecodesJsonAndStoresValuesAsJson(): void
    {
        $field = new AnchorPickerField(
            value: '[{"label":"Intro","anchor":"#intro"}]'
        );

        self::assertSame(
            [['label' => 'Intro', 'anchor' => '#intro']],
            $field->toFormValue()
        );
        self::assertSame(
            '[{"label":"Intro","anchor":"#intro"}]',
            $field->toStoredValue()
        );
    }

    public function testMalformedJsonFallsBackToEmptyArray(): void
    {
        $field = new AnchorPickerField(value: '[invalid json');

        self::assertSame([], $field->toFormValue());
        self::assertSame('[]', $field->toStoredValue());
    }

    public function testAcceptsFilledEntries(): void
    {
        $field = new AnchorPickerField(
            value: [['label' => 'Intro', 'anchor' => '#intro']]
        );

        self::assertTrue($field->isValid());
    }

    public function testRejectsIncompleteEntries(): void
    {
        $field = new AnchorPickerField(
            value: [['label' => 'Intro', 'anchor' => '']]
        );

        self::assertArrayHasKey('all-filled', $field->errors());
    }

    public function testAllowsAnEmptyOptionalValue(): void
    {
        $field = new AnchorPickerField(value: []);

        self::assertTrue($field->isValid());
    }
}
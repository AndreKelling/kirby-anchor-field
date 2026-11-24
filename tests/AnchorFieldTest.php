<?php
declare(strict_types=1);

use Kirby\Form\Field;
use KirbyAnchorField\AnchorField;
use PHPUnit\Framework\TestCase;

final class AnchorFieldTest extends TestCase
{
    public function testFieldTypeIsRegistered(): void
    {
        self::assertSame(AnchorField::class, Field::resolve('anchor'));
    }

    public function testAcceptsCssSafeValue(): void
    {
        $field = new AnchorField(value: 'section-2');

        self::assertTrue($field->isValid());
    }

    public function testRejectsCssUnsafeValue(): void
    {
        $field = new AnchorField(value: 'section title');

        self::assertArrayHasKey('css-url-safe', $field->errors());
    }

    public function testRejectsDuplicateValue(): void
    {
        $field = new AnchorField(value: 'section');

        self::assertArrayHasKey('unique', $field->errors());
    }
}
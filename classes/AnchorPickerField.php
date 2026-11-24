<?php
declare(strict_types=1);

namespace KirbyAnchorField;

use JsonException;
use Kirby\Exception\Exception as KirbyException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\InputField;
use Kirby\Reflection\Attributes\Prop;
use Kirby\Toolkit\BlockCollectionAccess;

class AnchorPickerField extends InputField
{
    protected array $value = [];

    #[Prop(getter: 'helpText')]
    protected array|string|null $help = null;

    #[Prop(getter: 'colorOptions')]
    protected array|bool|null $colors = false;

    #[BlockCollectionAccess]
    public function fill(mixed $value): static
    {
        if (is_string($value) === true && $value !== '') {
            try {
                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $value = [];
            }
        }

        return parent::fill(is_array($value) === true ? $value : []);
    }

    public function colorOptions(): array|bool|null
    {
        if ($this->colors !== true) {
            return $this->colors;
        }

        $plugins = array_keys(kirby()->plugins());

        if (in_array('tobimori/color-palette', $plugins, true) === false) {
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

    public function helpText(): array|string|null
    {
        return $this->help ?? t('anchor-picker.help');
    }

    public function toStoredValue(): string
    {
        return json_encode(array_values($this->value), JSON_THROW_ON_ERROR);
    }

    protected function validations(): array
    {
        return [
            'all-filled' => function (array $value): void {
                if ($value === []) {
                    return;
                }

                foreach ($value as $item) {
                    if (
                        is_array($item) === false ||
                        empty($item['label']) ||
                        empty($item['anchor'])
                    ) {
                        throw new InvalidArgumentException(t('anchor-picker.notallfilled'));
                    }
                }
            }
        ];
    }
}
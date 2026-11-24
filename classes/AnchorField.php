<?php
declare(strict_types=1);

namespace KirbyAnchorField;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\Field\InputField;

class AnchorField extends InputField
{
    protected string $value = '';

    protected function validations(): array
    {
        return [
            'css-url-safe' => function ($value) {
                if (empty($value)) {
                    return; // skip validation for optional empty fields
                }
                $pattern = '^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$';
                if (!preg_match('~' . $pattern . '~', $value)) {
                    throw new InvalidArgumentException(t('anchor.cssUrlSafe'));
                }
            },
            'unique' => function ($value) {
                if (empty($value)) {
                    return; // skip validation for optional empty fields
                }
                $count = 0;
                // Use submitted request data, not saved model data
                $blocks = $this->kirby()->request()->data()['blocks'];
                if (!is_array($blocks) || empty($blocks) || !isset($blocks[0]['type'], $blocks[0]['content'])) {
                    return;
                }
                foreach ($blocks as $block) {
                    if (trim($block['content']['anchor'] ?? '') === $value) {
                        $count++;
                    }
                }
                if ($count > 1) {
                    throw new InvalidArgumentException(t('anchor.unique'));
                }
            }
        ];
    }

}
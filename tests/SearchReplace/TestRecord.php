<?php
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Batch\Tests\SearchReplace;

use Docalist\Data\Record;
use Docalist\Type\Text;
use Docalist\Type\Composite;
use Docalist\Type\ListEntry;
use Docalist\Type\TypedText;

/**
 * Record de test pour la classe FieldTest.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TestRecord extends Record
{
    public static function loadSchema(): array
    {
        return [
            'name' => 'testrecord',
            'fields' => [
                'text' => Text::class,

                'texts' => [
                    'type' => Text::class,
                    'repeatable' => true,
                ],

                'value' => ListEntry::class,

                'values' => [
                    'type' => ListEntry::class,
                    'repeatable' => true,
                ],

                'object' => [
                    'type' => Composite::class,
                    'fields' => [
                        'text' => Text::class,

                        'texts' => [
                            'type' => Text::class,
                            'repeatable' => true,
                        ],

                        'value' => ListEntry::class,

                        'values' => [
                            'type' => ListEntry::class,
                            'repeatable' => true,
                        ],
                    ],
                ],

                'objects' => [
                    'type' => Composite::class,
                    'repeatable' => true,
                    'fields' => [
                        'text' => Text::class,

                        'texts' => [
                            'type' => Text::class,
                            'repeatable' => true,
                        ],

                        'value' => ListEntry::class,

                        'values' => [
                            'type' => ListEntry::class,
                            'repeatable' => true,
                        ],
                    ],
                ],

                'typedText' => [
                    'type' => TypedText::class,
                    'repeatable' => true,
                    'fields' => [
                        'type'  => ['type' => ListEntry::class],
                        'value' => ['type' => Text::class],
                    ],
                ],

                'typedTexts' => [
                    'type' => TypedText::class,
                    'repeatable' => true,
                    'fields' => [
                        'type'  => ['type' => ListEntry::class],
                        'value' => ['type' => Text::class, 'repeatable' => true],
                    ],
                ],
                'typedValue' => [
                    'type' => TypedText::class,
                    'repeatable' => true,
                    'fields' => [
                        'type'  => ['type' => ListEntry::class],
                        'value' => ['type' => ListEntry::class],
                    ],
                ],

                'typedValues' => [
                    'type' => TypedText::class,
                    'repeatable' => true,
                    'fields' => [
                        'type'  => ['type' => ListEntry::class],
                        'value' => ['type' => ListEntry::class, 'repeatable' => true],
                    ],
                ],
            ],
        ];
    }
}

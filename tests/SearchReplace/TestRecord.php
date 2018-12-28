<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace;

use Docalist\Data\Record;
use Docalist\Type\Text;
use Docalist\Type\Composite;
use Docalist\Type\ListEntry;

/**
 * Record de test pour la classe FieldTest.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TestRecord extends Record
{
    public static function loadSchema()
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
            ],
        ];
    }

}

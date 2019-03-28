<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace\Operation\ValueOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\OperationTestCase;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\ClearValue;

/**
 * Teste la classe ClearValue.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ClearValueTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new ClearValue(new Field('field', Field::TYPE_VALUE, false));
        $expected = 'Vider le champ <var>field (valeur)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function valueProvider(): array
    {
        // Vide le champ 'value'
        $operation = $this->getOperation('', 'value', '', '');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                [],
                false
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['value' => ''],
                $operation,
                [],
                true
            ],

            // Le champ contient quelque chose
            [
                ['value' => 'Test'],
                $operation,
                [],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function valuesProvider(): array
    {
        // Vide le champ 'values'
        $operation = $this->getOperation('', 'values', '', '');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                [],
                false
            ],

            // Le champ existe dans le record mais il est vide (collection vide)
            [
                ['values' => []],
                $operation,
                [],
                true
            ],

            // Le champ contient un seul item
            [
                ['values' => ['Test']],
                $operation,
                [],
                true
            ],

            // Le champ contient plusieurs items
            [
                ['values' => ['Test', 'tes', 'tse', 'est']],
                $operation,
                [],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function otherTestsProvider(): array
    {
        return [
            // Vide le champ 'object.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'value', '', ''),
                [],
                false
            ],
            // Vide le champ 'object.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'values', '', ''),
                [],
                false
            ],
            // Vide le champ 'objects.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'value', '', ''),
                [],
                false
            ],
            // Vide le champ 'objects.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'values', '', ''),
                [],
                false
            ],


            // Vide le champ 'objects.value', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                $this->getOperation('objects', 'value', '', ''),
                ['objects' => [ [], [] ] ],
                true
            ],
            // Vide le champ 'objects.values', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                $this->getOperation('objects', 'values', '', ''),
                ['objects' => [ [], [] ] ],
                true
            ],
        ];
    }
}

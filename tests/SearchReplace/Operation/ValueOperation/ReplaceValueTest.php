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

namespace Docalist\Batch\Tests\SearchReplace\Operation\ValueOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\OperationTestCase;

/**
 * Teste la classe ReplaceValue.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReplaceValueTest extends OperationTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function valueProvider(): array
    {
        // Remplace 'es' par 'ES' dans le champ 'value'
        $operation = $this->getOperation('', 'value', 'es', 'ES');

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
                ['value' => ''],
                false
            ],

            // Le champ ne contient pas la valeur recherchée
            [
                ['value' => 'not found'],
                $operation,
                ['value' => 'not found'],
                false
            ],

            // Le champ contient une seule occurence de la valeur recherchée
            [
                ['value' => 'es'],
                $operation,
                ['value' => 'ES'],
                true
            ],

            // Le champ contient plusieurs occurences de la valeur recherchée
            [
                ['value' => 'Test tes est'],
                $operation,
                ['value' => 'Test tes est'],
                false
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function valuesProvider(): array
    {
        // Remplace 'es' par 'ES' dans le champ 'values'
        $operation = $this->getOperation('', 'values', 'es', 'ES');

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
                ['values' => []],
                false
            ],

            // Aucun item du champ ne contient la valeur recherchée
            [
                ['values' => ['not', 'found']],
                $operation,
                ['values' => ['not', 'found']],
                false
            ],

            // Un item du champ contient la valeur recherchée
            [
                ['values' => ['es']],
                $operation,
                ['values' => ['ES']],
                true
            ],

            // Un item du champ contient plusieurs occurences de la valeur recherchée
            [
                ['values' => ['Test tes']],
                $operation,
                ['values' => ['Test tes']],
                false
            ],

            // Plusieurs items du champ contiennent une occurence de la valeur recherchée
            [
                ['values' => ['es', 'tes', 'tse', 'es']],
                $operation,
                ['values' => ['ES', 'tes', 'tse', 'ES']],
                true
            ],

            // Plusieurs items du champ contiennent plusieurs occurences de la valeur recherchée
            [
                ['values' => ['Test es', 'tse es']],
                $operation,
                ['values' => ['Test es', 'tse es']],
                false
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function otherTestsProvider(): array
    {
        return [
            // Remplace 'es' par 'ES' dans le champ 'object.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'value', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'object.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'values', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'value', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'values', 'es', 'ES'),
                [],
                false
            ],


            // Remplace 'es' par 'ES' dans le champ 'objects.value', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                $this->getOperation('objects', 'value', 'es', 'ES'),
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.values', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                $this->getOperation('objects', 'values', 'es', 'ES'),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                false
            ],


            // Remplace 'es' par 'ES' dans le champ 'objects.value', une des occurences parent contient la valeur
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'es'] ] ],
                $this->getOperation('objects', 'value', 'es', 'ES'),
                ['objects' => [ ['value' => 'A'], ['value' => 'ES'] ] ],
                true
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.values', une des occurences parent contient la valeur
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','es','D']] ] ],
                $this->getOperation('objects', 'values', 'es', 'ES'),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','ES','D']] ] ],
                true
            ],
        ];
    }
}

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
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\RemoveValue;

/**
 * Teste la classe RemoveValue.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveValueTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new RemoveValue(new Field('field', Field::TYPE_VALUE, false), 'search');
        $expected = 'Supprimer <del>search</del> dans le champ <var>field (valeur)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function valueProvider(): array
    {
        // Supprime la valeur 'es' dans le champ 'value'
        $operation = $this->getOperation('', 'value', 'es', '');

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

            // Le champ contient la valeur recherchée
            [
                ['value' => 'es'],
                $operation,
                ['value' => ''],
                true
            ],

            // Le champ contient plusieurs occurences de la valeur recherchée (mais pas la valeur exacte)
            [
                ['value' => 'Test tes est'],
                $operation,
                ['value' => 'Test tes est'],
                false
            ],

            // La recherche est sensible à la casse
            [
                ['value' => 'ES'],
                $operation,
                ['value' => 'ES'],
                false
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function valuesProvider(): array
    {
        // Supprime la valeur 'es' dans le champ 'values'
        $operation = $this->getOperation('', 'values', 'es', '');

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

            // Un item du champ contient une seule occurence de la valeur recherchée
            [
                ['values' => ['es']],
                $operation,
                ['values' => ['']],
                true
            ],

            // Un item du champ contient plusieurs occurences de la recherche mais pas la valeur exacte
            [
                ['values' => ['Test tes']],
                $operation,
                ['values' => ['Test tes']],
                false
            ],

            // Plusieurs items du champ contiennent la valeur recherchée
            [
                ['values' => ['Test', 'es', 'tse', 'es']],
                $operation,
                ['values' => ['Test', '', 'tse', '']],
                true
            ],

            // Le champ est vide après le chercher/remplacer
            [
                ['values' => ['es', 'es']],
                $operation,
                ['values' => []],
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
            // Supprime 'es' dans le champ 'object.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'value', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'object.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'values', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'objects.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'value', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'objects.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'values', 'es', ''),
                [],
                false
            ],

            // Supprime 'es' dans le champ 'objects.value', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                $this->getOperation('objects', 'value', 'es', ''),
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                false
            ],
            // Supprime 'es' dans le champ 'objects.values', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                $this->getOperation('objects', 'values', 'es', ''),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                false
            ],


            // Supprime 'es' dans le champ 'objects.value', une des occurences parent contient la valeur cherchée
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'es'] ] ],
                $this->getOperation('objects', 'value', 'es', ''),
                ['objects' => [ ['value' => 'A'], ['value' => ''] ] ],
                true
            ],
            // Supprime 'es' dans le champ 'objects.values', une des occurences parent contient la valeur cherchée
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','es','D']] ] ],
                $this->getOperation('objects', 'values', 'es', ''),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','','D']] ] ],
                true
            ],
        ];
    }
}

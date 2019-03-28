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
use Docalist\Batch\SearchReplace\Operation\ValueOperation\InjectValue;

/**
 * Teste la classe InjectValue.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class InjectValueTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new InjectValue(new Field('field', Field::TYPE_VALUE, false), 'replace');
        $expected = 'Injecter <ins>replace</ins> dans le champ <var>field (valeur)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function valueProvider(): array
    {
        // Injecte la valeur 'ES' dans le champ 'value'
        $operation = $this->getOperation('', 'value', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['value' => 'ES'],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['value' => ''],
                $operation,
                ['value' => 'ES'],
                true
            ],

            // Le champ contient déjà une valeur
            [
                ['value' => 'XX'],
                $operation,
                ['value' => 'ES'],
                true
            ],

            // Le champ contient déjà la valeur à injecter
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
        // Injecte la valeur 'ES' dans le champ 'values'
        $operation = $this->getOperation('', 'values', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['values' => ['ES']],
                true
            ],

            // Le champ existe dans le record mais il est vide (collection vide)
            [
                ['values' => []],
                $operation,
                ['values' => ['ES']],
                true
            ],

            // Le champ contient déjà des items
            [
                ['values' => ['XX', 'YY']],
                $operation,
                ['values' => ['XX', 'YY', 'ES']],
                true
            ],

            // Le champ a un item qui contient déjà la valeur à injecter
            [
                ['values' => ['ES']],
                $operation,
                ['values' => ['ES']],
                false
            ],

            // Le champ contient plusieurs items qui contiennent la valeur à injecter
            [
                ['values' => ['ES', 'ES']],
                $operation,
                ['values' => ['ES', 'ES']],
                false
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function objectsValueProvider(): array
    {
        // on ne peut pas utiliser applyProviderTo() car les résultats sont différents avec un parent répétable
        // les tests ont été repris et adaptés de ceux qu'on a dans valueProvider

        // Injecte la valeur 'ES' dans le champ 'objects.value'
        $operation = $this->getOperation('objects', 'value', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                [ 'objects' => [ ['value' => 'ES'] ] ],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                [ 'objects' => [ ['value' => ''] ] ],
                $operation,
                [ 'objects' => [ ['value' => ''],['value' => 'ES'] ] ],
                true
            ],

            // Le champ contient déjà une valeur
            [
                [ 'objects' => [ ['value' => 'xx'] ] ],
                $operation,
                [ 'objects' => [ ['value' => 'xx'],['value' => 'ES'] ] ],
                true
            ],

            // Le champ contient déjà la valeur à injecter
            [
                [ 'objects' => [ ['value' => 'ES'] ] ],
                $operation,
                [ 'objects' => [ ['value' => 'ES'] ] ],
                false
            ],
        ];
    }

    protected function objectsValuesProvider(): array
    {
        // on ne peut pas utiliser applyProviderTo() car les résultats sont différents avec un parent répétable
        // les tests ont été repris et adaptés de ceux qu'on a dans valuesProvider

        // Injecte la valeur 'ES' dans le champ 'objects.values'
        $operation = $this->getOperation('objects', 'values', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['objects' => [ ['values' => ['ES']] ] ],
                true
            ],

            // Le champ existe dans le record mais il est vide (collection vide)
            [
                ['objects' => [ ['values' => []] ] ],
                $operation,
                ['objects' => [ ['values' => ['ES']] ] ],
                true
            ],

            // Le champ contient déjà des items
            [
                ['objects' => [ ['values' => ['XX','YY']] ] ],
                $operation,
                ['objects' => [ ['values' => ['XX','YY']],['values' => ['ES']] ] ], // et non pas ['XX','YY','ES']
                true
            ],

            // Le champ a un item qui contient déjà la valeur à injecter
            [
                ['objects' => [ ['values' => ['XX','ES']] ] ],
                $operation,
                ['objects' => [ ['values' => ['XX','ES']] ] ],
                false
            ],

            // Le champ contient plusieurs items qui contiennent la valeur à injecter
            [
                ['objects' => [ ['values' => ['ES','ES']] ] ],
                $operation,
                ['objects' => [ ['values' => ['ES','ES']] ] ],
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
            // Injecte 'ES' dans le champ 'object.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'value', '', 'ES'),
                ['object' => ['value' => 'ES']],
                true
            ],
            // Injecte 'ES' dans le champ 'object.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'values', '', 'ES'),
                ['object' => ['values' => ['ES']]],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.value', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'value', '', 'ES'),
                ['objects' => [ ['value' => 'ES'] ] ],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.values', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'values', '', 'ES'),
                ['objects' => [ ['values' => ['ES'] ] ] ],
                true
            ],


            // Injecte 'ES' dans le champ 'objects.value', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'B'] ] ],
                $this->getOperation('objects', 'value', '', 'ES'),
                ['objects' => [ ['value' => 'A'], ['value' => 'B'], ['value' => 'ES'] ] ],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.values', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']] ] ],
                $this->getOperation('objects', 'values', '', 'ES'),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','D']], ['values' => ['ES']] ] ],
                true
            ],


            // Injecte 'ES' dans le champ 'objects.value', une des occurences du parent contient déjà la valeur
            [
                ['objects' => [ ['value' => 'A'], ['value' => 'ES'] ] ],
                $this->getOperation('objects', 'value', '', 'ES'),
                ['objects' => [ ['value' => 'A'], ['value' => 'ES'] ] ],
                false
            ],
            // Injecte 'ES' dans le champ 'objects.values', une des occurences du parent contient déjà la valeur
            [
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','ES','D']] ] ],
                $this->getOperation('objects', 'values', '', 'ES'),
                ['objects' => [ ['values' => ['A','B']], ['values' => ['C','ES','D']] ] ],
                false
            ],
        ];
    }
}

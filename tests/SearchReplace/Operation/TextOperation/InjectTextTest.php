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

namespace Docalist\Batch\Tests\SearchReplace\Operation\TextOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\OperationTestCase;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Operation\TextOperation\InjectText;

/**
 * Teste la classe InjectText.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class InjectTextTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new InjectText(new Field('field', Field::TYPE_TEXT, false), 'replace');
        $expected = 'Injecter <ins>replace</ins> dans le champ <var>field (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function textProvider(): array
    {
        // Injecte le texte 'ES' dans le champ 'text'
        $operation = $this->getOperation('', 'text', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['text' => 'ES'],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['text' => ''],
                $operation,
                ['text' => 'ES'],
                true
            ],

            // Le champ contient déjà une valeur
            [
                ['text' => 'XX'],
                $operation,
                ['text' => 'ES'],
                true
            ],

            // Le champ contient déjà le texte à injecter
            [
                ['text' => 'ES'],
                $operation,
                ['text' => 'ES'],
                false
            ],

            // Le champ contient plusieurs fois le texte à injecter
            [
                ['text' => 'ES ES'],
                $operation,
                ['text' => 'ES'],
                true
            ],

            // Le champ contient déjà le texte à injecter, mais avec ue casse différente
            [
                ['text' => 'es'],
                $operation,
                ['text' => 'ES'],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function textsProvider(): array
    {
        // Injecte le texte 'ES' dans le champ 'texts'
        $operation = $this->getOperation('', 'texts', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['texts' => ['ES']],
                true
            ],

            // Le champ existe dans le record mais il est vide (collection vide)
            [
                ['texts' => []],
                $operation,
                ['texts' => ['ES']],
                true
            ],

            // Le champ contient déjà des items
            [
                ['texts' => ['XX', 'YY']],
                $operation,
                ['texts' => ['XX', 'YY', 'ES']],
                true
            ],

            // Le champ a un item qui contient déjà le texte à injecter
            [
                ['texts' => ['ES']],
                $operation,
                ['texts' => ['ES']],
                false
            ],

            // Le champ contient plusieurs items qui contiennent le texte à injecter
            [
                ['texts' => ['ES', 'ES']],
                $operation,
                ['texts' => ['ES', 'ES']],
                false
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function objectsTextProvider(): array
    {
        // on ne peut pas utiliser applyProviderTo() car les résultats sont différents avec un parent répétable
        // les tests ont été repris et adaptés de ceux qu'on a dans textProvider

        // Injecte le texte 'ES' dans le champ 'objects.text'
        $operation = $this->getOperation('objects', 'text', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                [ 'objects' => [ ['text' => 'ES'] ] ],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                [ 'objects' => [ ['text' => ''] ] ],
                $operation,
                [ 'objects' => [ ['text' => ''],['text' => 'ES'] ] ],
                true
            ],

            // Le champ contient déjà une valeur
            [
                [ 'objects' => [ ['text' => 'xx'] ] ],
                $operation,
                [ 'objects' => [ ['text' => 'xx'],['text' => 'ES'] ] ],
                true
            ],

            // Le champ contient déjà le texte à injecter
            [
                [ 'objects' => [ ['text' => 'ES'] ] ],
                $operation,
                [ 'objects' => [ ['text' => 'ES'] ] ],
                false
            ],
        ];
    }

    protected function objectsTextsProvider(): array
    {
        // on ne peut pas utiliser applyProviderTo() car les résultats sont différents avec un parent répétable
        // les tests ont été repris et adaptés de ceux qu'on a dans textsProvider

        // Injecte le texte 'ES' dans le champ 'texts'
        $operation = $this->getOperation('objects', 'texts', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['objects' => [ ['texts' => ['ES']] ] ],
                true
            ],

            // Le champ existe dans le record mais il est vide (collection vide)
            [
                ['objects' => [ ['texts' => []] ] ],
                $operation,
                ['objects' => [ ['texts' => ['ES']] ] ],
                true
            ],

            // Le champ contient déjà des items
            [
                ['objects' => [ ['texts' => ['XX','YY']] ] ],
                $operation,
                ['objects' => [ ['texts' => ['XX','YY']],['texts' => ['ES']] ] ], // et non pas ['XX','YY','ES']
                true
            ],

            // Le champ a un item qui contient déjà le texte à injecter
            [
                ['objects' => [ ['texts' => ['XX','ES']] ] ],
                $operation,
                ['objects' => [ ['texts' => ['XX','ES']] ] ],
                false
            ],

            // Le champ contient plusieurs items qui contiennent le texte à injecter
            [
                ['objects' => [ ['texts' => ['ES','ES']] ] ],
                $operation,
                ['objects' => [ ['texts' => ['ES','ES']] ] ],
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
            // Injecte 'ES' dans le champ 'object.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'text', '', 'ES'),
                ['object' => ['text' => 'ES']],
                true
            ],
            // Injecte 'ES' dans le champ 'object.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'texts', '', 'ES'),
                ['object' => ['texts' => ['ES']]],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'text', '', 'ES'),
                ['objects' => [ ['text' => 'ES'] ] ],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'texts', '', 'ES'),
                ['objects' => [ ['texts' => ['ES'] ] ] ],
                true
            ],


            // Injecte 'ES' dans le champ 'objects.text', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                $this->getOperation('objects', 'text', '', 'ES'),
                ['objects' => [ ['text' => 'A'], ['text' => 'B'], ['text' => 'ES'] ] ],
                true
            ],
            // Injecte 'ES' dans le champ 'objects.texts', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                $this->getOperation('objects', 'texts', '', 'ES'),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']], ['texts' => ['ES']] ] ],
                true
            ],


            // Injecte 'ES' dans le champ 'objects.text', une des occurences du parent contient déjà le texte injecté
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'ES'] ] ],
                $this->getOperation('objects', 'text', '', 'ES'),
                ['objects' => [ ['text' => 'A'], ['text' => 'ES'] ] ],
                false
            ],
            // Injecte 'ES' dans le champ 'objects.texts', une des occurences du parent contient déjà le texte injecté
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','ES','D']] ] ],
                $this->getOperation('objects', 'texts', '', 'ES'),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','ES','D']] ] ],
                false
            ],
        ];
    }
}

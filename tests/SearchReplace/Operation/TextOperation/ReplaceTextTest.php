<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace\Operation\TextOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\OperationTestCase;

/**
 * Teste la classe ReplaceText.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReplaceTextTest extends OperationTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function textProvider(): array
    {
        // Remplace le texte 'es' par 'ES' dans le champ 'text'
        $operation = $this->getOperation('', 'text', 'es', 'ES');

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
                ['text' => ''],
                $operation,
                ['text' => ''],
                false
            ],

            // Le champ ne contient pas le texte recherché
            [
                ['text' => 'not found'],
                $operation,
                ['text' => 'not found'],
                false
            ],

            // Le champ contient une seule occurence du texte recherché
            [
                ['text' => 'Test'],
                $operation,
                ['text' => 'TESt'],
                true
            ],

            // Le champ contient plusieurs occurences du texte recherché
            [
                ['text' => 'Test tes est'],
                $operation,
                ['text' => 'TESt tES ESt'],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function textsProvider(): array
    {
        // Remplace le texte 'es' par 'ES' dans le champ 'texts'
        $operation = $this->getOperation('', 'texts', 'es', 'ES');

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
                ['texts' => []],
                $operation,
                ['texts' => []],
                false
            ],

            // Aucun item du champ ne contient le texte recherché
            [
                ['texts' => ['not', 'found']],
                $operation,
                ['texts' => ['not', 'found']],
                false
            ],

            // Un item du champ contient une seule occurence du texte recherché
            [
                ['texts' => ['Test']],
                $operation,
                ['texts' => ['TESt']],
                true
            ],

            // Un item du champ contient plusieurs occurences du texte recherché
            [
                ['texts' => ['Test tes']],
                $operation,
                ['texts' => ['TESt tES']],
                true
            ],

            // Plusieurs items du champ contiennent une occurence du texte recherché
            [
                ['texts' => ['Test', 'tes', 'tse', 'est']],
                $operation,
                ['texts' => ['TESt', 'tES', 'tse', 'ESt']],
                true
            ],

            // Plusieurs items du champ contiennent plusieurs occurences du texte recherché
            [
                ['texts' => ['Test tes', 'tse est']],
                $operation,
                ['texts' => ['TESt tES', 'tse ESt']],
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
            // Remplace 'es' par 'ES' dans le champ 'object.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'text', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'object.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'texts', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'text', 'es', 'ES'),
                [],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'texts', 'es', 'ES'),
                [],
                false
            ],


            // Remplace 'es' par 'ES' dans le champ 'objects.text', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                $this->getOperation('objects', 'text', 'es', 'ES'),
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                false
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.texts', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                $this->getOperation('objects', 'texts', 'es', 'ES'),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                false
            ],


            // Remplace 'es' par 'ES' dans le champ 'objects.text', une des occurences parent contient le texte cherché
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'es'] ] ],
                $this->getOperation('objects', 'text', 'es', 'ES'),
                ['objects' => [ ['text' => 'A'], ['text' => 'ES'] ] ],
                true
            ],
            // Remplace 'es' par 'ES' dans le champ 'objects.texts', une des occurences parent contient le texte cherché
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','es','D']] ] ],
                $this->getOperation('objects', 'texts', 'es', 'ES'),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','ES','D']] ] ],
                true
            ],
        ];
    }
}

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
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\Operation\TextOperation\RemoveText;

/**
 * Teste la classe RemoveText.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveTextTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new RemoveText(new Field('field', Field::TYPE_TEXT, false), 'search');
        $expected = 'Supprimer <del>search</del> dans le champ <var>field (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function textProvider(): array
    {
        // Supprime le texte 'es' dans le champ 'text'
        $operation = $this->getOperation('', 'text', 'es', '');

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
                ['text' => 'Tt'],
                true
            ],

            // Le champ contient plusieurs occurences du texte recherché
            [
                ['text' => 'Test tes est'],
                $operation,
                ['text' => 'Tt t t'],
                true
            ],

            // Le champ est vide après le chercher/remplacer
            [
                ['text' => 'eseses'],
                $operation,
                ['text' => ''],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function textsProvider(): array
    {
        // Supprime le texte 'es' dans le champ 'texts'
        $operation = $this->getOperation('', 'texts', 'es', '');

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
                ['texts' => ['Tt']],
                true
            ],

            // Un item du champ contient plusieurs occurences du texte recherché
            [
                ['texts' => ['Test tes']],
                $operation,
                ['texts' => ['Tt t']],
                true
            ],

            // Plusieurs items du champ contiennent une occurence du texte recherché
            [
                ['texts' => ['Test', 'tes', 'tse', 'est']],
                $operation,
                ['texts' => ['Tt', 't', 'tse', 't']],
                true
            ],

            // Plusieurs items du champ contiennent plusieurs occurences du texte recherché
            [
                ['texts' => ['Test tes', 'tse est']],
                $operation,
                ['texts' => ['Tt t', 'tse t']],
                true
            ],

            // Le champ est vide après le chercher/remplacer
            [
                ['texts' => ['es', 'eses']],
                $operation,
                ['texts' => []],
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
            // Supprime 'es' dans le champ 'object.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'text', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'object.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'texts', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'objects.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'text', 'es', ''),
                [],
                false
            ],
            // Supprime 'es' dans le champ 'objects.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'texts', 'es', ''),
                [],
                false
            ],

            // Supprime 'es' dans le champ 'objects.text', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                $this->getOperation('objects', 'text', 'es', ''),
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                false
            ],
            // Supprime 'es' dans le champ 'objects.texts', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                $this->getOperation('objects', 'texts', 'es', ''),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                false
            ],


            // Supprime 'es' dans le champ 'objects.text', une des occurences parent contient le texte cherché
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'es'] ] ],
                $this->getOperation('objects', 'text', 'es', ''),
                ['objects' => [ ['text' => 'A'], ['text' => ''] ] ],
                true
            ],
            // Supprime 'es' dans le champ 'objects.texts', une des occurences parent contient le texte cherché
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','es','D']] ] ],
                $this->getOperation('objects', 'texts', 'es', ''),
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','','D']] ] ],
                true
            ],
        ];
    }
}

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
use Docalist\Batch\SearchReplace\Operation\TextOperation\ClearText;

/**
 * Teste la classe ClearText.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ClearTextTest extends OperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = new ClearText(new Field('field', Field::TYPE_TEXT, false));
        $expected = 'Vider le champ <var>field (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * {@inheritDoc}
     */
    protected function textProvider(): array
    {
        // Vide le champ 'text'
        $operation = $this->getOperation('', 'text', '', '');

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
                [],
                true
            ],

            // Le champ contient quelque chose
            [
                ['text' => 'Test'],
                $operation,
                [],
                true
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function textsProvider(): array
    {
        // Vide le champ 'texts'
        $operation = $this->getOperation('', 'texts', '', '');

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
                [],
                true
            ],

            // Le champ contient un seul item
            [
                ['texts' => ['Test']],
                $operation,
                [],
                true
            ],

            // Le champ contient plusieurs items
            [
                ['texts' => ['Test', 'tes', 'tse', 'est']],
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
            // Vide le champ 'object.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'text', '', ''),
                [],
                false
            ],
            // Vide le champ 'object.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('object', 'texts', '', ''),
                [],
                false
            ],
            // Vide le champ 'objects.text', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'text', '', ''),
                [],
                false
            ],
            // Vide le champ 'objects.texts', le champ parent n'existe pas encore
            [
                [],
                $this->getOperation('objects', 'texts', '', ''),
                [],
                false
            ],


            // Vide le champ 'objects.text', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['text' => 'A'], ['text' => 'B'] ] ],
                $this->getOperation('objects', 'text', '', ''),
                ['objects' => [ [], [] ] ],
                true
            ],
            // Vide le champ 'objects.texts', le champ parent a déjà plusieurs occurences
            [
                ['objects' => [ ['texts' => ['A','B']], ['texts' => ['C','D']] ] ],
                $this->getOperation('objects', 'texts', '', ''),
                ['objects' => [ [], [] ] ],
                true
            ],
        ];
    }
}

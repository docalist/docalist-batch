<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace\Operation\TypedOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\TypedOperationTestCase;

/**
 * Teste les classes ClearText / ClearValue sur un champ Typed.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ClearTypedTest extends TypedOperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        $operation = $this->getOperation('typedText', 'one', '', '');

        $expected = 'Vider le champ <var>typedText/type.one (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * Fournit des opérations sur le champ 'typedText' (champ typé).
     *
     * @return array
     */
    protected function typedTextProvider(): array
    {
        // Vide le champ 'typedText/type.two'
        $operation = $this->getOperation('typedText', 'two', '', '');

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
                ['typedText' => []],
                $operation,
                ['typedText' => []],
                false
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'three','value'=>'c']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'three','value'=>'c']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedText' => [['type'=> 'one','value'=>'a'], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences du bon type
            [
                ['typedText' => [['type'=>'two','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedText' => [['type'=>'two'], ['type'=>'two']]],
                true
            ],
        ];
    }

    /**
     * Fournit des opérations sur le champ 'typedTexts' (champ typé).
     *
     * @return array
     */
    protected function typedTextsProvider(): array
    {
        // Vide le champ 'typedTexts/type.two'
        $operation = $this->getOperation('typedTexts', 'two', '', '');

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
                ['typedTexts' => []],
                $operation,
                ['typedTexts' => []],
                false
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedTexts' => [['type'=>'one','value'=> ['a']],['type'=>'three','value'=>['c']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=> ['a']],['type'=>'three','value'=>['c']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two','value' => []]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences
            [
                ['typedTexts' => [['type'=>'two','value'=>['a']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedTexts' => [['type'=>'two'], ['type'=>'two']]],
                true
            ],
        ];
    }

    /**
     * Fournit des opérations sur le champ 'typedValue' (champ typé).
     *
     * @return array
     */
    protected function typedValueProvider(): array
    {
        // Vide le champ 'typedValue/type.two'
        $operation = $this->getOperation('typedValue', 'two', '', '');

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
                ['typedValue' => []],
                $operation,
                ['typedValue' => []],
                false
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'three','value'=>'c']]],
                $operation,
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'three','value'=>'c']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                $operation,
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'']]],
                $operation,
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedValue' => [['type'=> 'one','value'=>'a'], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences
            [
                ['typedValue' => [['type'=>'two','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedValue' => [['type'=>'two'], ['type'=>'two']]],
                true
            ],
        ];
    }

    /**
     * Fournit des opérations sur le champ 'typedValues' (champ typé).
     *
     * @return array
     */
    protected function typedValuesProvider(): array
    {
        // Vide le champ 'typedValues/type.two'
        $operation = $this->getOperation('typedValues', 'two', '', '');

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
                ['typedValues' => []],
                $operation,
                ['typedValues' => []],
                false
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedValues' => [['type'=>'one','value'=> ['a']],['type'=>'three','value'=>['c']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=> ['a']],['type'=>'three','value'=>['c']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two','value' => []]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two']]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences
            [
                ['typedValues' => [['type'=>'two','value'=>['a']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedValues' => [['type'=>'two'], ['type'=>'two']]],
                true
            ],
        ];
    }
}

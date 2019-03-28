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

namespace Docalist\Batch\Tests\SearchReplace\Operation\TypedOperation;

use Docalist\Batch\Tests\SearchReplace\Operation\TypedOperationTestCase;

/**
 * Teste les classes InjectText / InjectValue sur un champ Typed.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class InjectTypedTest extends TypedOperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        // Injecte le texte 'ES' dans le champ 'typedText/type.one'
        $operation = $this->getOperation('typedText', 'one', '', 'ES');

        $expected = 'Injecter <ins>ES</ins> dans le champ <var>typedText/type.one (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * Fournit des opérations sur le champ 'typedText' (champ typé).
     *
     * @return array
     */
    protected function typedTextProvider(): array
    {
        // Injecte le texte 'ES' dans le champ 'typedText/type.one'
        $operation = $this->getOperation('typedText', 'two', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['typedText' => [ ]],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedText' => [ ['type'=>'one','value'=>'a'] ]],
                $operation,
                ['typedText' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedText' => [ ['type'=>'one','value'=>'a'], ['type'=>'two'] ]],
                $operation,
                ['typedText' => [ ['type'=>'one','value'=>'a'], ['type'=>'two'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedText' => [ ['type'=>'two','value'=>''] ]],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>''], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value ne contient pas la chaine
            [
                ['typedText' => [ ['type'=>'two','value'=>'b'] ]],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>'b'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient déjà la chaine
            [
                ['typedText' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                $operation,
                ['typedText' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                false
            ],

            // Le champ existe dans le record, le type existe, la chaine existe avec une casse différente
            [
                ['typedText' => [ ['type'=>'two','value'=>'es'] ]],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>'es'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedText' => [ ['type'=>'one','value'=>'ES'] ]],
                $operation,
                ['typedText' => [ ['type'=>'one','value'=>'ES'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la chaine
            [
                ['typedText' => [ ['type'=>'two','value'=>'ES ES'] ]],
                $operation,
                ['typedText' => [ ['type'=>'two','value'=>'ES ES'], ['type'=>'two','value'=>'ES'] ]],
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
        // Injecte le texte 'ES' dans le champ 'typedTexts/type.two'
        $operation = $this->getOperation('typedTexts', 'two', '', 'ES');

        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['typedTexts' => []],
                $operation,
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['a']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedTexts' => [ ['type'=>'two'] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedTexts' => [ ['type'=>'two','value'=>[]] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, un autre type contient déjà la chaine
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['ES']], ['type'=>'two','value'=>['b']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['ES']], ['type'=>'two','value'=>['b','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ contient déjà la valeur à injecter
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                false
            ],

            // Le champ existe dans le record, le type existe, la valeur existe avec une casse différente
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['es']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['es','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la valeur
            [
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES','ES']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES','ES']] ]],
                false
            ],

            // Le champ existe dans le record, le type a plusieurs occurences qui contiennent la chaine
            [
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']], ['type'=>'two','value'=>['ES']] ]],
                $operation,
                ['typedTexts' => [ ['type'=>'two','value'=>['ES']], ['type'=>'two','value'=>['ES']] ]],
                false
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
        // Injecte la valeur 'ES' dans le champ 'typedValue/type.two'
        $operation = $this->getOperation('typedValue', 'two', '', 'ES');

        // Ordre des champs : Record before, Operation, Record after, Modified
        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['typedValue' => []],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedValue' => [ ['type'=>'one','value'=>'a'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedValue' => [ ['type'=>'one','value'=>'a'], ['type'=>'two'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'one','value'=>'a'], ['type'=>'two'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedValue' => [ ['type'=>'two','value'=>''] ]],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>''], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value ne contient pas la chaine
            [
                ['typedValue' => [ ['type'=>'two','value'=>'b'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>'b'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient déjà la chaine
            [
                ['typedValue' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES'] ]],
                false
            ],

            // Le champ existe dans le record, le type existe, la valeur existe avec une casse différente
            [
                ['typedValue' => [ ['type'=>'two','value'=>'es'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>'es'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedValue' => [ ['type'=>'one','value'=>'ES'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'one','value'=>'ES'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la chaine
            [
                ['typedValue' => [ ['type'=>'two','value'=>'ES ES'] ]],
                $operation,
                ['typedValue' => [ ['type'=>'two','value'=>'ES ES'], ['type'=>'two','value'=>'ES'] ]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences du bon type
            [
                ['typedValue' => [ ['type'=>'two','value'=>'es'], ['type'=>'two','value'=>'es'] ]],
                $operation,
                ['typedValue' => [
                    ['type'=>'two','value'=>'es'],
                    ['type'=>'two','value'=>'es'],
                    ['type'=>'two','value'=>'ES']
                ]],
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
        // Injecte la valeur 'ES' dans le champ 'typedValues/type.two'
        $operation = $this->getOperation('typedValues', 'two', '', 'ES');

        return [
            // Le champ ne figure pas dans le record
            [
                [],
                $operation,
                ['typedValues' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record mais il est vide
            [
                ['typedValues' => []],
                $operation,
                ['typedValues' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record mais il ne contient pas le type demandé
            [
                ['typedValues' => [ ['type'=>'one','value'=>['a']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value n'existe pas
            [
                ['typedValues' => [ ['type'=>'two'] ]],
                $operation,
                ['typedValues' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value est vide
            [
                ['typedValues' => [ ['type'=>'two','value'=>[]] ]],
                $operation,
                ['typedValues' => [ ['type'=>'two','value'=>['ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['b','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, un autre type contient déjà la chaine
            [
                ['typedValues' => [ ['type'=>'one','value'=>['ES']], ['type'=>'two','value'=>['b']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['ES']], ['type'=>'two','value'=>['b','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ contient déjà la valeur à injecter
            [
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES']] ]],
                false
            ],

            // Le champ existe dans le record, le type existe, la valeur existe avec une casse différente
            [
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['es']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['es','ES']] ]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la valeur
            [
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES','ES']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'one','value'=>['a']], ['type'=>'two','value'=>['ES','ES']] ]],
                false
            ],

            // Le champ existe dans le record, le type a plusieurs occurences qui contiennent la chaine
            [
                ['typedValues' => [ ['type'=>'two','value'=>['ES']], ['type'=>'two','value'=>['ES']] ]],
                $operation,
                ['typedValues' => [ ['type'=>'two','value'=>['ES']], ['type'=>'two','value'=>['ES']] ]],
                false
            ],
        ];
    }
}

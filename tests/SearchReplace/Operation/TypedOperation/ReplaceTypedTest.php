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
 * Teste les classes ReplaceText / ReplaceValue sur un champ Typed.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReplaceTypedTest extends TypedOperationTestCase
{
    /**
     * Teste la méthode getExplanation().
     */
    public function testGetExplanation()
    {
        // Remplace le texte 'es' par 'ES' dans le champ 'typedText/type.one'
        $operation = $this->getOperation('typedText', 'one', 'es', 'ES');

        $expected = 'Remplacer <del>es</del> par <ins>ES</ins> dans le champ <var>typedText/type.one (texte)</var>.';
        $this->assertSame($expected, $operation->getExplanation());
    }

    /**
     * Fournit des opérations sur le champ 'typedText' (champ typé).
     *
     * @return array
     */
    protected function typedTextProvider(): array
    {
        // Remplace le texte 'es' par 'ES' dans le champ 'typedText/type.one'
        $operation = $this->getOperation('typedText', 'two', 'es', 'ES');

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
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value ne contient pas la chaine
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient la chaine
            [
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'es']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES']]],
                true
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedText' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'b']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la chaine
            [
                ['typedText' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'test es set']]],
                $operation,
                ['typedText' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'tESt ES set']]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences du bon type
            [
                ['typedText' => [['type'=>'two','value'=>'est'], ['type'=>'two','value'=>'tes tests']]],
                $operation,
                ['typedText' => [['type'=>'two','value'=>'ESt'], ['type'=>'two','value'=>'tES tESts']]],
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
        // Remplace le texte 'es' par 'ES' dans le champ 'typedTexts/type.two'
        $operation = $this->getOperation('typedTexts', 'two', 'es', 'ES');

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
                ['typedTexts' => [['type'=>'one','value'=>['a']], ['type'=>'two','value' => []]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ contient la chaine
            [
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['es']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['ES']]]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la chaine
            [
                ['typedTexts' => [['type'=>'one','value'=>['es','est']], ['type'=>'two','value'=>['es','est','test']]]],
                $operation,
                ['typedTexts' => [['type'=>'one','value'=>['es','est']], ['type'=>'two','value'=>['ES','ESt','tESt']]]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences qui contiennent la chaine
            [
                ['typedTexts' => [['type'=>'two','value'=>['est']], ['type'=>'two','value'=>['es', 'est','test']]]],
                $operation,
                ['typedTexts' => [['type'=>'two','value'=>['ESt']], ['type'=>'two','value'=>['ES', 'ESt','tESt']]]],
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
        // Remplace la valeur 'es' par 'ES' dans le champ 'typedValue/type.two'
        $operation = $this->getOperation('typedValue', 'two', 'es', 'ES');

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
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value ne contient pas la chaine
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedValue' => [['type'=> 'one','value'=>'a'], ['type'=>'two','value'=>'b']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient la valeur
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'es']]],
                $operation,
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'ES']]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient du texte similaire
            [
                ['typedValue' => [['type'=>'one','value'=>'a'], ['type'=>'two','value'=>'est']]],
                $operation,
                ['typedValue' => [['type'=> 'one','value'=>'a'], ['type'=>'two','value'=>'est']]],
                false
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedValue' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'b']]],
                $operation,
                ['typedValue' => [['type'=> 'one','value'=>'es'], ['type'=>'two','value'=>'b']]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ inclut le texte
            [
                ['typedValue' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'test es set']]],
                $operation,
                ['typedValue' => [['type'=>'one','value'=>'es'], ['type'=>'two','value'=>'test es set']]],
                false
            ],

            // Le champ existe dans le record, le type a plusieurs occurences qui contiennent la chaine
            [
                ['typedValue' => [['type'=>'two','value'=>'es'], ['type'=>'two','value'=>'es']]],
                $operation,
                ['typedValue' => [['type'=>'two','value'=>'ES'], ['type'=>'two','value'=>'ES']]],
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
        // Remplace la valeur 'es' par 'ES' dans le champ 'typedValues/type.two'
        $operation = $this->getOperation('typedValues', 'two', 'es', 'ES');

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
                ['typedValues' => [['type'=>'one','value'=>['a']], ['type'=>'two','value' => []]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient quelque chose
            [
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, un autre type contient la chaine
            [
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['b']]]],
                false
            ],

            // Le champ existe dans le record, le type existe, le sous-champ contient la chaine
            [
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['es']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['ES']]]],
                true
            ],

            // Le champ existe dans le record, le type existe, le sous-champ value contient plusieurs fois la chaine
            [
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['es','est','test']]]],
                $operation,
                ['typedValues' => [['type'=>'one','value'=>['es']], ['type'=>'two','value'=>['ES','est','test']]]],
                true
            ],

            // Le champ existe dans le record, le type a plusieurs occurences qui contiennent la chaine
            [
                ['typedValues' => [['type'=>'two','value'=>['es']], ['type'=>'two','value'=>['es', 'est','es']]]],
                $operation,
                ['typedValues' => [['type'=>'two','value'=>['ES']], ['type'=>'two','value'=>['ES', 'est','ES']]]],
                true
            ],
        ];
    }
}

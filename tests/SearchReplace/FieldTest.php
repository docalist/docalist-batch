<?php declare(strict_types=1);
/**
 * This file is part of Docalist Basket.
 *
 * Copyright (C) 2015-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace;

use WP_UnitTestCase;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\Tests\SearchReplace\TestRecord;
use InvalidArgumentException;

/**
 * Teste la classe Field.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class FieldTest extends WP_UnitTestCase
{
    public function testAddField()
    {
        $field = new Field('test', Field::TYPE_OBJECT);
        $field->addField(new Field('a'));
        $this->assertTrue($field->hasFields());
    }

    public function testAddFieldInTextField()
    {
        $field = new Field('test');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not an object');
        $field->addField(new Field('a'));
    }

    public function testAddFieldAlreadyHasParent()
    {
        $parent = new Field('parent', Field::TYPE_OBJECT);
        $field = new Field('test');
        $parent->addField($field);

        $parent2 = new Field('parent2', Field::TYPE_OBJECT);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('already has a parent');
        $parent2->addField($field);
    }

    public function testGetName()
    {
        $field = new Field('test');
        $this->assertSame('test', $field->getName());
    }

    public function testGetType()
    {
        $field = new Field('test');
        $this->assertSame(Field::TYPE_TEXT, $field->getType());

        foreach ([Field::TYPE_OBJECT, Field::TYPE_TEXT, Field::TYPE_VALUE] as $type) {
            $field = new Field('test', $type);
            $this->assertSame($type, $field->getType());
        }
    }

    public function testBadType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid field type');

        new Field('test', 0);
    }

    public function testIsType()
    {
        $field = new Field('test', Field::TYPE_OBJECT);
        $this->assertTrue($field->isObject());
        $this->assertFalse($field->isText());
        $this->assertFalse($field->isValue());

        $field = new Field('test', Field::TYPE_TEXT);
        $this->assertFalse($field->isObject());
        $this->assertTrue($field->isText());
        $this->assertFalse($field->isValue());

        $field = new Field('test', Field::TYPE_VALUE);
        $this->assertFalse($field->isObject());
        $this->assertFalse($field->isText());
        $this->assertTrue($field->isValue());
    }

    public function testIsRepeatable()
    {
        $field = new Field('test', Field::TYPE_TEXT);
        $this->assertFalse($field->isRepeatable());

        $field = new Field('test', Field::TYPE_TEXT, false);
        $this->assertFalse($field->isRepeatable());

        $field = new Field('test', Field::TYPE_TEXT, true);
        $this->assertTrue($field->isRepeatable());
    }

    public function testHasParent()
    {
        $field = new Field('field');
        $this->assertFalse($field->hasParent());

        $parent = new Field('parent', Field::TYPE_OBJECT);
        $parent->addField($field);

        $this->assertTrue($field->hasParent());
    }

    public function testGetParent()
    {
        $field = new Field('field', Field::TYPE_TEXT);
        $this->assertNull($field->getParent());

        $parent = new Field('parent', Field::TYPE_OBJECT);
        $parent->addField($field);

        $this->assertSame($parent, $field->getParent());
    }

    public function testGetKey()
    {
        $parent = new Field('parent', Field::TYPE_OBJECT);
        $this->assertSame('parent', $parent->getKey());

        $field = new Field('field', Field::TYPE_OBJECT);
        $this->assertSame('field', $field->getKey());

        $parent->addField($field);
        $this->assertSame('parent.field', $field->getKey());

        $subfield = new Field('subfield', Field::TYPE_TEXT, false);
        $field->addField($subfield);
        $this->assertSame('parent.field.subfield', $subfield->getKey());
    }

    public function testGetLabel()
    {
        // on teste juste que ça retourne des libellés différents selon le type et repeat et que ça contient la clé
        $seen = [];
        foreach ([Field::TYPE_OBJECT, Field::TYPE_TEXT, Field::TYPE_VALUE] as $type) {
            foreach([false, true] as $repeat) {
                $field = new Field('test', $type, $repeat);
                $label = $field->getLabel();

                $this->assertTrue(false !== strpos($label, $field->getKey()));
                $this->assertFalse(isset($seen[$label]));
                $seen[$label] = true;
            }
        }
    }

    /**
     * Fournit des exemples de chercher/remplacer pour un champ simple de type texte.
     *
     * @return array
     */
    public function modifyTextProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // Chercher/remplacer, text, non répétable, champ vide
            ['text', '', 'xx', 'yy', ''],

            // Chercher/remplacer, text, non répétable, aucune occurence
            ['text', 'Test', 'xx', 'yy', 'Test'],

            // Chercher/remplacer, text, non répétable, une occurence
            ['text', 'Test', 'es', 'ES', 'TESt'],

            // Chercher/remplacer, text, non répétable, plusieurs occurences
            ['text', 'Test tes est', 'es', 'ES', 'TESt tES ESt'],

            // --

            // Chercher/remplacer, text, répétable, champ vide
            ['texts', [], 'xx', 'yy', []],

            // Chercher/remplacer, text, répétable, aucune occurence
            ['texts', ['Test'], 'xx', 'yy', ['Test']],

            // Chercher/remplacer, text, répétable, une occurence
            ['texts', ['Test'], 'es', 'ES', ['TESt']],

            // Chercher/remplacer, text, répétable, plusieurs occurences
            ['texts', ['Test', 'tes', 'tse', 'est'], 'es', 'ES', ['TESt', 'tES', 'tse', 'ESt']],
        ];
    }

    /**
     * Fournit des exemples de chercher/remplacer pour un champ simple de type value.
     *
     * @return array
     */
    public function modifyValueProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // Chercher/remplacer, value, non répétable, champ vide
            ['value', '', 'xx', 'yy', ''],

            // Chercher/remplacer, value, non répétable, aucune occurence
            ['value', 'Test', 'Tes', 'yy', 'Test'],

            // Chercher/remplacer, value, non répétable, une occurence
            ['value', 'Test', 'Test', 'TEST', 'TEST'],

            // --

            // Chercher/remplacer, value, répétable, champ vide
            ['values', [], 'xx', 'yy', []],

            // Chercher/remplacer, value, répétable, aucune occurence
            ['values', ['Test'], 'xx', 'yy', ['Test']],

            // Chercher/remplacer, value, répétable, une occurence
            ['values', ['Test'], 'Test', 'TEST', ['TEST']],

            // Chercher/remplacer, value, répétable, plusieurs occurences
            ['values', ['Test', 'tes', 'Test', 'est'], 'Test', 'TEST', ['TEST', 'tes', 'TEST', 'est']],

        ];
    }

    /**
     * Fournit des exemples de chercher/remplacer pour un sous-champ simple de type texte.
     *
     * @return array
     */
    public function modifyObjectTextProvider(): array
    {
        $tests = $this->modifyTextProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples de chercher/remplacer pour un sous-champ simple de type texte.
     *
     * @return array
     */
    public function modifyObjectValueProvider(): array
    {
        $tests = $this->modifyValueProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples de suppressions pour un champ simple de type texte.
     *
     * @return array
     */
    public function deleteTextProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // Supprimer, text, non répétable, champ vide
            ['text', '', 'xx', '', ''],

            // Supprimer, text, non répétable, aucune occurence
            ['text', 'Test', 'xx', '', 'Test'],

            // Supprimer, text, non répétable, une occurence
            ['text', 'Test', 'es', '', 'Tt'],

            // Supprimer, text, non répétable, plusieurs occurences
            ['text', 'Test tes est', 'es', '', 'Tt t t'],

            // --

            // Supprimer, text, répétable, champ vide
            ['texts', [], 'xx', '', []],

            // Supprimer, text, répétable, aucune occurence
            ['texts', ['Test'], 'xx', '', ['Test']],

            // Supprimer, text, répétable, une occurence
            ['texts', ['Test'], 'es', '', ['Tt']],

            // Supprimer, text, répétable, plusieurs occurences
            ['texts', ['Test', 'tes', 'tse', 'est', 'es'], 'es', '', ['Tt', 't', 'tse', 't', '']],

        ];
    }

    /**
     * Fournit des exemples de suppressions pour un champ simple de type value.
     *
     * @return array
     */
    public function deleteValueProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // Supprimer, value, non répétable, champ vide
            ['value', '', 'xx', '', ''],

            // Supprimer, value, non répétable, aucune occurence
            ['value', 'Test', 'xx', '', 'Test'],

            // Supprimer, value, non répétable, une occurence
            ['value', 'Test', 'Test', '', ''],

            // --

            // Supprimer, value, répétable, champ vide
            ['values', [], 'xx', '', []],

            // Supprimer, value, répétable, aucune occurence
            ['values', ['Test'], 'Tes', '', ['Test']],

            // Supprimer, value, répétable, une occurence
            ['values', ['Test'], 'Test', '', ['']],

            // Supprimer, value, répétable, plusieurs occurences
            ['values', ['Test', 'tes', 'Test', 'est'], 'Test', '', ['', 'tes', '', 'est']],
        ];
    }

    /**
     * Fournit des exemples de suppressions pour un sous-champ simple de type texte.
     *
     * @return array
     */
    public function deleteObjectTextProvider(): array
    {
        $tests = $this->deleteTextProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples de suppressions pour un sous-champ simple de type value.
     *
     * @return array
     */
    public function deleteObjectValueProvider(): array
    {
        $tests = $this->deleteValueProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples d'injections pour un champ simple de type texte.
     *
     * @return array
     */
    public function injectTextProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // non répétable, champ vide
            ['text', '', '', 'yy', 'yy'],

            // non répétable, champ renseigné
            ['text', 'xx', '', 'yy', 'yy'],

            // non répétable, le champ contient déjà le texte à injecter
            ['text', 'xx', '', 'xx', 'xx'],

            // non répétable, le champ contient du texte qui inclue le texte recherché
            ['text', 'xx xx xx', '', 'xx', 'xx'],

            // --

            // répétable, champ vide
            ['texts', [], '', 'yy', ['yy']],

            // répétable, champ renseigné
            ['texts', ['xx'], '', 'yy', ['xx', 'yy']],

            // répétable, le champ contient déjà le texte à injecter
            ['texts', ['xx', 'yy'], '', 'yy', ['xx', 'yy']],
        ];
    }

    /**
     * Fournit des exemples d'injections pour un champ simple de type value.
     *
     * @return array
     */
    public function injectValueProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // non répétable, champ vide
            ['value', '', '', 'yy', 'yy'],

            // non répétable, le champ contient déjà une valeur
            ['value', 'xx', '', 'yy', 'yy'],

            // non répétable, le champ contient déjà la valeur à injecter
            ['value', 'xx', '', 'xx', 'xx'],

            // non répétable, le champ a une valeur qui inclue la valeur recherché
            ['value', 'xx xx xx', '', 'xx', 'xx'],

            // --

            // répétable, champ vide
            ['values', [], '', 'yy', ['yy']],

            // répétable, champ renseigné
            ['values', ['xx'], '', 'yy', ['xx', 'yy']],

            // Chercher/remplacer, value, répétable, une occurence
            ['values', ['Test'], 'Test', 'TEST', ['TEST']],

            // répétable, le champ contient déjà la valeur à injecter
            ['texts', ['xx', 'yy'], '', 'yy', ['xx', 'yy']],
        ];
    }

    /**
     * Fournit des exemples d'injections pour un sous-champ simple de type texte.
     *
     * @return array
     */
    public function injectObjectTextProvider(): array
    {
        $tests = $this->injectTextProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples d'injections pour un sous-champ simple de type value.
     *
     * @return array
     */
    public function injectObjectValueProvider(): array
    {
        $tests = $this->injectValueProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Fournit des exemples de suppressions pour un champ.
     *
     * @return array
     */
    public function clearProvider(): array
    {
        // Ordre des champs : Nom du champ, Valeur initiale, Rechercher, Remplacer par, Résultat attendu
        return [
            // non répétable, champ vide
            ['value', '', '', '', ''],

            // non répétable, le champ contient déjà une valeur
            ['value', 'xx', '', '', ''],

            // répétable, champ vide
            ['values', [], '', '', []],

            // répétable, champ renseigné
            ['values', ['xx'], '', '', ['']],
        ];
    }

    /**
     * Fournit des exemples de suppressions pour un sous-champ.
     *
     * @return array
     */
    public function clearObjectProvider(): array
    {
        $tests = $this->clearProvider();
        return array_merge(
            $this->applyProviderTo('object', $tests),
            $this->applyProviderTo('objects', $tests, true)
        );
    }

    /**
     * Modifie les tests fournis par un provider pour qu'il s'appliquent sur un sous-champ.
     *
     * @param string    $parent     Nom du champ parent ('object' ou 'objects').
     * @param array     $tests      Liste des tests à générer.
     * @param bool      $repeatable Indique si le champ parent est répétable ou non.
     *
     * @return array    Les tests modifiés.
     */
    private function applyProviderTo(string $parent, array $tests, bool $repeatable = false): array
    {
        foreach ($tests as & $test) {
            // echo json_encode($test), "\n";
            $field = $test[0];
            $result = $test[4];

            $test[0] = "$parent.$field";        // Nom du champ
            $test[1] = [$field => $test[1]];    // Valeur initiale
            $test[4] = [$field => $test[4]];    // Résultat attendu

            // Composite::getPhpValue() fait des optimisations qui nous embètent : si le sous-champ
            // est une collection vide, il ne retourne pas le sous-champ
            $test[4] = ($result === []) ? [] : [$field => $result];

            if ($repeatable) {
                $test[1] = [$test[1]];
                $test[4] = [$test[4]];
            }

            // echo json_encode($test), "\n";
        }
        //die();
        return $tests;
    }

    /**
     * Teste getOperation()
     *
     * @dataProvider modifyTextProvider
     * @dataProvider modifyValueProvider
     * @dataProvider modifyObjectTextProvider
     * @dataProvider modifyObjectValueProvider
     *
     * @dataProvider deleteTextProvider
     * @dataProvider deleteValueProvider
     * @dataProvider deleteObjectTextProvider
     * @dataProvider deleteObjectValueProvider
     *
     * @dataProvider injectTextProvider
     * @dataProvider injectValueProvider
     * @dataProvider injectObjectTextProvider
     * @dataProvider injectObjectValueProvider
     *
     * @dataProvider clearProvider
     * @dataProvider clearObjectProvider
     */
    public function testGetOperation(string $name, $value, string $search, string $replace, $result)
    {
        // Génère l'objet Field à tester (il a besoin du nom complet de la forme "object.text")
        $testField = $this->getTestField($name);

        // Pour notre part, on ne s'occupe que du champ parent ("object")
        $field = strtok($name, '.');

        // Génère un record contenant les données fournées
        $record = new TestRecord([$field => $value]);

        // Exécute l'opération demandée
        $operation = $testField->getOperation($search, $replace);
        $changed = $operation($record);

        // Vérifie que le résultat obtenu correspond à ce qu'on attend
        $this->assertSame($result, $record->__get($field)->getPhpValue());

        // Composite::getPhpValue fait des optimisations, on ne peut pas tester $changed pour les tableaux vides
        if ($testField->hasParent() && ($result === [] || $result === [[]])) {
            return;
        }

        // Vérifie que l'opération a bien retourné true si elle a modifié le champ
        $this->assertSame($changed, $result !== $value);
    }

    private function getTestField(string $name): Field
    {
        $parent = null;
        (strpos($name, '.') !== false) && list($parent, $name) = explode('.', $name, 2);

        switch ($parent) {
            case null:
                break;
            case 'object':
                $parent = new Field($parent, Field::TYPE_OBJECT);
                break;
            case 'objects':
                $parent = new Field($parent, Field::TYPE_OBJECT, true);
                break;
            default:
                die('champ parent non géré ' . $parent);
        }

        switch ($name) {
            case 'text':
                $repeat = false;
                $type = Field::TYPE_TEXT;
                break;
            case 'texts':
                $repeat = true;
                $type = Field::TYPE_TEXT;
                break;
            case 'value':
                $repeat = false;
                $type = Field::TYPE_VALUE;
                break;
            case 'values':
                $repeat = true;
                $type = Field::TYPE_VALUE;
                break;
            default:
                die('champ non géré ' . $name);
        }

        $field = new Field($name, $type, $repeat);
        !is_null($parent) && $parent->addField($field);

        return $field;
    }

    /**
     * Vérifie qu'une exception est générée si on essaie de lancer un chercher/remplacer sur un composite.
     */
    public function testGetOperationOnObject()
    {
        $field = new Field('test', FIELD::TYPE_OBJECT);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('not allowed');
        $field->getOperation('', '');
    }
}

<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Tests\SearchReplace\Operation;

use PHPUnit_Framework_TestCase;
use Docalist\Batch\SearchReplace\Operation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\Tests\SearchReplace\TestRecord;
use InvalidArgumentException;

/**
 * Une version spécialisée de PHPUnit_Framework_TestCase qui automatise les tests sur les opérations.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class OperationTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Teste process()
     */
    public function testProcess()
    {
        /*
         * On gère nous même la liste des tests plutôt que des @dataProvider phpunit car ça pose un problème
         * pour la couverture (le code exécuté par les dataProvider n'est pas considéré comme faisant partie des tests)
         */

        foreach ($this->getProviders() as $provider) {
            foreach ($this->$provider() as $i => $test) {
                list($before, $operation, $after, $modified) = $test; /** @var Operation $operation */

                $message = sprintf(
                    '%s#%d - replace(%s, %s, %s) in %s',
                    $provider,
                    $i,
                    json_encode($operation->getField()->getKey()),
                    json_encode($operation->getSearch()),
                    json_encode($operation->getReplace()),
                    json_encode($before)
                );

                /*
                 * Dans les tests, on indique le résultat logique attendu. le problème c'est que
                 * Composite::getPhpValue() fait des optimisations qui nous empêchent de comparer directement avec
                 * le résultat obtenu (par exemple si un champ contient une collection vide, le champ est supprimé).
                 * Pour contourner ça, on crée un record avec le résultat attendu, on appelle getPhpValue() pour
                 * obtenir le résultat "optimisé" et c'est avec ça qu'on compare le résultat obtenu.
                 */
                $record = new TestRecord($after);
                $record->filterEmpty(true);
                $after = $record->getPhpValue();

                $record = new TestRecord($before);
                $result = $operation->process($record);
                $record->filterEmpty(true);

                $this->assertSame($after, $record->getPhpValue(), $message);
                $this->assertSame($modified, $result, $message);
            }
        }
    }

    /**
     * Retourne la liste des providers pour testProcess().
     *
     * @return array Un tableau contenant le nom des méthodes qui retournent des tests.
     */
    protected function getProviders(): array
    {
        return [
            'textProvider',
            'textsProvider',
            'objectTextProvider',
            'objectTextsProvider',
            'objectsTextProvider',
            'objectsTextsProvider',

            'valueProvider',
            'valuesProvider',
            'objectValueProvider',
            'objectValuesProvider',
            'objectsValueProvider',
            'objectsValuesProvider',

            'otherTestsProvider',
        ];
    }

    /**
     * Modifie les tests fournis par un provider pour qu'il s'appliquent sur un sous-champ.
     *
     * @param string    $parent     Nom du champ parent ('object' ou 'objects').
     * @param array     $tests      Liste des tests à générer.
     *
     * @return array    Les tests modifiés.
     */
    protected function applyProviderTo(string $parent, array $tests): array
    {
        // Sanity check
        if (empty($tests)) {
            return [];
        }

        // Récupère l'opération initiale (on suppose que c'est la même pour tous les tests)
        $operation = $tests[0][1]; /** @var Operation $operation */

        // Crée l'opération modifiée
        $operation = $this->getOperation(
            $parent,                            // Le parent demandé
            $operation->getField()->getName(),  // Le nom du champ sur lequel portait initialement l'opération
            $operation->getSearch(),
            $operation->getReplace()
        );

        // Détermine si le champ parent est répétable ou non
        $repeat = $parent === 'objects';

        // Modifie les tests
        foreach ($tests as & $test) {
            $test[0] = [$parent => $repeat ? [$test[0]] : $test[0]];
            $test[1] = $operation;
            $test[2] = [$parent => $repeat ? [$test[2]] : $test[2]];
        }

        // Ok
        return $tests;
    }

    /**
     * Génère une opération sur le champ indiqué.
     *
     * @param string $parent    Nom du champ parent : '', 'object' ou 'objects'.
     * @param string $field     Nom du champ 'text', 'text', 'value' ou 'values'.
     * @param string $search    Chaine recherchée
     * @param string $replace   Chaine de remplacement
     *
     * @return Operation Opération générée
     */
    protected function getOperation(string $parent, string $field, string $search, string $replace): Operation
    {
        $type = ($field === 'text' || $field === 'texts') ? Field::TYPE_TEXT : Field::TYPE_VALUE;
        $field = new Field($field, $type, $field === 'texts' || $field === 'values');
        if (!empty($parent)) {
            $parent = new Field($parent, Field::TYPE_OBJECT, $parent === 'objects');
            $parent->addField($field);
        }
        return $field->getOperation($search, $replace);
    }

    /**
     * Fournit des opérations sur le champ 'text' (champ simple, non répétable).
     *
     * @return array
     */
    protected function textProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'texts' (champ simple, répétable).
     *
     * @return array
     */
    protected function textsProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'object.text' (parent non répétable, sous-champ non répétable).
     *
     * @return array
     */
    protected function objectTextProvider(): array
    {
        return $this->applyProviderTo('object', $this->textProvider());
    }

    /**
     * Fournit des opérations sur le champ 'object.texts' (parent non répétable, sous-champ répétable).
     *
     * @return array
     */
    protected function objectTextsProvider(): array
    {
        return $this->applyProviderTo('object', $this->textsProvider());
    }

    /**
     * Fournit des opérations sur le champ 'objects.text' (parent répétable, sous-champ non répétable).
     *
     * @return array
     */
    protected function objectsTextProvider(): array
    {
        return $this->applyProviderTo('objects', $this->textProvider());
    }

    /**
     * Fournit des opérations sur le champ 'objects.texts' (parent répétable, sous-champ répétable).
     *
     * @return array
     */
    protected function objectsTextsProvider(): array
    {
        return $this->applyProviderTo('object', $this->textsProvider());
    }

    /**
     * Fournit des opérations sur le champ 'value' (champ valeur, non répétable).
     *
     * @return array
     */
    protected function valueProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'values' (champ valeur, répétable).
     *
     * @return array
     */
    protected function valuesProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'object.value' (parent non répétable, sous-champ non répétable).
     *
     * @return array
     */
    protected function objectValueProvider(): array
    {
        return $this->applyProviderTo('object', $this->valueProvider());
    }

    /**
     * Fournit des opérations sur le champ 'object.values' (parent non répétable, sous-champ répétable).
     *
     * @return array
     */
    protected function objectValuesProvider(): array
    {
        return $this->applyProviderTo('object', $this->valuesProvider());
    }

    /**
     * Fournit des opérations sur le champ 'objects.value' (parent répétable, sous-champ non répétable).
     *
     * @return array
     */
    protected function objectsValueProvider(): array
    {
        return $this->applyProviderTo('objects', $this->valueProvider());
    }

    /**
     * Fournit des opérations sur le champ 'objects.values' (parent répétable, sous-champ répétable).
     *
     * @return array
     */
    protected function objectsValuesProvider(): array
    {
        return $this->applyProviderTo('object', $this->valuesProvider());
    }

    /**
     * Autres tests.
     *
     * @return array
     */
    protected function otherTestsProvider(): array
    {
        return [];
    }
}

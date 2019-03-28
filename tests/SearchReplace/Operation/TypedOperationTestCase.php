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

namespace Docalist\Batch\Tests\SearchReplace\Operation;

use Docalist\Batch\Tests\SearchReplace\Operation\OperationTestCase;
use Docalist\Batch\SearchReplace\Operation;
use Docalist\Batch\SearchReplace\FieldsBuilder;
use Docalist\Batch\Tests\SearchReplace\TestRecord;

/**
 * Une version spécialisée de OperationTestCase pour les tests sur les champs Typed.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class TypedOperationTestCase extends OperationTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getProviders(): array
    {
        return [
            'typedTextProvider',
            'typedTextsProvider',

            'typedValueProvider',
            'typedValuesProvider',

            'otherTestsProvider',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getOperation(string $parent, string $field, string $search, string $replace): Operation
    {
        $fields = new FieldsBuilder();
        $fields->addFieldsFromRecord(new TestRecord());
        $fields->addTypedFields($parent, ['one', 'two', 'three']);

        return $fields->getField($parent . '/type')->getField($field)->getOperation($search, $replace);
    }

    /**
     * Fournit des opérations sur le champ 'typedText' (champ typé, sous champ value de type text).
     *
     * @return array
     */
    protected function typedTextProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'typedTexts' (champ typé, sous champ value de type text répétable).
     *
     * @return array
     */
    protected function typedTextsProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'typedValue' (champ typé, sous champ value de type value).
     *
     * @return array
     */
    protected function typedValueProvider(): array
    {
        return [];
    }

    /**
     * Fournit des opérations sur le champ 'typedValues' (champ typé, sous champ value de type value répétable).
     *
     * @return array
     */
    protected function typedValuesProvider(): array
    {
        return [];
    }
}

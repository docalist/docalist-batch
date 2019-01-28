<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace\Operation\ValueOperation;

use Docalist\Batch\SearchReplace\Operation\TextOperation\RemoveText;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Type\Scalar;

/**
 * Supprime un item dans un champ value.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveValue extends RemoveText
{
    /**
     * {@inheritDoc}
     */
    protected const FIELD_TYPE = Field::TYPE_VALUE;

    /**
     * {@inheritDoc}
     */
    protected function replace(Scalar $field, string $search, string $replace)
    {
        // Si le champ ne contient pas la valeur exacte recherché, retourne false pour indiquer "non modifié"
        if ($field->getPhpValue() !== $search) {
            return false;
        }

        // Stocke la valeur de remplacement dans le champ et retourne true
        $field->assign($replace);

        return true;
    }
}
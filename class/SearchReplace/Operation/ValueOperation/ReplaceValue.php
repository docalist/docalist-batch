<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace\Operation\ValueOperation;

use Docalist\Batch\SearchReplace\Operation\ValueOperation\ReplaceTrait;
use Docalist\Batch\SearchReplace\Operation\TextOperation\ReplaceText;
use Docalist\Type\Scalar;
use Docalist\Batch\SearchReplace\Field;

/**
 * Remplace une valeur dans un champ Value.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReplaceValue extends ReplaceText
{
    use ReplaceTrait;

    /**
     * {@inheritDoc}
     */
    protected const FIELD_TYPE = Field::TYPE_VALUE;
}

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

use Docalist\Batch\SearchReplace\Operation\TextOperation\InjectText;
use Docalist\Batch\SearchReplace\Field;

/**
 * Injecte un item dans un champ value.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class InjectValue extends InjectText
{
    /**
     * {@inheritDoc}
     */
    protected const FIELD_TYPE = Field::TYPE_VALUE;
}
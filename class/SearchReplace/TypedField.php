<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\SearchReplace\Field;
use Docalist\Type\TypedText;
use Docalist\Type\Collection;

/**
 * Champ spécial utilisé pour les champs de type TypedText présentés en vue éclatée (Topic, Number).
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class TypedField extends Field
{
    /**
     *
     * @var string
     */
    private $condition;

    /**
     * @var Field
     */
    private $realField;

    /**
     * Initialise le champ Typed.
     *
     * @param string    $condition  Condition à appliquer sur le type du champ parent.
     * @param Field     $type       Champ value réel sur lequel porte l'opération.
     */
    public function __construct(string $condition, Field $realField)
    {
        $this->condition = $condition;
        $this->realField = $realField;
        parent::__construct($condition, $realField->getType(), $realField->isRepeatable());
    }

    /**
     * {@inheritDoc}
     */
    public function getOperation(string $search, string $replace): Operation
    {
        $operation = $this->realField->getOperation($search, $replace);
        $operation->setCondition($this->condition);

        $explanation = parent::getOperation($search, $replace)->getExplanation();
        $operation->setExplanation($explanation);

        return $operation;
    }
}

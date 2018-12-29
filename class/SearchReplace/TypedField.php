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
    public function getLabel(): string
    {
        $label = $this->getName();

        $type = $this->getParent()->getName();
        $this->isRepeatable() && $type .= ', répétable';

        return $label . ' (' . $type . ')';
    }

    protected function parent(callable $operation): callable
    {
        // Le nom (court) du champ correspond au type d'entrée à modifier (par exemple "free" pour les topics)
        $type = $this->getName();

        // Le champ parent (TypedText) est répétable
        return function (Collection $collection) use ($type, $operation): bool {
            // Crée un nouvel élément du type indiqué. C'est nécessaire pour injecter une valeur (par exemple
            // un nouvel isbn). Pour les autres opérations, sera supprimé lors du save (appel de filterEmpty).
            $collection[] = ['type' => $type];

            // Applique l'opération à tous les éléments qui ont le type demandé
            $result = false;
            foreach ($collection as $typedText) { /** @var TypedText $typedText */
                if ($typedText->type->getPhpValue() === $type) {
                    $changed = $operation($typedText->value);
                    $result = $result || $changed;
                }
            }

            // Ok
            return $result;
        };
    }
}

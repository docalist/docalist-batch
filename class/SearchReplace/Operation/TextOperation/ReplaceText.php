<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace\Operation\TextOperation;

use Docalist\Batch\SearchReplace\Operation\BaseOperation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Data\Record;
use Docalist\Type\Composite;
use Docalist\Type\Scalar;

/**
 * Un chercher/remplacer sur un champ texte.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ReplaceText extends BaseOperation
{
    /**
     * Méthode de base utilisée pour faire le chercher/remplacer.
     *
     * Pour un champ texte, la méthode utilise str_replace().
     * La méthode est surchargée dnas ReplaceValue pour opérer sur la totalité du champ.
     *
     * @param Scalar $field     Champ à modifier.
     * @param string $search    Chaine recherchée.
     * @param string $replace   Chaine de remplacement.
     *
     * @return boolean True si le champ a été modifié, false sinon.
     */
    protected function replace(Scalar $field, string $search, string $replace)
    {
        // Fait le chercher/remplacer
        $count = 0;
        $value = str_replace($search, $replace, $field->getPhpValue(), $count);

        // Si la valeur recherchée n'a pas été trouvée, retourne false pour indiquer qu'on n'a rien modifié
        if ($count === 0) {
            return false;
        }

        // Stocke la valeur modifiée dans le champ et retourne true
        $field->assign($value);

        return true;
    }

    /**
     * {@inheritDoc}
     */
    protected function createProcess(): callable
    {
        $search = $this->getSearch();
        $replace = $this->getReplace();

        // Le process de base opère sur un scalaire et se contente d'appeller la méthode replace()
        $process = function (Scalar $field) use ($search, $replace): bool {
            return $this->replace($field, $search, $replace);
        };

        // Si le champ est répétable, on itère sur tous les éléments de la collection
        $field = $this->getField();
        $field->isRepeatable() && $process = $this->collection($process);

        // Applique au containeur du champ
        $name = $field->getName();
        $process = function (Composite $parent) use ($name, $process): bool {
            return isset($parent->$name) && $process($parent->$name);
        };

        // Si le champ n'a pas de champ parent, terminé
        $parent = $field->getParent();
        if (is_null($parent)) {
            return $process;
        }

        // Si le parent est répétable, on itère sur tous les éléments du parent
        $parent->isRepeatable() && $process = $this->parentCollection($process);

        // Applique le process au champ parent
        $name = $parent->getName();
        return function (Record $record) use ($name, $process): bool {
            return isset($record->$name) && $process($record->$name);
        };
    }
}

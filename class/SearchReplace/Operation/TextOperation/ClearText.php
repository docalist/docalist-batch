<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace\Operation\TextOperation;

use Docalist\Batch\SearchReplace\Operation\BaseOperation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Data\Record;
use Docalist\Type\Composite;

/**
 * Vide le contenu d'un champ texte.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class ClearText extends BaseOperation
{
    /**
     * Constructeur.
     *
     * @param Field $field Champ à vider.
     */
    public function __construct(Field $field)
    {
        parent::__construct($field, '', '');
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultExplanation(): string
    {
        return sprintf(
            __('Vider le champ <var>%s</var>.', 'docalist-batch'),
            $this->getField()->getLabel()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function createProcess(): callable
    {
        $field = $this->getField();
        $name = $field->getName();

        // Le process de base opère sur un Composite (champ : un Record, sous-champ : le Composite parent)
        $process = function (Composite $parent) use ($name): bool {
            // Si le champ n'existe pas, on ne fait rien et on retourne false pour indiquer qu'on n'a rien modifié
            if (! isset($parent->$name)) {
                return false;
            }

            // Vide le champ ou le sous-champ
            unset($parent->$name);

            // Retourne true pour indiquer qu'on a fait des modifications
            return true;
        };

        // Si le champ n'a pas de champ parent, terminé
        $parent = $field->getParent();
        if (is_null($parent)) {
            return $process;
        }

        // Si le parent est répétable, on va itèrer sur tous les éléments de la collection parent
        $parent->isRepeatable() && $process = $this->parentCollection($process);

        // Applique le process au champ parent
        $name = $parent->getName();
        return function (Record $record) use ($name, $process): bool {
            // Si le champ parent n'existe pas, on ne fait rien et on retourne false (non modifié)
            if (! isset($record->$name)) {
                return false;
            }

            // Applique le process au champ parent
            return $process($record->$name);
        };
    }
}

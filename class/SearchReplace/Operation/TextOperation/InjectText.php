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

namespace Docalist\Batch\SearchReplace\Operation\TextOperation;

use Docalist\Batch\SearchReplace\Operation\BaseOperation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Data\Record;
use Docalist\Type\Composite;

/**
 * Injecte une chaine dans un champ texte.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class InjectText extends BaseOperation
{
    /**
     * Constructeur.
     *
     * @param Field     $field      Champ sur lequel porte l'opération.
     * @param string    $inject     Chaine à injecter.
     */
    public function __construct(Field $field, string $inject)
    {
        parent::__construct($field, '', $inject);
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultExplanation(): string
    {
        return sprintf(
            __('Injecter <ins>%s</ins> dans le champ <var>%s</var>.', 'docalist-batch'),
            htmlspecialchars($this->getReplace()),
            $this->getField()->getLabel()
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function createProcess(): callable
    {
        $field = $this->getField();

        // Le process de base est différent selon que le champ est répétable ou non
        $process = $field->isRepeatable() ? $this->injectRepeatable() : $this->injectNotRepeatable();

        // Si le champ n'a pas de champ parent, terminé, on applique le process au record
        $parent = $field->getParent();
        if (is_null($parent)) {
            return $process;
        }

        // Champ parent non répétable
        $name = $parent->getName();
        if (! $parent->isRepeatable()) {
            return function (Record $record) use ($name, $process): bool {
                return $process($record->$name); // Crée le champ $name s'il n'existe pas déjà
            };
        }

        // Teste s'il s'agit d'une opération sur un champ Typed
        $condition = $this->getCondition();

        // Crée un prédicat qui teste si le texte à injecter existe déjà
        $repeatable = $field->isRepeatable();
        $exists = $repeatable ? $this->existsRepeatable() : $this->existsNotRepeatable();

        // Champ parent répétable standard
        if (empty($condition)) {
            return function (Record $record) use ($name, $exists, $process): bool {
                foreach ($record->$name as $occurence) {
                    if ($exists($occurence)) {
                        return false; // L'item à injecter existe déjà, retourne false (non modifié)
                    }
                }

                // Crée un nouvel item dans la collection et exécute le process dessus
                $record->$name[] = [];
                return $process($record->$name->last());
            };
        }

        // Opération sur un champ Typed
        return function (Record $record) use ($name, $exists, $process, $condition, $repeatable): bool {
            $indexOfType = null;

            // Teste si le champ parent a un item du bon type qui contient déjà la valeur à injecter
            foreach ($record->$name as $index => $occurence) { /** @var TypedText $occurence */
                if ($occurence->type->getPhpValue() === $condition) {
                    if ($exists($occurence)) {
                        return false; // L'item à injecter existe déjà, retourne false (non modifié)
                    }
                    is_null($indexOfType) && $indexOfType = $index;
                }
            }

            // La valeur à injecter n'existe pas déjà
            if ($repeatable) {
                if (is_null($indexOfType)) {
                    $record->$name[] = ['type' => $condition];
                    return $process($record->$name->last());
                }
                return $process($record->$name[$indexOfType]);
            }

            $record->$name[] = ['type' => $condition];
            return $process($record->$name->last());
        };
    }

    /**
     * Retourne un callable qui injecte du contenu dans un champ non répétable.
     *
     * Le callback généré teste si la valeur actuelle du champ est identique à la valeur à injecter.
     * Si c'est le cas, elle ne fait rien et retourne false, sinon elle assigne la valeur au champ et retourne true.
     *
     * @return callable Un callable de la forme "function (Composite): bool".
     */
    private function injectNotRepeatable(): callable
    {
        $name = $this->getField()->getName();
        $inject = $this->getReplace();

        return function (Composite $parent) use ($name, $inject): bool {
            // Si le champ existe et contient déjà la valeur à injecter, terminé
            if (isset($parent->$name) && $parent->$name->getPhpValue() === $inject) {
                return false;
            }

            // Injecte la valeur dans le champ
            $parent->$name = $inject;

            // Retourne true pour indiquer qu'on a fait des modifications
            return true;
        };
    }

    /**
     * Retourne un callable qui teste si le contenu à injecter figure déjà dans un champ répétable.
     *
     * Le callable généré teste si la valeur à injecter figure déjà dans la liste des valeurs du champs.
     * Si c'est le cas, elle ne fait rien et retourne false, sinon, elle ajoute la nouvelle valeur à la fin
     * de la collection et retourne true.
     *
     * @return callable Un callable de la forme "function (Composite): bool".
     */
    private function injectRepeatable(): callable
    {
        $name = $this->getField()->getName();
        $inject = $this->getReplace();
        return function (Composite $parent) use ($name, $inject): bool {
            // Si le champ existe et que la collection contient déjà la valeur à injecter, terminé
            if (isset($parent->$name)) {
                foreach ($parent->$name as $occurence) {
                    if ($occurence->getPhpValue() === $inject) {
                        return false;
                    }
                }
            }

            // Ajoute la valeur à injecter dans la collection du champ
            $parent->$name[] = $inject;

            // Retourne true pour indiquer qu'on a fait des modifications
            return true;
        };
    }

    /**
     * Retourne un callable qui teste si le contenu à injecter figure déjà dans un champ non répétable.
     *
     * Le callback généré teste si la valeur actuelle du champ est identique à la valeur à injecter.
     * Il retourne true si c'est les cas, false sinon.
     *
     * @return callable Un callable de la forme "function (Composite): bool".
     */
    private function existsNotRepeatable(): callable
    {
        $name = $this->getField()->getName();
        $inject = $this->getReplace();

        return function (Composite $parent) use ($name, $inject): bool {
            // Si le champ existe et contient déjà la valeur à injecter, retourne true
            if (isset($parent->$name) && $parent->$name->getPhpValue() === $inject) {
                return true;
            }

            // Retourne false pour indiquer que la valeur n'existe pas déjà
            return false;
        };
    }

    /**
     * Retourne un callable qui injecte du contenu dans un champ répétable.
     *
     * Le callable généré teste si la valeur à injecter figure déjà dans la liste des valeurs du champs.
     * Il retourne true si c'est les cas, false sinon.
     *
     * @return callable Un callable de la forme "function (Composite): bool".
     */
    private function existsRepeatable(): callable
    {
        $name = $this->getField()->getName();
        $inject = $this->getReplace();
        return function (Composite $parent) use ($name, $inject): bool {
            // Si le champ existe et que la collection contient déjà la valeur à injecter, retourne true
            if (isset($parent->$name)) {
                foreach ($parent->$name as $occurence) {
                    if ($occurence->getPhpValue() === $inject) {
                        return true;
                    }
                }
            }

            // Retourne false pour indiquer que la valeur n'existe pas déjà
            return false;
        };
    }
}

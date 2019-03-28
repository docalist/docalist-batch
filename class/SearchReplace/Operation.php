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

namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\SearchReplace\Field;
use Docalist\Data\Record;

/**
 * Interface d'une opération de chercher/remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Operation
{
    /**
     * Retourne le champ sur lequel porte l'opération.
     *
     * @return Field
     */
    public function getField(): Field;

    /**
     * Retourne la chaine recherchée.
     *
     * @return string
     */
    public function getSearch(): string;

    /**
     * Retourne la chaine de remplacement.
     *
     * @return string
     */
    public function getReplace(): string;

    /**
     * Exécute l'opération sur l'enregistrement passé en paramètre.
     *
     * @param Record $record L'enregistrement à modifier.
     *
     * @return bool True si l'enregistrement a été modifié, false si la chaine recherchée n'a pas été trouvée.
     */
    public function process(Record $record): bool;

    /**
     * Modifie l'explication de l'opération.
     *
     * @param string $explanation
     */
    public function setExplanation(string $explanation):void;

    /**
     * Retourne une chaine qui explique ce que fait l'opération.
     *
     * @return string
     */
    public function getExplanation(): string;

    /**
     * Modifie la condition à appliquer au champ parent.
     *
     * Utilisé uniquement par les TypedField.
     *
     * @param string $condition
     */
    public function setCondition(string $condition): void;

    /**
     * Retourne la condition à appliquer au parent.
     *
     * @return string
     */
    public function getCondition(): string;
}

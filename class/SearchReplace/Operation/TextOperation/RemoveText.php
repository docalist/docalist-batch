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

use Docalist\Batch\SearchReplace\Operation\TextOperation\ReplaceText;
use Docalist\Batch\SearchReplace\Field;

/**
 * Supprime une chaine dans un champ texte.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class RemoveText extends ReplaceText
{
    /**
     * Constructeur.
     *
     * @param Field     $field      Champ sur lequel porte l'opération.
     * @param string    $remove     Chaine à supprimer.
     */
    public function __construct(Field $field, string $remove)
    {
        parent::__construct($field, $remove, '');
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultExplanation(): string
    {
        return sprintf(
            __('Supprimer <del>%s</del> dans le champ <var>%s</var>.', 'docalist-batch'),
            htmlspecialchars($this->getSearch()),
            $this->getField()->getLabel()
        );
    }
}

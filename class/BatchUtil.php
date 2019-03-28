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

namespace Docalist\Batch;

use Docalist\Tools\Tool;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Data\Database;
use Docalist\Data\Record;

/**
 * Méthodes utilitaires pour les vues générées par les lots traitements par lot.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface BatchUtil
{
    /**
     * Classes CSS d'un bouton principal.
     */
    public const PRIMARY_BUTTON = 'button button-primary button-large';

    /**
     * Classes CSS d'un bouton secondaire.
     */
    public const SECONDARY_BUTTON = 'button button-large';


    /**
     * Génère un lien "Retour à la page précédente".
     *
     * @param string $class Classes css du lien généré (SECONDARY_BUTTON par défaut).
     * @param string $title Texte du lien ('Retour à la page précédente' par défaut).
     *
     * @return string
     */
    public function backButton(string $class = 'button', string $title = '');

    /**
     * Génère un lien "Retour à la page de recherche".
     *
     * @param string $class Classes css du lien généré (SECONDARY_BUTTON par défaut).
     * @param string $title Texte du lien ('Retour à la page de recherche' par défaut).
     *
     * @return string
     */
    public function backToSearchButton(string $class = '', string $title = '');

    /**
     * Génère un bouton "Confirmer".
     *
     * @param string $class Classes css du lien généré (PRIMARY_BUTTON par défaut).
     * @param string $title Texte du lien ('Confirmer' par défaut).
     *
     * @return string
     */
    public function confirmButton(string $class = '', string $title = '');

    /**
     * Génère un bouton "Continuer".
     *
     * @param string $class Classes css du lien généré (PRIMARY_BUTTON par défaut).
     * @param string $title Texte du lien ('Continuer' par défaut).
     *
     * @return string
     */
    public function continueButton(string $class = '', string $title = '');

    /**
     * Génère un input hidden "silent=1".
     *
     * @param string $name Nom du champ généré.
     *
     * @return string
     */
    public function silentInput($name = 'silent');
}

<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch;

use Docalist\Tools\Tool;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Data\Database;
use Docalist\Data\Record;

/**
 * Interface de gestion des paramètres d'exécution d'un traitement par lot.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface BatchParameters extends Tool
{
    /**
     * Modifie les paramètres d'exécution.
     *
     * @param array $parameters
     */
    public function setParameters(array $parameters): void;

    /**
     * Retourne les paramètres d'exécution.
     *
     * @return array
     */
    public function getParameters(): array;

    /**
     * Teste si un paramètre d'exécution à été fourni.
     *
     * @param string $parameter Nom du paramètre à tester.
     *
     * @return bool True si le paramètre existe et n'est pas vide (empty).
     */
    public function hasParameter(string $parameter): bool;

    /**
     * Retourne un paramètre d'exécution.
     *
     * @param string    $parameter  Nom du paramètre à retourner.
     * @param mixed     $default    Valeur par défaut.
     *
     * @return mixed
     */
    public function getParameter(string $parameter, $default = null);
}

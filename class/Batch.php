<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch;

use Docalist\Tools\Tool;
use Docalist\Batch\BatchParameters;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Data\Database;
use Docalist\Data\Record;

/**
 * Interface d'un traitement par lot.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
interface Batch extends Tool, BatchParameters, BatchUtil
{
    /**
     * Crée une requête de recherche à partir des paramètres fournis.
     *
     * Cette méthode crée une requête à partir des paramètres fournis et ajoute éventuellement des filtres et
     * des agrégations supplémentaires (la classe BaseBatch par exemple, ajoute une agrégation de type TermsIn).
     *
     * @return SearchRequest|null La requête de recherche générée ou null si les paramètres fournis ne permettent
     * pas de créer une requête.
     */
    public function createSearchRequest(): ?SearchRequest;

    /**
     * Valide la requête sur laquelle portera le traitement par lot.
     *
     * Cette méthode permet à un outil d'examiner la réponse obtenue pour la requête initiale générée par
     * createSearchRequest() et d'adapter la requête finale pour qu'elle ne porte que sur des contenus que
     * l'outil peut gérer.
     *
     * La classe BaseBatch par exemple, regarde les types retournés par l'agrégation TermsIn ajoutée dans
     * createSearchRequest() et modifie la requête pour la restreindre aux bases Docalist.
     *
     * @param SearchRequest     $searchRequest     La requête à modifier (celle générée par createSearchRequest).
     * @param SearchResponse    $searchResponse    Les résultats obtenus.
     *
     * @return bool
     */
    public function validateRequest(SearchRequest $searchRequest, SearchResponse $searchResponse): bool;

    /**
     * Prépare l'exécution du traitement par lot.
     *
     * Cette méthode est appellée juste avant que le traitement ne soit lancé. Elle peut demander confirmation,
     * demander des paramètres supplémentaires, initialiser des propriétés, etc.
     *
     * Si elle retourne false, l'exécution s'arrête, si elle retourne true, la traitement par lot est lancé.
     *
     * @param SearchResponse $searchResponse La réponse contenenant les notices à traiter.
     *
     * @return bool
     */
    public function beforeProcess(SearchResponse $searchResponse): bool;

    /**
     * Exécute le traitement sur l'enregistrement docalist passé en paramètre.
     *
     * @param Record    $record     L'enregistrement docalist à traiter.
     * @param Database  $database   La base docalist d'où provient l'enregistrement.
     *
     * @return bool False pour interrompre le traitement, true pour continuer.
     */
    public function process(Record $record, Database $database): bool;

    /**
     * Finalise l'exécution du traitement par lot.
     *
     * Cette méthode est appellée une fois que le traitement par lot est terminé. Elle peut afficher
     * un message de synthèse, fermer les ressources utilisées, etc.
     *
     * @return bool
     */
    public function afterProcess(): void;
}

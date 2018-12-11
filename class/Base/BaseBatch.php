<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Base;

use Docalist\Batch\Batch;
use Docalist\Search\SearchUrl;
use Docalist\Data\RecordIterator;
use Docalist\Views;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Search\IndexManager;
use Docalist\Data\Plugin as DocalistData;
use Docalist\Search\Aggregation\Standard\TermsIn;
use Docalist\Data\Database;
use Docalist\Data\Record;
use Generator;
use InvalidArgumentException;
use Docalist\Search\Indexer;
use Docalist\Batch\BatchParameters;
use Docalist\Batch\Base\BatchParametersTrait;
use Docalist\Batch\Base\BatchUtilTrait;
use Docalist\Search\QueryDSL;

/**
 * Classe de base pour les traitements par lot.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class BaseBatch implements Batch
{
    use BatchParametersTrait, BatchUtilTrait;

    /**
     * Liste des bases docalist sur lesquelles on peut lancer le traitement par lot.
     *
     * @var Database[] Un tableau de la forme "post-type" => Database.
     */
    private $databases;

    /**
     * Le QueryDSL à utiliser pour créer des filtres dans les requêtes.
     *
     * @var QueryDSL
     */
    private $queryDsl;

    /**
     * Constructeur.
     *
     * @param Database[] $databases Liste des bases docalist sur lesquelles on peut lancer le traitement par lot,
     *                              sous la forme d'un tableau de la forme "post-type" => Database.
     */
    public function __construct(array $databases, QueryDSL $queryDsl)
    {
        $this->databases = $databases;
        $this->queryDsl = $queryDsl;
    }

    /**
     * Teste si le type de post passé en paramètre correspond à une base Docalist.
     *
     * @param string $postType
     *
     * @return bool
     */
    private function isDatabase(string $postType): bool
    {
        return isset($this->databases[$postType]);
    }

    /**
     * Retourne le QueryDSL à utiliser pour créer des filtres dans les requêtes.
     *
     * @return QueryDSL
     */
    protected function getQueryDsl(): QueryDSL
    {
        return $this->queryDsl;
    }

    /**
     * Retourne la base Docalist qui contient le Record passé en paramètre.
     *
     * @param Record $record
     *
     * @return Database|null Retourne null si le post-type du record n'est pas dans la liste des bases Docalist.
     */
    private function getDatabase(Record $record): ?Database
    {
        return $this->databases[$record->posttype->getPhpValue()] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return get_class($this);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getCategory(): string
    {
        return __('Traitements par lots sur les notices Docalist', 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'manage_options';
    }

    /**
     * {@inheritDoc}
     */
    final public function run(array $args = []): void
    {
        // Stocke les paramètres
        $this->setParameters($args);

        // Affiche la description de l'outil
        echo '<p class="description">', $this->getDescription(), '</p>';

        // Crée la requête initiale
        if (is_null($searchRequest = $this->createSearchRequest())) {
            $this->view('docalist-batch:Base/no-search-request');

            return;
        }

        // Exécute la requête initiale
        if (is_null($searchResponse = $searchRequest->execute())) {
            $this->view('docalist-batch:Base/search-request-error');

            return;
        };

        // Vérifie que la requête initiale donne des réponses
        if (0 === $searchResponse->getHitsCount()) {
            $this->view('docalist-batch:Base/no-search-results');

            return;
        }

        // Valide les résultats obtenus et ajoute des paramètres de la requête
        if (! $this->validateRequest($searchRequest, $searchResponse)) {
            $this->view('docalist-batch:Base/validate-failed');

            return;
        }

        // Exécute la requête modifiée
        if (is_null($searchResponse = $searchRequest->execute())) {
            $this->view('docalist-batch:Base/search-request-error');

            return;
        };

        // Vérifie que la requête modifiée donne des réponses
        if (0 === $searchResponse->getHitsCount()) {
            $this->view('docalist-batch:Base/no-search-results', ['modified' => true]);

            return;
        }

        // Prépare l'exécution du traitement par lot
        if (! $this->beforeProcess($searchResponse)) {
            return;
        }

        // Affiche un message quand docalist-search flushe son cache
        $reportFlush = function (int $count, int $size): void {
            printf('<p>Flush du cache docalist-search, %d notices</p>', $count);
        };
        add_action('docalist_search_before_flush', $reportFlush, 10, 2);

        // Lance le traitement par lot
        $this->processRecords($searchRequest);

        // Flush le cache docalist-search si besoin
        $indexManager = docalist('docalist-search-index-manager'); /** @var IndexManager $indexManager */
        $indexManager->flush();

        // Supprime l'action ajoutée précédemment
        remove_action('docalist_search_before_flush', $reportFlush, 10);

        // Permet aux classes descendantes d'afficher un message final
        $this->afterProcess();

        // Génère un bouton "back to search"
        $this->view('docalist-batch:Base/after-process');
    }

    /**
     * {@inheritDoc}
     */
    public function createSearchRequest(): ?SearchRequest
    {
        // Récupère l'url de recherche passée en paramètre et crée la searchUrl correspondante
        if (! $this->hasParameter('search-url')) {
            return null;
        }

        // Crée une SearchUrl
        $searchUrl = new SearchUrl($this->getParameter('search-url'));

        // Crée une requête de recherche à partir de cette url
        $searchRequest = $searchUrl->getSearchRequest();

        // Ajoute une aggrégation TermsIn pour pouvoir valider les types dans validateRequest()
        $in = new TermsIn(['size' => 1000]);
        $in->setName('in');
        $searchRequest->addAggregation($in);

        // Initialement, on n'a pas besoin des hits, juste de savoir combien on a de réponses
        $searchRequest->setSize(0);

        // Ok
        return $searchRequest;
    }

    /**
     * {@inheritDoc}
     */
    public function validateRequest(SearchRequest $searchRequest, SearchResponse $searchResponse): bool
    {
        $in = $searchResponse->getAggregation('in'); /** @var TermsIn $in */
        $types = [];
        $messages = [];
        foreach ($in->getBuckets() as $bucket) {
            $collection = $bucket->key;
            $count = $bucket->doc_count;
            $postType = $this->collectionToPostType($collection);
            if (empty($postType) || ! $this->isDatabase($postType)) {
                $messages[] = sprintf(
                    __('<b>%d réponse(s)</b> de type <b>« %s »</b>', 'docalist-batch'),
                    $count,
                    $in->getBucketLabel($bucket)
                );
                continue;
            }
            $types[] = $postType;
        }

        if ($messages && !$this->hasParameter('silent')) {
            printf(
                __(
                    '<p>Votre recherche initiale retournait <b>%d réponse(s)</b>,
                    des filtres ont été ajoutés pour éliminer :</p>',
                    'docalist-batch'
                ),
                $searchResponse->getHitsCount()
            );
            echo '<ul class="ul-square"><li>', implode(',</li><li>', $messages), '.</li></ul>';
        }

        if (empty($types)) {
            echo "<p><b>Il n'y a plus de notices docalist dans les résultats, impossible de continuer.</b></p>";

            return false;
        }

        //printf('<p>La requête modifiée portera sur les bases docalist suivantes : %s</p>', implode(', ', $databases));

        $searchRequest->setTypes($types);

        return true;
    }

    private function collectionToPostType(string $collection): string
    {
        $indexManager = docalist('docalist-search-index-manager'); /** @var IndexManager $indexManager */
        $collections = $indexManager->getCollections();
        if (! isset($collections[$collection])) {
            return '';
        }

        $indexer = $collections[$collection]; /** @var Indexer $indexer */
        return $indexer->getType();
    }

    /**
     * {@inheritDoc}
     */
    public function beforeProcess(SearchResponse $searchResponse): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Record $record, Database $database): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
    }

    public function processRecords(SearchRequest $searchRequest): void
    {
        $iterator = new RecordIterator($searchRequest);

        $iterator->rewind();
        $count = 0;
        while ($iterator->valid()) {
            $id = $iterator->key();
            try {
                $record = $iterator->current();
            } catch (InvalidArgumentException $e) {
                printf(
                    '<p>Warning : erreur lors du chargement de la notice %s ("%s").
                    l\'index docalist-search est peut-être désynchronisé.
                    Post ignoré</p>',
                    $id,
                    $e->getMessage()
                );
                $iterator->next();
                continue;
            }

            ++$count;
            if (0 === $count % 100) {
                printf('%d notices traitées...<br />', $count);
            }

            $database = $this->getDatabase($record);
            if (is_null($database)) {
                printf('Warning : Post %s is not a docalist record, ignore<br />', $record->getID());
                continue;
            }

            if (! $this->process($record, $database)) {
                echo '<p>process a retourné false, traitement interrompu</p>';
                return;
            }

            $iterator->next();
        }
    }

    /**
     * Exécute une vue et affiche le résultat
     *
     * @param string $view
     *
     * @param array $data
     */
    protected function view(string $view, array $data = []): void
    {
        $views = docalist('views'); /** @var Views $views */
        $data['this'] = $this;
        $views->display($view, $data);
    }
}

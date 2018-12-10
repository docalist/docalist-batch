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

/**
 * Classe de base pour les traitements par lot.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class BaseBatch implements Batch
{
    /**
     * Liste des bases docalist sur lesquelles on peut lancer le traitement par lot.
     *
     * @var Database[] Un tableau de la forme "post-type" => Database.
     */
    private $databases;

    /**
     * Constructeur.
     *
     * @param Database[] $databases Liste des bases docalist sur lesquelles on peut lancer le traitement par lot,
     *                              sous la forme d'un tableau de la forme "post-type" => Database.
     */
    public function __construct(array $databases)
    {
        $this->databases = $databases;
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
        echo '<p class="description">', $this->getDescription(), '</p>';

        // Crée la requête initiale
        if (is_null($searchRequest = $this->createSearchRequest($args))) {
            $this->view('docalist-batch:Base/no-search-request', ['this' => $this, 'args' => $args]);

            return;
        }

        // Exécute la requête initiale
        if (is_null($searchResponse = $searchRequest->execute())) {
            $this->view('docalist-batch:Base/search-request-error', ['this' => $this, 'args' => $args]);

            return;
        };

        // Vérifie que la requête initiale donne des réponses
        if (0 === $initialCount = $searchResponse->getHitsCount()) {
            $this->view('docalist-batch:Base/no-search-results', ['this' => $this, 'args' => $args]);

            return;
        }

        // Valide les résultats obtenus et ajoute des paramètres de la requête
        ob_start();
        $validate = $this->validateRequest($searchRequest, $searchResponse);
        $output = ob_get_clean();

        // Si validateRequest a généré des messages, on affiche d'abord le nombre initial de réponses
        if (! empty($output) && !isset($args['silent'])) {
            printf('<p>Votre recherche retourne <b>%d réponse(s)</b> :</p>', $initialCount);
            echo $output;
        }

        // Si validateRequest() a retourné false, terminé
        if (! $validate) {
            $this->view('docalist-batch:Base/validate-failed', ['this' => $this, 'args' => $args]);

            return;
        }

        // Exécute la requête modifiée
        if (is_null($searchResponse = $searchRequest->execute())) {
            $this->view('docalist-batch:Base/search-request-error', ['this' => $this, 'args' => $args]);

            return;
        };

        // Vérifie que la requête modifiée donne des réponses
        if (0 === $searchResponse->getHitsCount()) {
            $this->view('docalist-batch:Base/no-search-results', ['this' => $this, 'args' => $args]);

            return;
        }

        // Prépare l'exécution du traitement par lot
        if (! $this->beforeProcess($args, $searchResponse)) {
            return;
        }

        // Lance le traitement par lot
        $this->processRecords($searchRequest);

        // Finalise le traitement par lot
        $this->afterProcess($args);
    }

    /**
     * {@inheritDoc}
     */
    public function createSearchRequest(array $args): ?SearchRequest
    {
        // Récupère l'url de recherche passée en paramètre et crée la searchUrl correspondante
        if (empty($args['search-url'])) {
            return null;
        }

        // Crée une SearchUrl
        $searchUrl = new SearchUrl($args['search-url']);

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
                    'ajout d\'un filtre pour éliminer les <b>%d réponse(s)</b> de type <b>%s</b>',
                    $count,
                    $in->getBucketLabel($bucket)
                );
                continue;
            }
            $types[] = $postType;
        }

        if ($messages) {
            echo '<ul class="ul-square"><li>', implode(',</li><li>', $messages), '.</li></ul>';
        }

        if (empty($types)) {
            echo "<p>Il n'y a plus de notices docalist dans les résultats, impossible de continuer.</p>";

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
    public function beforeProcess(array $args, SearchResponse $searchResponse): bool
    {
        add_action('docalist_search_before_flush', function (int $count, int $size): void {
            printf('<p>Flush du cache docalist-search, %d notices</p>', $count);
        }, 10, 2);

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
    public function afterProcess(array $args): void
    {
        $indexManager = docalist('docalist-search-index-manager'); /** @var IndexManager $indexManager */
        $indexManager->flush();

        $this->view('docalist-batch:Base/after-process', ['this' => $this, 'args' => $args]);
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

        $views->display($view, $data);
    }
}

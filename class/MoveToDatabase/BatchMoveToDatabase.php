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

namespace Docalist\Batch\MoveToDatabase;

use Docalist\Batch\Base\BaseBatch;
use Docalist\Data\Record;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Search\Aggregation\Bucket\FilterAggregation;
use Docalist\Data\Database;
use Docalist\Data\Field\PostTypeField;
use Docalist\Forms\Container;
use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Search\Indexer\Field\CollectionIndexer;
use Docalist\Search\Indexer\Field\PostTypeIndexer;

/**
 * Transfère les notices vers une autre base Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchMoveToDatabase extends BaseBatch
{
    /**
     * La nouvelle bases des notices (post type).
     *
     * @var string
     */
    private $postType;

    /**
     * Nombre de notices modifiées.
     *
     * @var int
     */
    private $modified;

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Transférer vers une base', 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __(
            "Transfère les notices sélectionnées vers une autre base Docalist.",
            'docalist-batch'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'docalist_batch_move_to_database';
    }

    /**
     * {@inheritDoc}
     */
    public function createSearchRequest(): ?SearchRequest
    {
        // Récupère la requête créée par la classe parent
        if (is_null($searchRequest = parent::createSearchRequest())) {
            return null;
        }

        // Si l'utilisateur a déjà choisi la nouvelle base, ajoute une agrégation de type "Filter"
        if ($this->hasParameter('posttype')) {
            $postType = $this->getParameter('posttype');
            $collection = $this->postTypeToCollection($postType);
            if ($collection) {
                $dbCount = new FilterAggregation($this->getFilter($collection));
                $dbCount->setName('db-count');
                $searchRequest->addAggregation($dbCount);
            }

            $types = new TermsAggregation(PostTypeIndexer::CODE_FILTER, ['size' => 1000]);
            $types->setName('types');
            $searchRequest->addAggregation($types);
        }

        // Ok
        return $searchRequest;
    }

    private function postTypeToCollection(string $postType): string
    {
        $database = $this->getDatabase($postType);

        return is_null($database) ? '' : $database->getSettings()->name->getPhpValue();

        /*
         * La ligne ci-desssus doit rester synchro avec  DatabaseIndexer (nom de la collection = nom de la base)
         * Il faudrait que Database ait une méthode getCollection() mais ce n'est pas le cas pour le moment.
         */
    }

    /**
     * Retourne un filtre elasticsearch permettant de n'inclure que les réponses de la collection indiquée.
     *
     * @param string $collection Collection (champ "in").
     *
     * @return array La définition DSL du filtre elasticsearch.
     */
    private function getFilter(string $collection): array
    {
        return $this->getQueryDsl()->term(CollectionIndexer::FILTER, $collection);
    }

    /**
     * Retourne un filtre elasticsearch permettant d'exclure les notices provenant de la collection indiquée.
     *
     * @param string $collection Collection (champ "in").
     *
     * @return array La définition DSL du filtre elasticsearch.
     */
    private function getExcludeFilter(string $collection): array
    {
        $dsl = $this->getQueryDsl();
        return $dsl->bool([$dsl->mustNot($this->getFilter($collection))]);
    }

    /**
     * {@inheritDoc}
     */
    public function validateRequest(SearchRequest $searchRequest, SearchResponse $searchResponse): bool
    {
        // Laisse la classe parent valider la requête
        if (!parent::validateRequest($searchRequest, $searchResponse)) {
            return false;
        }

        // Si l'utilisateur n'a pas encore choisi la nouvelle base, terminé
        if (! $this->hasParameter('posttype')) {
            return true;
        }

        $postType = $this->getParameter('posttype');
        $database = $this->getDatabase($postType);

        // Ajoute des filtres pour exclure les notices qui ont un type qui n'existe pas dans la base de destination
        $types = $searchResponse->getAggregation('types'); /** @var TermsAggregation $types */
        $databaseTypes = $database->getSettings()->types;
        $messages = [];
        foreach ($types->getBuckets() as $bucket) {
            $type = $bucket->key;
            $count = $bucket->doc_count;
            if (! isset($databaseTypes[$type])) {
                $dsl = $this->getQueryDsl();
                $excludeFilter = $dsl->bool([$dsl->mustNot($dsl->term(PostTypeIndexer::CODE_FILTER, $type))]);
                $searchRequest->addFilter($excludeFilter);
                $messages[] = sprintf(__('<b>%d notices</b> de type <b>« %s »</b>', 'docalist-batch'), $count, $type);
            }
        }

        if ($messages && !$this->hasParameter('silent2')) {
            printf(
                __(
                    '<p>Des filtres ont été ajoutés pour éliminer les types de notices
                    qui n\'existent pas dans la base "%s" :</p>',
                    'docalist-batch'
                ),
                $database->getLabel()
            );
            echo '<ul class="ul-square"><li>', implode(',</li><li>', $messages), '.</li></ul>';
        }

        // Ajoute un filtre pour exclure les notices qui sont déjà dans la base des destination
        if ($searchResponse->hasAggregation('db-count')) {
            $dbCount = $searchResponse->getAggregation('db-count'); /** @var FilterAggregation $dbCount */
            $count = $dbCount->getResult('doc_count');
            if ($count) {
                $collection = $this->postTypeToCollection($postType);
                $searchRequest->addFilter($this->getExcludeFilter($collection));

                if (!$this->hasParameter('silent2')) {
                    printf(
                        __(
                            '<p>Un filtre a été ajouté pour éliminer %d notice(s) qui sont déjà dans la base "%s".</p>',
                            'docalist-batch'
                        ),
                        $count,
                        $database->getLabel()
                    );
                }
            }
        }

        // Ok
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeProcess(SearchResponse $searchResponse): bool
    {
        // Affiche le formulaire permettant de choisir la base des destination
        if (! $this->hasParameter('posttype')) {
            $this->view(
                'docalist-batch:MoveToDatabase/form',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Demande confirmation à l'utilisateur
        if (! $this->hasParameter('confirm')) {
            $this->view(
                'docalist-batch:MoveToDatabase/confirm',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Lance le traitement
        $this->postType = $this->getParameter('posttype');
        $this->modified = 0;
        $this->view(
            'docalist-batch:MoveToDatabase/before-process',
            ['count' => $searchResponse->getHitsCount()]
        );

        // Ok
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Record $record, Database $database): bool
    {
        // Change la base de destination
        $record->posttype->assign($this->postType);

        // Supprime l'éventuel numéro de réf existant
        unset($record->ref);

        // Supprime le slug (lié au numéro de ref)
        unset($record->slug);

        // Modifie la date de dernière modification
        $record->lastupdate->assign(current_time('mysql'));

        // Indique l'utilisateur qui a fait la modif
        update_post_meta($record->getID(), '_edit_last', get_current_user_id());

        // Sauvegarde la notice dans la base des destination
        $this->getDatabase($this->postType)->save($record);

        // Met à jour nos compteurs
        ++$this->modified;

        // Ok
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
        $this->view('docalist-batch:MoveToDatabase/after-process', ['count' => $this->modified]);
        parent::afterProcess();
    }

    /**
     * Génère le formulaire permettant de choisir le nouvel auteur.
     *
     * @return string
     */
    private function renderForm(): string
    {
        $postType = new PostTypeField();

        $item = $postType->getEditorForm(['editor' => 'select']);
        $item->setLabel(__('Base de destination :', 'docalist-batch'));
        $item->setDescription(
            __(
                'Choisissez dans la liste.',
                'docalist-batch'
            )
        );

        $form = new Container();
        $form->add($item);

        $form->bind($this->getParameters());

        return $form->render();
    }
}

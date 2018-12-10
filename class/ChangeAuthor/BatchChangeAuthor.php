<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\ChangeAuthor;

use Docalist\Batch\Base\BaseBatch;
use Docalist\Data\Record;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Data\Database;
use Docalist\Data\Field\PostAuthorField;
use Docalist\Forms\Container;
use Docalist\Search\Aggregation\Standard\TermsCreatedBy;
use Docalist\Search\QueryDSL;

/**
 * Change l'auteur WordPress des notices.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchChangeAuthor extends BaseBatch
{
    /**
     * L'ID du nouvel auteur WordPress.
     *
     * @var int
     */
    private $createdBy;

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
        return __("Changer l'auteur WordPress", 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __(
            "Permet de modifier l'auteur WordPress des notices sélectionnées.",
            'docalist-batch'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'docalist_batch_change_author';
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

        // Si l'utilisateur a déjà choisi le nouveau compte, ajoute une agrégation de type "TermsCreatedBy"
        if ($this->hasParameter('createdBy')) {
            $authors = new TermsCreatedBy([], ['multiselect' => false]);
            $authors->setName('authors');
            $searchRequest->addAggregation($authors);
        }

        // Ok
        return $searchRequest;
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

        // Si l'utilisateur a déjà choisi le nouveau compte, ajoute un filtre sur createdBy
        if ($this->hasParameter('createdBy')) {
            $user = get_user_by('ID', $this->getParameter('createdBy'));
            $login = $user->user_login;
            $authors = $searchResponse->getAggregation('authors'); /** @var TermsCreatedBy $authors */
            foreach ($authors->getBuckets() as $bucket) {
                $author = $bucket->key;
                if ($author !== $login) {
                    continue;
                }
                $count = $bucket->doc_count;
                $dsl = docalist('elasticsearch-query-dsl'); /** @var QueryDSL $dsl */
                $filter = $dsl->bool([$dsl->mustNot($dsl->term('createdby', $login))]);
                $searchRequest->addFilter($filter);

                if (!$this->hasParameter('silent2')) {
                    printf(
                        __(
                            '<p>Un filtre a été ajouté pour éliminer %d notice(s) déjà attribuée(s) à %s.</p>',
                            'docalist-batch'
                        ),
                        $count,
                        $user->display_name
                    );
                }
                break;
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
        // Affiche le formulaire permettant de choisir le nouvel auteur
        if (! $this->hasParameter('createdBy')) {
            $this->view(
                'docalist-batch:ChangeAuthor/form',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Demande confirmation à l'utilisateur
        if (! $this->hasParameter('confirm')) {
            $this->view(
                'docalist-batch:ChangeAuthor/confirm',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Lance le traitement
        $this->createdBy = (int) $this->getParameter('createdBy');
        $this->modified = 0;
        $this->view(
            'docalist-batch:ChangeAuthor/before-process',
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
        $record->createdBy->assign($this->createdBy);
        $database->save($record);
        ++$this->modified;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
        $this->view('docalist-batch:ChangeAuthor/after-process', ['count' => $this->modified]);
        parent::afterProcess();
    }

    /**
     * Génère le formulaire permettant de choisir le nouvel auteur.
     *
     * @return string
     */
    private function renderForm(): string
    {
        $createdBy = new PostAuthorField();

        $item = $createdBy->getEditorForm(['editor' => 'entry-picker']);
        $item->setLabel(__('Nouvel auteur :', 'docalist-batch'));
        $item->setDescription(
            __(
                'Choisissez le compte WordPress auquel seront attribuées les enregistrements.',
                'docalist-batch'
            )
        );

        $form = new Container();
        $form->add($item);

        $form->bind($this->getParameters());

        return $form->render();
    }
}

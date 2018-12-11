<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\ChangeStatus;

use Docalist\Batch\Base\BaseBatch;
use Docalist\Data\Database;
use Docalist\Data\Record;
use Docalist\Data\Field\PostStatusField;
use Docalist\Forms\Container;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Search\Aggregation\Bucket\FilterAggregation;

/**
 * Change le statut des notices.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchChangeStatus extends BaseBatch
{
    /**
     * Le nouveau statut des notices.
     *
     * @var int
     */
    private $status;

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
        return __('Changer le statut', 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __(
            'Permet de modifier le statut de publication des notices sélectionnées.',
            'docalist-batch'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'docalist_batch_change_status';
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
        if ($this->hasParameter('status')) {
            $statusCount = new FilterAggregation($this->getFilter($this->getParameter('status')));
            $statusCount->setName('status-count');
            $searchRequest->addAggregation($statusCount);
        }

        // Ok
        return $searchRequest;
    }

    private function getFilter(string $status): array
    {
        return $this->getQueryDsl()->term('status', $status);
    }

    private function getExcludeFilter(string $status): array
    {
        $dsl = $this->getQueryDsl();
        return $dsl->bool([$dsl->mustNot($dsl->term('status', $status))]);
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

        // Si l'utilisateur a déjà choisi le nouveau statut, ajoute un filtre sur status
        if ($this->hasParameter('status')) {
            $statusCount = $searchResponse->getAggregation('status-count'); /** @var FilterAggregation $statusCount */
            $count = $statusCount->getResult('doc_count');
            if ($count) {
                $status = $this->getParameter('status');
                $searchRequest->addFilter($this->getExcludeFilter($status));

                if (!$this->hasParameter('silent2')) {
                    printf(
                        __(
                            '<p>Un filtre a été ajouté pour éliminer %d notice(s) qui sont déjà en statut %s.</p>',
                            'docalist-batch'
                        ),
                        $count,
                        $status
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
        // Affiche le formulaire permettant de choisir le nouveau statut
        if (! $this->hasParameter('status')) {
            $this->view(
                'docalist-batch:ChangeStatus/form',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Demande confirmation à l'utilisateur
        if (! $this->hasParameter('confirm')) {
            $this->view(
                'docalist-batch:ChangeStatus/confirm',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm()]
            );
            return false;
        }

        // Lance le traitement
        $this->status = $this->getParameter('status');
        $this->modified = 0;
        $this->view(
            'docalist-batch:ChangeStatus/before-process',
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
        $record->status->assign($this->status);
        // $database->save($record);
        ++$this->modified;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
        $this->view('docalist-batch:ChangeStatus/after-process', ['count' => $this->modified]);
        parent::afterProcess();
    }

    /**
     * Génère le formulaire permettant de choisir le nouvel auteur.
     *
     * @return string
     */
    private function renderForm(): string
    {
        $status = new PostStatusField();

        $item = $status->getEditorForm(['editor' => 'select']);
        $item->setLabel(__('Nouveau statut :', 'docalist-batch'));
        $item->setDescription(
            __(
                'Choisissez le nouveau statut dans la liste.',
                'docalist-batch'
            )
        );

        $form = new Container();
        $form->add($item);

        $form->bind($this->getParameters() + ['status' => 'publish']);

        return $form->render();
    }
}

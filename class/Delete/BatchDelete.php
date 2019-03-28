<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Delete;

use Docalist\Batch\Base\BaseBatch;
use Docalist\Data\Record;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Data\Database;

/**
 * Suppression en série.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchDelete extends BaseBatch
{
    /**
     * Nombre de notices supprimées.
     *
     * @var int
     */
    private $deleted;

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Supprimer tout', 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __(
            'Supprime toutes les notices sélectionnées. La suppression est définitive, les notices
            <i>ne sont pas</i> transférées dans la corbeille.',
            'docalist-batch'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'docalist_batch_delete';
    }

    /**
     * {@inheritDoc}
     */
    public function beforeProcess(SearchResponse $searchResponse): bool
    {
        // Demande confirmation à l'utilisateur
        if (! $this->hasParameter('confirm')) {
            $this->view('docalist-batch:Delete/confirm', ['count' => $searchResponse->getHitsCount()]);
            return false;
        }

        // Lance le traitement
        $this->deleted = 0;
        $this->view('docalist-batch:Delete/before-process', ['count' => $searchResponse->getHitsCount()]);

        // Ok
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function process(Record $record, Database $database): bool
    {
        $database->delete($record->getID());
        ++$this->deleted;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
        $this->view('docalist-batch:Delete/after-process', ['count' => $this->deleted]);
    }
}

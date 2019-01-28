<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\Base\BaseBatch;
use Docalist\Batch\SearchReplace\Operation;
use Docalist\Data\Database;
use Docalist\Data\Record;
use Docalist\Forms\Container;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Search\Aggregation\Standard\TermsIn;

/**
 * Chercher / remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchSearchReplace extends BaseBatch
{
    /**
     * La liste des opérations à effectuer.
     *
     * @var Operation[]
     */
    private $operations;

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
        return __('Chercher-remplacer', 'docalist-batch');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __(
            'Permet de faire un chercher/remplacer sur les notices sélectionnées.',
            'docalist-batch'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getCapability(): string
    {
        return 'docalist_batch_search_replace';
    }

    /**
     * {@inheritDoc}
     */
    public function createSearchRequest(): ?SearchRequest
    {
        // Récupère la requête créée par la classe parent
        $searchRequest = parent::createSearchRequest();
        if (is_null($searchRequest)) {
            return null;
        }

        // Ajoute une agrégation imbriquée type de notice » collection
        $types = new TermsAggregation('type', ['size' => 1000]);
        $types->setName('types');
        $collections = new TermsIn(['size' => 1000]);
        $collections->setName('collections');
        $types->addAggregation($collections);
        $searchRequest->addAggregation($types);

        // Ok
        return $searchRequest;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeProcess(SearchResponse $searchResponse): bool
    {
        // Récupère l'agrégation sur les types de notices créée dans createRequest
        $types = $searchResponse->getAggregation('types'); /** @var TermsAggregation $types */

        // Génère la liste des champs sur lesquels on peut lancer un chercher/remplacer
        $fieldsBuilder = $this->getFieldsBuilder($types);

        // Valide la liste des opérations et génère la clé "explain" de chaque opération
        $valid = $this->validateOperations($this->getParameter('operations', []), $fieldsBuilder);

        // Affiche le formulaire permettant de choisir les opérations à effectuer
        if (!$valid) {
            $this->view(
                'docalist-batch:SearchReplace/form',
                ['count' => $searchResponse->getHitsCount(), 'form' => $this->renderForm($fieldsBuilder)]
            );
            return false;
        }

        // Demande confirmation à l'utilisateur
        if (! $this->hasParameter('confirm')) {
            $this->view(
                'docalist-batch:SearchReplace/confirm',
                [
                    'count' => $searchResponse->getHitsCount(),
                    'form' => $this->renderForm($fieldsBuilder),
                    'operations' => $this->operations
                ]
            );
            return false;
        }

        // Lance le traitement
        $this->modified = 0;
        $this->view(
            'docalist-batch:SearchReplace/before-process',
            ['count' => $searchResponse->getHitsCount()]
        );

        // Ok
        return true;
    }

    /**
     * Valide la liste des opérations passée en paramètre et ajoute une clause "explain" à chaque opération.
     *
     * @param array         $operations
     * @param FieldsBuilder $fieldsBuilder
     *
     * @return bool
     */
    private function validateOperations(array $operations, FieldsBuilder $fieldsBuilder): bool
    {
        $this->operations = [];
        foreach ($operations as $operation) {
            $name = $operation['field'];
            $search = $operation['search'];
            $replace = $operation['replace'];

            // Ignore les lignes vides
            if (empty($name) && empty($search) && empty($replace)) {
                continue;
            }

            // Vérifie que le champ indiqué existe
            $field = $fieldsBuilder;
            foreach (explode('.', $name) as $part) {
                if (! $field->hasField($part)) {
                    die("Le champ $name n'existe pas");
                }
                $field = $field->getField($part);
            }

            // Stocke l'opération
            $this->operations[] = $field->getOperation($search, $replace);
        }

        return !empty($this->operations);
    }

    /**
     * {@inheritDoc}
     */
    public function process(Record $record, Database $database): bool
    {
        // Indique s'il faut enregistrer le record
        $save = false;

        // Exécute tous les traitements demandés
        foreach ($this->operations as $operation) {
            $changed = $operation->process($record);
            $save = $save || $changed;
        }

        // Enregistre le record s'il a été modifié
        if ($save) {
            $record->filterEmpty(false);
            $database->save($record);
            ++$this->modified;
        }

        // Ok
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function afterProcess(): void
    {
        $this->view('docalist-batch:SearchReplace/after-process', ['count' => $this->modified]);
        parent::afterProcess();
    }

    /**
     * Crée un FieldBuilder à partir de l'agrégation "types de notices" passée en paramètre.
     *
     * @param TermsAggregation $types
     *
     * @return FieldsBuilder
     */
    private function getFieldsBuilder(TermsAggregation $types): FieldsBuilder
    {
        $builder = new FieldsBuilder();
        $typed = ['topic', 'number', 'date', 'content', 'extent', 'relation'];
        $tables = [];
        foreach ($types->getBuckets() as $bucket) {
            $type = $bucket->key;
            $class = Database::getClassForType($type);
            $record = new $class(); /** @var Record $record */
            $builder->addFieldsFromRecord($record);

            foreach ($bucket->collections->buckets as $bucket) {
                $collection = $bucket->key;
                $postType = $this->collectionToPostType($collection);
                $database = $this->getDatabase($postType);
                if (is_null($database)) {
                    echo "base $postType non trouvée<br />";
                    continue;
                }
                $record = $database->createReference($type);
                $schema = $record->getSchema();
                foreach ($typed as $field) {
                    if (!$schema->hasField($field)) {
                        continue;
                    }
                    $table = $schema->getField($field)->getField('type')->table();
                    list(, $table) = explode(':', $table);
                    $tables[$field][$table] = $table;
                }
            }
        }

        foreach ($tables as $field => $fieldTables) {
            $types = [];
            foreach ($fieldTables as $table) {
                $table = docalist('table-manager')->get($table); /** @var TableInterface $table */
                foreach ($table->search('code') as $code) {
                    $types[$code] = $code;
                }
            }
            $builder->addTypedFields($field, $types);
        }

        return $builder;
    }

    /**
     * Génère le formulaire permettant de choisir le nouvel auteur.
     *
     * @return string
     */
    private function renderForm(FieldsBuilder $fieldsBuilder): string
    {
        $form = new Container();

        $operations = $form->table('operations')
            ->setRepeatable(true)
            ->setAttribute('required')
            ->setLabel(__('Liste des opérations', 'docalist-batch'));

        $operations
            ->select('field')
            ->setOptions($fieldsBuilder->getFieldsAsSelectOptions())
            ->setFirstOption(__('Choisissez un champ', 'docalist-batch'))
            ->setAttribute('required')
            ->setLabel(__('Champ', 'docalist-batch'))
            ->setDescription(__('Champ sur lequel porte l\'opération', 'docalist-batch'));

        $operations
            ->input('search')
            ->setLabel(__('Rechercher', 'docalist-batch'))
            // ->setDescription('Texte ou valeur recherchée')
            ->setAttribute('placeholder', 'Texte ou valeur à supprimer')
            ->addClass('large-text');

        $operations
            ->input('replace')
            ->setLabel(__('Remplacer par', 'docalist-batch'))
            // ->setDescription(__('Texte ou valeur à injecter', 'docalist-batch'))
            ->setAttribute('placeholder', __('Texte ou valeur à injecter', 'docalist-batch'))
            ->addClass('large-text');

        $form->bind($this->getParameters());

        return $form->render();
    }
}

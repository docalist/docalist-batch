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
use Docalist\Data\Record;
use Docalist\Search\SearchRequest;
use Docalist\Search\SearchResponse;
use Docalist\Search\Aggregation\Bucket\TermsAggregation;
use Docalist\Data\Database;
use Docalist\Forms\Container;
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
     * @var array
     */
    private $operations;

    /**
     * La liste des callbacks à exécuter.
     *
     * @var callable[]
     */
    private $callbacks;

    /**
     * Nombre de notices modifiées.
     *
     * @var int
     */
    private $modified;

    public function ZZZsetParameters(array $parameters): void
    {
        if (! isset($parameters['operations'])) {
            $operations = [];
            $fields = [
                'title',            // texte
                'edition',          // textes
                'posttype',         // value
                'media',            // values
                'context.title',    // single.subtext
                'corporation.name', // repeat.subtext
                'author.role',      // repeat.subvalue
                'topic.value',      // repeat.subvalues
            ];
            foreach ($fields as $field) {
                $operations[] = ['field' => $field, 'search' => 'se&amp;"arch', 'replace' => '<rep>'];
                $operations[] = ['field' => $field, 'search' => 'to be removed', 'replace' => ''];
                $operations[] = ['field' => $field, 'search' => '', 'replace' => 'inject'];
                $operations[] = ['field' => $field, 'search' => '', 'replace' => ''];
                $operations[] = ['field' => '', 'search' => '', 'replace' => ''];
            }
            $parameters['operations'] = $operations;
        }

        parent::setParameters($parameters);
    }

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
        $this->callbacks = [];

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

            // Explique ce que va faire l'opération
            $operation['explain'] = $this->explainOperation($field, $search, $replace);

            // Stocke l'opération et le callback correspondant
            $this->operations[] = $operation;
            $this->callbacks[] = $field->getOperation($search, $replace);
        }

        return !empty($this->operations);
    }

    /**
     * Explique ce que va faire une opération.
     *
     * @param Field     $field
     * @param string    $search
     * @param string    $replace
     *
     * @return string
     */
    private function explainOperation(Field $field, string $search, string $replace)
    {
        // Détermine le message de base
        if ($search !== '') {
            if ($replace !== '') {
                $explain = __('Remplacer {search} par {replace} dans {field}.', 'docalist-batch');
            } else {
                $explain = __('Supprimer {search} dans {field}.', 'docalist-batch');
            }
        } else {
            if ($replace !== '') {
                $explain = __('Injecter {replace} dans {field}.', 'docalist-batch');
            } else {
                $explain = __('Vider {field}.', 'docalist-batch');
            }
        }

        // Nom du champ
        if ($field->hasParent()) {
            $name = sprintf(
                $field->isRepeatable()
                ? __('le sous-champ répétable %s', 'docalist-batch')
                : __('le sous-champ %s', 'docalist-batch'),
                sprintf('<var>%s</var>', $field->getName())
            );

            $parent = $field->getParent();
            $name .= sprintf(
                $parent->isRepeatable()
                ? __(' du champ répétable %s', 'docalist-batch')
                : __(' du champ %s', 'docalist-batch'),
                sprintf('<var>%s</var>', $parent->getName())
            );
        } else {
            $name = sprintf(
                $field->isRepeatable()
                ? __('le champ répétable %s', 'docalist-batch')
                : __('le champ %s', 'docalist-batch'),
                sprintf('<var>%s</var>', $field->getName())
            );
        }

        // Texte ou valeur recherchée
        $search = sprintf(
            $field->isText()
            ? __('la chaine %s', 'docalist-batch')
            : __('la valeur « %s »', 'docalist-batch'),
            sprintf('<del>%s</del>', htmlspecialchars($search))
        );

        // Texte ou valeur de remplacement
        $replace = sprintf(
            $field->isText()
            ? __('la chaine %s', 'docalist-batch')
            : __('la valeur « %s »', 'docalist-batch'),
            sprintf('<ins>%s</ins>', htmlspecialchars($replace))
        );

        // Ok
        return  strtr($explain, ['{field}' => $name, '{search}' => $search, '{replace}' => $replace]);
    }

    /**
     * {@inheritDoc}
     */
    public function process(Record $record, Database $database): bool
    {
        // Indique s'il faut enregistrer le record
        $save = false;

        // Exécute tous les traitements demandés
        foreach ($this->callbacks as $callback) {
            $changed = $callback($record);
            $save = $save || $changed;
        }

        // Enregistre le record s'il a été modifié
        if ($save) {
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
/*
    private function getFieldsByCollection(TermsIn $typesByCollection): array
    {
        $fields = [];
        foreach ($typesByCollection->getBuckets() as $bucket) {
            $collection = $bucket->key;
            $postType = $this->collectionToPostType($collection);
            echo "Collection = $collection, post-type=$postType<br />";
            $database = $this->getDatabase($postType);
            foreach ($bucket->types->buckets as $bucket) {
                $type = $bucket->key;
                echo "Récupère les champs du type $type<br />";
                $record = $database->createReference($type);
                $this->addFields($fields, $record->getSchema());
            }
        }

//         echo '<pre>';
//         var_export($fields);
//         echo '</pre>';
//         die();
        return $fields;
    }
*/
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
        foreach ($types->getBuckets() as $bucket) {
            $type = $bucket->key;
            $class = Database::getClassForType($type);
            $record = new $class(); /** @var Record $record */
            $builder->addFieldsFromRecord($record);
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
            ->setLabel(__('Liste des opérations', 'docalist-batch'));

        $operations
            ->select('field')
            ->setOptions($fieldsBuilder->getFieldsAsSelectOptions())
            ->setLabel(__('Champ', 'docalist-batch'))
            ->setDescription(__('Champ sur lequel porte l\'opération', 'docalist-batch'));

        $operations
            ->input('search')
            ->setLabel(__('Rechercher', 'docalist-batch'))
            ->setDescription('Texte ou valeur recherchée');

        $operations
            ->input('replace')
            ->setLabel(__('Remplacer par', 'docalist-batch'))
            ->setDescription(__('Texte ou valeur de à injecter', 'docalist-batch'));

        $form->bind($this->getParameters());

        return $form->render();
    }
}

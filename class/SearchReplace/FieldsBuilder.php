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

use Docalist\Batch\SearchReplace\Fields;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Schema\Schema;
use Docalist\Data\Record;
use Docalist\Type\Composite;
use Docalist\Type\ListEntry;
use Docalist\Type\DateTime;
use Docalist\Type\Text;

/**
 * Permet de générer la liste des champs sur lesquels on peut lancer un chercher / remplacer à partir
 * d'un schéma ou d'un type Docalist.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class FieldsBuilder extends Fields
{
    /**
     * Ajoute les champs d'un schéma dans la liste.
     *
     * @param Schema $schema
     */
    public function addFieldsFromSchema(Schema $schema): void
    {
        $this->addFields($this, $schema);
    }

    /**
     * Ajoute les champs d'un Record dans la liste.
     *
     * @param Record $record
     */
    public function addFieldsFromRecord(Record $record): void
    {
        $this->addFieldsFromSchema($record->getSchema());
    }

    /**
     * Ajoute les champs d'un type Docalist dans la liste.
     *
     * @param string $type Le nom de code d'un type docalist
     */
//     public function addFieldsFromType(string $type)
//     {
//         $class = Database::getClassForType($type);
//         $record = new $class(); /** @var Record $record */

//         $this->addFields($this->fields, $record->getSchema());
//     }

    /**
     * Ajoute les champs du schéma dans la liste de champs passée en paramètre.
     *
     * @param Fields $fields La liste de champs à modifier.
     * @param Schema $schema Le schéma contenant les champs à ajouter.
     */
    private function addFields(Fields $fields, Schema $schema, Field $parent = null): void
    {
        foreach ($schema->getFields() as $schema) { /** @var Schema $schema */
            $name = $schema->name();
            if ($fields->hasField($name)) {
                continue;
            }

            // Crée un champ chercher/remplacer
            $field = new Field($name, $this->getType($schema->type()), ! empty($schema->collection()));

            // Ajoute le champ dans la liste
            $fields->addField($field);

            // Ajoute les sous-champs si le champ est un objet
            if ($schema->hasFields()) {
                $this->addFields($field, $schema, $field);
            }
        }
    }

    /**
     * Détermine le type d'un champ en fonction de la classe passée en paramètre.
     *
     * @param string $class Nom complet d'une classe représentant un type docalist (Composite, Text, etc.)
     *
     * @return int Une des constantes Field::TYPE_*.
     */
    private function getType(string $class): int
    {
        if (is_a($class, Composite::class, true)) {
            return Field::TYPE_OBJECT;
        }

        if (is_a($class, ListEntry::class, true)) {
            return Field::TYPE_VALUE;
        }

        if (is_a($class, DateTime::class, true)) {
            return Field::TYPE_VALUE;
        }

        if (is_a($class, Text::class, true)) {
            return Field::TYPE_TEXT;
        }

        return Field::TYPE_VALUE;
    }

    /**
     * Initialise la liste des champs à partir de l'agrégation passée en paramètre.
     *
     * @param TermsAggregation $types
     */
//     private function initFields(TermsAggregation $types): void
//     {
//         $this->fields = new Fields();
//         foreach ($types->getBuckets() as $bucket) {
//             $type = $bucket->key;
//             $class = Database::getClassForType($type);
//             $record = new $class(); /** @var Record $record */
//             $this->addFields($this->fields, $record->getSchema());
//         }
//     }

    /**
     *
     *
     * @param Database[] $databases Liste des bases Docalist sur lesquelles on peut lancer le traitement par lot,
     *                              sous la forme d'un tableau de la forme "post-type" => Database.
     */
//     private function addTopics(array $databases, TermsAggregation $types): void
//     {
//         echo "recherche des champs topics<br />";
//         //$fields = [];
//         foreach ($types->getBuckets() as $bucket) {
//             $type = $bucket->key;
//             echo "type=$type<br />";
//             foreach ($bucket->collections->buckets as $bucket) {
//                 $collection = $bucket->key;
//                 echo "collection=$collection<br />";
//                 $postType = $this->collectionToPostType($collection);
//                 echo "postType=$postType<br />";
//                 if (! isset($databases[$postType])) {
//                     echo "base non trouvée<br />";
//                     continue;
//                 }
//                 $database = $databases[$postType];
//                 echo "Crée une réf de type $type dans la base $postType<br />";
//                 $record = $database->createReference($type);
//                 $schema = $record->getSchema();
//                 if (!$schema->hasField('topic')) {
//                     echo "pas de champ topic<br />";
//                     continue;
//                 }
//                 $table = $schema->getField('topic')->getField('type')->table();
//                 list(, $table) = explode(':', $table);

//                 echo "table=$table<br />";
//                 $table = docalist('table-manager')->get($table); /** @var TableInterface $table */
//                 foreach ($table->search('code') as $code) {
//                     echo "Ajouter le champ topic.$code<br />";
//                 }
//             }
//         }

//         //return $fields;

//     }

//     private function collectionToPostType(string $collection): string
//     {
//         $indexManager = docalist('docalist-search-index-manager'); /** @var IndexManager $indexManager */
//         $collections = $indexManager->getCollections();
//         if (! isset($collections[$collection])) {
//             return '';
//         }

//         $indexer = $collections[$collection]; /** @var Indexer $indexer */
//         return $indexer->getType();
//     }
}

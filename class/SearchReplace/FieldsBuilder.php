<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\SearchReplace\Fields;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Batch\SearchReplace\TypedField;
use Docalist\Schema\Schema;
use Docalist\Data\Record;
use Docalist\Type\Composite;
use Docalist\Type\ListEntry;
use Docalist\Type\DateTime;
use Docalist\Type\Text;
use InvalidArgumentException;

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
     * Crée des sous champ-typés pour le champ passé en paramètre.
     *
     * @param string    $field Nom du champ de base (par exemple "topic").
     * @param array     $types Liste des sous-champs à créer (par exemple ["prisme", "geo", "free"]).
     *
     * @throws InvalidArgumentException Si le champ indiqué n'exixte pas ou ne peut pas être un TypedField.
     */
    public function addTypedFields(string $field, array $types): void
    {
        // Récupère le champ parent (par exemple "topic") et vérifie qu'il peut être utilisé comme TypedField
        $base = $this->getField($field);        // champ parent
        if (! $base->isRepeatable()) {
            throw new InvalidArgumentException('Le champ parent doit être répétable');
        }
        if (! $base->isObject()) {
            throw new InvalidArgumentException('Le champ parent doit être de type objet');
        }
        if (!$base->hasField('type')) {
            throw new InvalidArgumentException('Le champ parent doit avoir un sous-champ "type"');
        }

        // Récupère le sous-champ "value" sur lequel portera réellement l'opération
        $valueField = $base->getField('value'); // le sous-champ "value" doit exister, exception sinon

        // Crée le nouveau champ parent "topic/type" s'il n'existe pas encore
        $name = $base->getName() . '/type';
        if (! $this->hasField($name)) {
            $this->addField(new Field($name, Field::TYPE_OBJECT, true));
        }
        $parent = $this->getField($name);

        // Crée un sous champ pour chacun des types fournis en paramètre
        foreach ($types as $type) {
            $field = new TypedField($type, $valueField);
            $parent->addField($field);
        }
    }
}

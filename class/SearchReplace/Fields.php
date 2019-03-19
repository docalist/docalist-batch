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

use Docalist\Batch\SearchReplace\Field;
use InvalidArgumentException;

/**
 * Gère la liste des champs sur lesquels on peut lancer un chercher / remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Fields
{
    /**
     * La liste des champs.
     *
     * @var Field[] Un tableau de champs indexés par nom.
     */
    private $fields = [];

    /**
     * Ajoute un champ.
     *
     * @param Field $field Champ à ajouter.
     *
     * @throws InvalidArgumentException Si un champ avec le même nom existe déjà.
     */
    public function addField(Field $field): void
    {
        $name = $field->getName();
        if ($this->hasField($name)) {
            throw new InvalidArgumentException(sprintf('Duplicate field "%s"', $name));
        }
        $this->fields[$name] = $field;
    }

    /**
     * Teste si le champ indiqué existe.
     *
     * @param string $name Nom du champ.
     *
     * @return bool
     */
    public function hasField(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /**
     * Retourne un champ.
     *
     * @param string $name Nom du champ.
     *
     * @throws InvalidArgumentException Si le champ indiqué n'existe pas.
     *
     * @return Field
     */
    public function getField(string $name): Field
    {
        if (!$this->hasField($name)) {
            throw new InvalidArgumentException(sprintf('Field not found "%s"', $name));
        }

        return $this->fields[$name];
    }

    /**
     * Indique si la liste des champs est vide.
     *
     * @return bool
     */
    public function hasFields(): bool
    {
        return !empty($this->fields);
    }

    /**
     * Retourne la liste des champs.
     *
     * @return Field[]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Retourne la liste des champs sous la forme d'un tableau d'options utilisable dans un Select.
     *
     * @return array Un tableau contenant les options et groupes d'options du select :
     * - Pour les champs simples, la clé du tableau contient la clé du champ et la valeur associée contient
     *   le libellé du champ (tag <option>).
     * - Pour les champs "object", la clé du tableau contient le libellé du champ et la valeur associée est un
     *   tableau d'options simples (tag <optgroup>).
     * - Les clés des tableaux retournés correspondent aux clés (pas au nom) des champs.
     * - Les tableaux sont triés par clés croissantes.
     */
    public function getFieldsAsSelectOptions(): array
    {
        $fields = $this->fields;
        ksort($fields);

        $options = [];
        foreach ($fields as $field) {
            /*
             * Pour le moment, le chercher/remplacer ne peut pas gérer plus de deux niveaux de hiérarchies (champ
             * et sous-champ) car on génère les process avec un callable pour le sous-champ qu'on applique ensuite
             * au champ parent (il n'y a pas de boucle "while has parent").
             * Ca pose plusieurs problèmes pour le champ address qui a trois niveaux de hiérarchie
             * (address->value->country) :
             * - on se retrouve avec un select qui a des groupes d'options imbriqués et ça génère une exception
             * - de toute façon le chercher/remplacer ne peut pas fonctionner
             * Modifier docalist-batch pour que ça gère plus de deux niveaux sera compliqué : il faut revoir
             * complètement le code et gérer tous els cas (injection, suppression, etc.)
             * Une autre solution serait de ne pas avoir plus de deux niveaux (ça pose aussi problème dans les
             * formulaires, etc.) et de transformer le champ address pour enlever le niveau "value".
             * Mais ça aussi c'est compliqué car il faut ré-écrire le widget "saisie d'adresse", gérer la
             * compatibilité ascendantes, mouliner les données, etc.
             * Donc pour le moment :
             * - le chercher/remplacer sur le champ address ne fonctionne pas (àa dit systématiquement "zéro notices
             *   modifiées)
             * - on modifie le select pour n'afficher que les sous-champs (plus de <optgroup>) pour éviter que ça
             *   bloque tout.
             */
            // version du code qui génère des optgroups
            // if ($field->hasFields()) {
            //     $options[$field->getLabel()] = $field->getFieldsAsSelectOptions();
            //     continue;
            // }

            // Génère uniquement une liste à plat avec les sous-champs
            if ($field->hasFields()) {
                $options += $field->getFieldsAsSelectOptions();
                continue;
            }

            $options[$field->getKey()] = $field->getLabel();
        }

        return $options;
    }
}

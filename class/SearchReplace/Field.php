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

namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\SearchReplace\Fields;
use Docalist\Batch\SearchReplace\Operation;
use Docalist\Batch\SearchReplace\Operation\ValueOperation;
use Docalist\Batch\SearchReplace\Operation\TextOperation\ClearText;
use Docalist\Batch\SearchReplace\Operation\TextOperation\InjectText;
use Docalist\Batch\SearchReplace\Operation\TextOperation\RemoveText;
use Docalist\Batch\SearchReplace\Operation\TextOperation\ReplaceText;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\ClearValue;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\InjectValue;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\ReplaceValue;
use Docalist\Batch\SearchReplace\Operation\ValueOperation\RemoveValue;
use InvalidArgumentException;

/**
 * Un champ sur lequel on peut lancer un chercher/remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
class Field extends Fields
{
    /**
     * Le type d'un champ composite.
     *
     * On ne peut pas lancer un chercher/remplacer sur un champ object, ils servent uniquement de champ parent
     * pour d'autres champs.
     *
     * @var integer
     */
    const TYPE_OBJECT = 1;

    /**
     * Le type d'un champ de type texte.
     *
     * Le chercher/remplacer teste si le texte contient le texte recherché.
     *
     * @var integer
     */
    const TYPE_TEXT = 2;

    /**
     * Le type d'un champ de type "valeurs".
     *
     * Le chercher/remplacer teste si la valeur du champ correspond exactement (en entier) au texte recherché.
     *
     * @var integer
     */
    const TYPE_VALUE = 3;

    /**
     * Le nom du champ.
     *
     * @var string
     */
    private $name;

    /**
     * Le type du champ.
     *
     * @var int Une des constantes TYPE_*.
     */
    private $type;

    /**
     * Champ répétable ou non.
     *
     * @var bool
     */
    private $repeatable;

    /**
     * Le champ parent (pour un sous-champ)
     *
     * @var Field|null
     */
    private $parent;

    /**
     * Initialise le champ.
     *
     * @param string    $name       Le nom du champ.
     * @param int       $type       Le type du champ (une des constantes TYPE_*).
     * @param bool      $repeatable True si le champ est répétable, false sinon.
     *
     * @throws InvalidArgumentException Si le type indiqué n'est pas valide (constantes TYPE_*)
     */
    public function __construct(string  $name, int $type = self::TYPE_TEXT, bool $repeatable = false)
    {
        if (! in_array($type, [self::TYPE_OBJECT, self::TYPE_TEXT, self::TYPE_VALUE])) {
            throw new InvalidArgumentException('Invalid field type');
        }
        $this->name = $name;
        $this->type = $type;
        $this->repeatable = $repeatable;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Field $field): void
    {
        // Seuls les champ "object" peuvent avoir des sous-champs
        if (! $this->isObject()) {
            throw new InvalidArgumentException(sprintf('Field "%s" is not an object', $this->getKey()));
        }

        // Aoute le champ dans la liste des champs
        parent::addField($field);

        // Modifie le parent du champ
        if ($field->hasParent()) {
            throw new InvalidArgumentException(sprintf('Field "%s" already has a parent', $field->getKey()));
        }
        $field->parent = $this;
    }

    /**
     * Retourne le nom du champ.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Retourne le type du champ.
     *
     * @return int Une des constantes TYPE_*.
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Indique si le champ est de type "objet".
     *
     * @return bool
     */
    public function isObject(): bool
    {
        return $this->type === self::TYPE_OBJECT;
    }

    /**
     * Indique si le champ est de type "texte".
     *
     * @return bool
     */
    public function isText(): bool
    {
        return $this->type === self::TYPE_TEXT;
    }

    /**
     * Indique si le champ est de type "valeur".
     *
     * @return bool
     */
    public function isValue(): bool
    {
        return $this->type === self::TYPE_VALUE;
    }

    /**
     * Indique si le champ est répétable.
     *
     * @return bool
     */
    public function isRepeatable(): bool
    {
        return $this->repeatable;
    }

    /**
     * Indique si le champ a un champ parent.
     *
     * @return bool True si le champ est un sous-champ, false sinon.
     */
    public function hasParent(): bool
    {
        return !is_null($this->parent);
    }

    /**
     * Retourne le champ parent.
     *
     * @return Field|null
     */
    public function getParent(): ?Field
    {
        return $this->parent;
    }

    /**
     * Retourne une clé permettant d'identifier le champ de façon unique.
     *
     * @return string Une clé de la forme "parent.parent.nom" si le champ a des parents ou "nom" sinon.
     */
    public function getKey(): string
    {
        if (! $this->hasParent()) {
            return $this->getName();
        }

        return $this->getParent()->getKey() . '.' . $this->getName();
    }

    /**
     * Retourne un libellé indiquant la clé et le type du champ.
     *
     * @return string Un libellé de la forme "nom (type de contenu)".
     */
    public function getLabel(): string
    {
        $label = $this->getKey();

        switch ($this->getType()) {
            case self::TYPE_OBJECT:
                if (! $this->isRepeatable()) {
                    return $label;
                }
                $type = 'répétable';
                break;

            case self::TYPE_TEXT:
                $type = $this->isRepeatable() ? 'textes' : 'texte';
                break;

            case self::TYPE_VALUE:
                $type = $this->isRepeatable() ? 'valeurs' : 'valeur';
                break;
        }

        return $label . ' (' . $type . ')';
    }


    /**
     * Retourne une opération qui permet de faire une chercher/remplacer sur le champ.
     *
     * @param string $search    Texte ou valeur recherchée.
     * @param string $replace   Texte ou valeur de remplacement.
     *
     * @return Operation
     */
    public function getOperation(string $search, string $replace): Operation
    {
        if ($this->isText()) {
            if ($search === '') {
                return ($replace === '') ? new ClearText($this) : new InjectText($this, $replace);
            }

            return ($replace === '') ? new RemoveText($this, $search) : new ReplaceText($this, $search, $replace);
        }

        if ($this->isValue()) {
            if ($search === '') {
                return ($replace === '') ? new ClearValue($this) : new InjectValue($this, $replace);
            }

            return ($replace === '') ? new RemoveValue($this, $search) : new ReplaceValue($this, $search, $replace);
        }

        throw new InvalidArgumentException('Search/replace is not allowed on this field');
    }
}

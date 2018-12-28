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

use Docalist\Type\Scalar;
use InvalidArgumentException;
use Docalist\Data\Record;
use Docalist\Type\Composite;
use Docalist\Type\Collection;

/**
 * Un champ sur lequel on peut lancer un chercher/remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class Field extends Fields
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
        $this->fields = [];
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
     * Retourne un callable qui permet de faire une opération de chercher/remplacer sur le champ.
     *
     * @param string $search    Texte ou valeur recherchée.
     * @param string $replace   Texte ou valeur de remplacement.
     *
     * @return callable Un callable de la forme : function (Record): bool :
     *
     * - Le callable retourné prend en paramètre l'enregistrement docalist à modifier.
     * - Il effectue l'opération de chercher/remplacer sur le champ en cours.
     * - Il retourne true si le champ a été modifié, faux sinon.
     */
    public function getOperation(string $search, string $replace): callable
    {
        if ($this->isObject()) {
            throw new InvalidArgumentException("Search/replace on an object is not allowed");
        }

        // Search est renseigné
        if ($search !== '') {
            // Replace est renseigné -> Rechercher et remplacer des occurences
            if ($replace !== '') {
                return $this->modify($search, $replace);
            }

            // Replace est vide -> Supprimer des occurences
            return $this->modify($search, '');
        }

        // Search est vide

        // Replace est renseigné -> Injecter du contenu
        if ($replace !== '') {
            return $this->inject($replace);
        }

        // Replace est vide -> Vider le champ
        return $this->clear();
    }


    /**
     * Retourne un callable qui change le texte ou la valeur indiquée.
     *
     * @param string $search    Texte ou valeur recherché.
     * @param string $replace   Texte ou valeur de remplacement.
     *
     * @return callable Un callable de la forme "function (Record): bool".
     */
    protected function modify(string $search, string $replace): callable
    {
        $operation = $this->isText() ? $this->modifyText($search, $replace) : $this->modifyValue($search, $replace);

        return $this->record($this->parent($this->repeatable($operation)));
    }

    /**
     * Retourne un callable qui modifie le contenu d'un champ scalaire de type "texte".
     *
     * @param string $search    Texte recherché.
     * @param string $replace   Texte de remplacement.
     *
     * @return callable Un callable de la forme "function (Scalar): bool".
     */
    private function modifyText(string $search, string $replace): callable
    {
        return function (Scalar $scalar) use ($search, $replace): bool {
            $count = 0;
            $value = str_replace($search, $replace, $scalar->getPhpValue(), $count);
            if ($count === 0) {
                return false;
            }

            $scalar->assign($value);

            return true;
        };
    }

    /**
     * Retourne un callable qui modifie le contenu d'un champ scalaire de type "value".
     *
     * @param string $search    Valeur recherchée.
     * @param string $replace   Valeur de remplacement.
     *
     * @return callable Un callable de la forme "function (Scalar): bool".
     */
    private function modifyValue(string $search, string $replace): callable
    {
        return function (Scalar $scalar) use ($search, $replace): bool {
            if ($scalar->getPhpValue() !== $search) {
                return false;
            }

            $scalar->assign($replace);

            return true;
        };
    }

    /**
     * Retourne un callable qui injecte le texte ou la valeur indiquée dans le champ.
     *
     * @param string $inject Le texte ou la valeur à injecter.
     *
     * @return callable Un callable de la forme : function (Record): bool.
     */
    protected function inject(string $inject): callable
    {
        $operation = $this->isRepeatable() ? $this->injectRepeatable($inject) : $this->injectNotRepeatable($inject);

        return $this->record($this->parent($operation));
    }

    /**
     * Retourne un callable qui injecte le texte ou la valeur indiquée dans un champ non répétable.
     *
     * Le callback généré teste si le champ contient déjà la valeur à injecter. Si c'est le cas, elle ne fait rien
     * et retourn false, sinon elle assigne la valeur au champ et retourne true.
     *
     * @param string $inject Le texte ou la valeur à injecter.
     *
     * @return callable Un callable de la forme "function (Scalar): bool".
     */
    private function injectNotRepeatable(string $inject): callable
    {
        return function (Scalar $scalar) use ($inject): bool {
            if ($scalar->getPhpValue() === $inject) {
                return false;
            }

            $scalar->assign($inject);

            return true;
        };
    }

    /**
     * Retourne un callable qui injecte le texte ou la valeur indiquée dans un champ répétable.
     *
     * Le callable généré teste si la valeur à injecter figure déjà dans la liste des valeurs du champs.
     * Si c'est le cas, elle ne fait rien et retourne false, sinon, elle ajoute la nouvelle valeur à la fin
     * de la collection et retourne true.
     *
     * @param string $inject Le texte ou la valeur à injecter.
     *
     * @return callable Un callable de la forme "function (Collection): bool".
     */
    private function injectRepeatable(string $inject): callable
    {
        return function (Collection $collection) use ($inject): bool {
            foreach ($collection as $occurence) {
                if ($occurence->getPhpValue() === $inject) {
                    return false;
                }
            }

            $collection[] = $inject;

            return true;
        };
    }

    /**
     * Retourne un callable qui efface le contenu du champ.
     *
     * @return callable Un callable de la forme "function (Record): bool".
     */
    protected function clear(): callable
    {
        $operation = function (Scalar $scalar): bool {
            $default = $scalar->getClassDefault();

            if ($scalar->getPhpValue() === $default) {
                return false;
            }

            $scalar->assign($default);

            return true;
        };

        return $this->record($this->parent($this->repeatable($operation)));
    }

    /**
     * Retourne un callable qui applique l'opération indiquée sur un Record.
     *
     * @param callable  $operation  Un callable de la forme "function (Any): bool".
     *
     * @return callable Un callable de la forme "function (Record): bool".
     */
    private function record(callable $operation): callable
    {
        $field = $this->hasParent() ? $this->getParent()->getName() : $this->getName();

        return function (Record $record) use ($field, $operation): bool {
            return $operation($record->$field);
        };
    }

    /**
     * Retourne un callable qui applique l'opération indiquée en tenant compte du parent du champ en cours.
     *
     * Si le champ en cours n'a pas de parent, l'opération passée en paramètre est retournée inchangée.
     *
     * Sinon, l'opération est modifiée pour qu'elle s'applique au sous-champ en cours.
     * Si le parent est répétable, l'opération sera appliquée à toutes les occurences du parent.
     *
     * @param callable  $operation  Un callable de la forme "function (Any): bool".
     *
     * @return callable Un callable de la forme :
     *
     * - "function (Any): bool" : le callable passé en paramètre si le champ n'a pas de parent.
     * - "function (Composite): bool" : si le champ a un parent,
     * - "function (Collection): bool" : si le parent est répétable,
     */
    private function parent(callable $operation): callable
    {
        if (! $this->hasParent()) {
            return $operation;
        }

        $subfield = $this->getName();
        $operation = function (Composite $composite) use ($subfield, $operation): bool {
            return $operation($composite->$subfield);
        };

        return $this->getParent()->isRepeatable() ? $this->collection($operation) : $operation;
    }

    /**
     * Modifie le callable passé en paramètre si le champ en cours est répétable.
     *
     * Si le champ en cours n'est pas répétable, l'opération passée en paramètre est retournée inchangée.
     *
     * Si le champ est répétable, la méthode retourne un callable qui applique l'opération passée en paramètre à
     * chaque élément de la collection. Le callable généré retourne true si l'opération a retourné true pour l'un
     * des éléments.
     *
     * @param callable $operation Un callable de la forme "function (Any): bool".
     *
     * @return callable Un callable de la forme :
     *
     * - "function (Collection): bool" : si le champ est répétable,
     * - "function (Any): bool" : le callable passé en paramètre sinon.
     */
    private function repeatable(callable $operation): callable
    {
        return $this->isRepeatable() ? $this->collection($operation) : $operation;
    }

    /**
     * Modifie le callable passé en paramètre pour qu'il s'applique à tous les éléments d'une collection.
     *
     * Le callable généré retourne true si l'opération a retourné true pour l'un des éléments.
     *
     * @param callable $operation Un callable de la forme "function (Any): bool".
     *
     * @return callable Un callable de la forme "function (Collection): bool".
     */
    private function collection(callable $operation): callable
    {
        return function (Collection $collection) use ($operation): bool {
            $result = false;
            foreach ($collection as $occurence) {
                $changed = $operation($occurence);
                $result = $result || $changed;
            }

            return $result;
        };
    }
}

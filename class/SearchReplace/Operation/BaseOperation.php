<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\SearchReplace\Operation;

use Docalist\Batch\SearchReplace\Operation;
use Docalist\Batch\SearchReplace\Field;
use Docalist\Data\Record;
use Docalist\Type\Collection;
use InvalidArgumentException;

/**
 * Classe de base pour les opérations de chercher/remplacer.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
abstract class BaseOperation implements Operation
{
    /**
     * Le champ sur lequel porte l'opération.
     *
     * @var Field
     */
    private $field;

    /**
     * Le type de champ sur lequel travaille l'opération.
     */
    protected const FIELD_TYPE = Field::TYPE_TEXT;

    /**
     * La chaine recherchée.
     *
     * @var string
     */
    private $search;

    /**
     * La chaine de remplacement.
     *
     * @var string
     */
    private $replace;

    /**
     * Le process qui traite les enregistrements (composition de fonctions anonymes).
     *
     * @var callable
     */
    private $process;

    /**
     * Constructeur.
     *
     * @param Field     $field      Champ sur lequel porte l'opération.
     * @param string    $search     Chaine recherchée.
     * @param string    $replace    Chaine de remplacement.
     *
     * @throws InvalidArgumentException Si le champ n'est pas du type attendu.
     */
    public function __construct(Field $field, string $search, string $replace)
    {
        if ($field->getType() !== $this::FIELD_TYPE) {
            throw new InvalidArgumentException('Invalid field type');
        }

        $this->field = $field;
        $this->search = $search;
        $this->replace = $replace;
        $this->process = $this->createProcess();
    }

    /**
     * {@inheritDoc}
     */
    final public function getField(): Field
    {
        return $this->field;
    }

    /**
     * {@inheritDoc}
     */
    final public function getSearch(): string
    {
        return $this->search;
    }

    /**
     * {@inheritDoc}
     */
    final public function getReplace(): string
    {
        return $this->replace;
    }

    /**
     * {@inheritDoc}
     */
    final public function process(Record $record): bool
    {
        return ($this->process)($record);
    }

    /**
     * {@inheritDoc}
     */
    public function getExplanation(): string
    {
        return sprintf(
            __('Remplacer <del>%s</del> par <ins>%s</ins> dans le champ <var>%s</var>.', 'docalist-batch'),
            htmlspecialchars($this->getSearch()),
            htmlspecialchars($this->getReplace()),
            $this->getField()->getLabel()
        );
    }

    /**
     * Crée le process qui traite les enregistrements.
     *
     * @return callable Un callable de la forme "function (Record): bool"
     *
     * - Le callable retourné prend en paramètre l'enregistrement docalist à modifier.
     * - Il effectue l'opération de chercher/remplacer sur le champ en cours.
     * - Il retourne true si le champ a été modifié, faux sinon.
     */
    protected function createProcess(): callable
    {
        return function (Record $record): bool {
            return false;
        };
    }

    /**
     * Modifie le callable passé en paramètre pour qu'il s'applique à tous les éléments d'une collection.
     *
     * Le callable généré retourne true si l'opération a retourné true pour l'un des éléments.
     *
     * @param callable $process Un callable de la forme "function (Any): bool".
     *
     * @return callable Un callable de la forme "function (Collection): bool".
     */
    protected function collection(callable $process): callable
    {
        return function (Collection $collection) use ($process): bool {
            $result = false;
            foreach ($collection as $occurence) {
                $changed = $process($occurence);
                $result = $result || $changed;
            }

            return $result;
        };
    }
}

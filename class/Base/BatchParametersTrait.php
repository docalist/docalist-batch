<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Base;

use Docalist\Batch\BatchParameters;

/**
 * Implementation standard de l'interface BatchParameters.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
trait BatchParametersTrait // implements BatchParameters
{
    /**
     * Les paramètres en cours.
     *
     * @var array
     */
    private $parameters = [];

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * {@inheritDoc}
     */
    public function hasParameter(string $parameter): bool
    {
        return !empty($this->parameters[$parameter]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter(string $parameter, $default = null)
    {
        return empty($this->parameters[$parameter]) ? $default : $this->parameters[$parameter];
    }
}

<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Base;

use Docalist\Batch\BatchUtil;
use Docalist\Batch\Base\BatchParametersTrait;

/**
 * Un trait avec des méthodes utilites pour les vues.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
trait BatchUtilTrait // implements BatchUtil
{
    /**
     * {@inheritDoc}
     */
    public function backButton(string $class = '', string $title = '')
    {
        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_attr('javascript:history.go(-1)'),
            $class ?: self::SECONDARY_BUTTON,
            $title ?: __('« Retour à la page précédente', 'docalist-batch')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function backToSearchButton(string $class = '', string $title = '')
    {
        return sprintf(
            '<a href="%s" class="%s">%s</a>',
            esc_attr($this->getParameter('search-url')),
            $class ?: self::SECONDARY_BUTTON,
            $title ?: __('« Retour à la page de recherche', 'docalist-batch')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function confirmButton(string $class = '', string $title = '')
    {
        return sprintf(
            '<button name="confirm" value="1" class="%s">%s</button>',
            $class ?: self::PRIMARY_BUTTON,
            $title ?: __('Confirmer »', 'docalist-batch')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function continueButton(string $class = '', string $title = '')
    {
        return sprintf(
            '<button name="continue" value="1" class="%s">%s</button>',
            $class ?: self::PRIMARY_BUTTON,
            $title ?: __('Continuer »', 'docalist-batch')
        );
    }

    /**
     * {@inheritDoc}
     */
    public function silentInput()
    {
        return '<input type="hidden" name="silent" value="1" />';
    }
}

<?php
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
namespace Docalist\Batch\Delete;

/**
 * Demande confirmation à l'utilisateur.
 *
 * @var array   $args   Les paramètres fournis par l'utilisateur.
 * @var int     $count
 */
$args;
?>
<form method="post" action="">
    <p><?php
        printf(
            __('Attention, vous allez <strong>supprimer définitivement %d notice(s)</strong>.', 'docalist-batch'),
            $count
        ) ?>
    </p>

    <p>
        <a href="<?= esc_attr($args['search-url']) ?>" class="button">
            <?= _('« Retour à la page de recherche', 'docalist-batch') ?>
        </a>

        <button name="confirm" value="1" class="button button-primary button-large button-link-delete">
            <?= __('Confirmer la suppression', 'docalist-batch') ?>
        </button>
    </p>

    <input type="hidden" name="silent" value="1" />
</form>

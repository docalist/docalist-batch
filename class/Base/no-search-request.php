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
namespace Docalist\Batch\Base;

/**
 * Vue affichée lorsqu'aucune url de recherche n'a été fournie en paramètre.
 *
 * @var Batch $this Le traitement par lot en cours d'exécution.
 * @var array $args Les paramètres fournis par l'utilisateur.
 */
?>
<p>
    <?= _('Aucune recherche indiquée dans les paramètres, impossible de continuer.', 'docalist-batch') ?>
</p>

<p>
    <a href="<?= esc_attr('javascript:history.go(-1)')?>" class="button">
        <?= __('Annuler', 'docalist-batch') ?>
    </a>
</p>

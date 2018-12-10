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
 * Vue affichée lorsque le traitement est terminé.
 *
 * @var Batch $this Le traitement par lot en cours d'exécution.
 * @var array $args Les paramètres fournis par l'utilisateur.
 */
$args;
?>
<p>
    <a href="<?= esc_attr($args['search-url']) ?>" class="button button-primary button-large">
        <?= _('« Retour à la page de recherche', 'docalist-batch') ?>
    </a>
</p>

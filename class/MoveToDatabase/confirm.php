<?php
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
declare(strict_types=1);

namespace Docalist\Batch\MoveToDatabase;

use Docalist\Batch\Batch;

/**
 * Demande confirmation à l'utilisateur.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count  Nombre de notices qui vont être traitées.
 * @var string  $form   Le formulaire de paramétrage à afficher.
 */
?>
<form method="post" action="">
    <p><?php
        printf(
            __('Vous allez <strong>transférer %d notice(s)</strong> vers la base <b>%s</b>.', 'docalist-batch'),
            $count,
            $this->getParameter('posttype')
        ) ?>
    </p>

    <p>
        <?= _e('Quand une notice est transférée dans une autre base :', 'docalist-batch') ?>
    </p>
    <ul class="ul-square">
        <li><?= __('elle conserve son statut (publiée, en attente, etc.),', 'docalist-batch') ?></li>
        <li><?= __('elle obtient un nouveau numéro de référence (champ ref),', 'docalist-batch') ?></li>
        <li><?= __('elle obtient un nouveau permalien (slug),', 'docalist-batch') ?></li>
        <li><?= __('sa date de dernière modification est mise à jour (modifiée par : vous),', 'docalist-batch') ?></li>
        <li><?= __('la page de l\'ancienne notice n\'existe plus (erreur "404 non trouvé"),', 'docalist-batch') ?></li>
        <li><?= __('on ne teste pas si l\'auteur a des droits dans la base de destination.', 'docalist-batch') ?></li>
    </ul>
    <div style="display: none">
        <?= $form ?>
    </div>

    <p>
        <?= $this->backToSearchButton() ?>
        <?= $this->backButton() ?>
        <?= $this->confirmButton() ?>
    </p>

    <?= $this->silentInput() ?>
    <?= $this->silentInput('silent2') ?>
</form>

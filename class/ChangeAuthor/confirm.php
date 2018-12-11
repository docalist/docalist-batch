<?php declare(strict_types=1);
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
namespace Docalist\Batch\ChangeAuthor;

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
        $user = get_user_by('ID', $this->getParameter('createdBy'));
        printf(
            __('Vous allez <strong>attribuer %d notice(s)</strong> à <b>%s (%s)</b>.', 'docalist-batch'),
            $count,
            $user->display_name,
            $user->user_login
        ) ?>
    </p>

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

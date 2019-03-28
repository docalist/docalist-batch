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

namespace Docalist\Batch\ChangeStatus;

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
        $status = $this->getParameter('status');
        printf(
            __('Vous allez passer <strong>%d notice(s)</strong> en statut <b>%s</b>.', 'docalist-batch'),
            $count,
            $status
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

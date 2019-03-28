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
 * Formulaire de saisie des paramètres.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count  Nombre de notices qui vont être traitées.
 * @var string  $form   Le formulaire de paramétrage à afficher.
 */
?>
<form method="post" action="">
    <p>
        <b><?php
            printf(
                __('Vous allez modifier le statut de publication de %d notice(s).', 'docalist-batch'),
                $count
            ) ?>
        </b>
    </p>

    <?= $form ?>

    <p>
        <?= $this->backToSearchButton() ?>
        <?= $this->continueButton() ?>
    </p>

    <?= $this->silentInput() ?>
</form>

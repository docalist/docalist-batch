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
namespace Docalist\Batch\SearchReplace;

use Docalist\Batch\Batch;
use Docalist\Batch\SearchReplace\Operation;

/**
 * Demande confirmation à l'utilisateur.
 *
 * @var Batch       $this       Le traitement par lot en cours d'exécution.
 * @var int         $count      Nombre de notices qui vont être traitées.
 * @var string      $form       Le formulaire de paramétrage à afficher.
 * @var array[]     $operations Liste des opérations à effectuer.
 */
?>
<style>
    var, ins, del {
        padding: 3px 3px;
        margin: 0;
        font-family: monospace;
        white-space: pre;
        text-decoration: none;
        font-style: normal;
        background: rgba(0,0,0,0.07);
        font-weight: bold;
    }
    del {
        color: #800;
    }
    ins {
        color: #080;
    }
    var {
        color: #008;
    }
</style>
<form method="post" action="">
    <p><?php
        printf(
            __('Vous allez lancer un chercher-remplacer dans %d notice(s) :', 'docalist-batch'),
            $count
        ) ?>
    </p>

    <ol class="ul-square"><?php
    foreach ($operations as $operation) { ?>
        <li><?= $operation['explain'] ?></li><?php
    } ?>
    </ol>

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

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
namespace Docalist\Batch\MoveToDatabase;

use Docalist\Batch\Batch;
use Docalist\Data\Field\PostAuthorField;
use Docalist\Forms\Container;

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
                __('Vous allez changer transférer %d notice(s).', 'docalist-batch'),
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

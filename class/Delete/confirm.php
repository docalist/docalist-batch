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

namespace Docalist\Batch\Delete;

use Docalist\Batch\Batch;

/**
 * Demande confirmation à l'utilisateur.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count  Nombre de notices qui vont être traitées.
 */
?>
<form method="post" action="">
    <p>
        <b><?= sprintf(__('Vous allez supprimer définitivement %d notice(s).', 'docalist-batch'), $count) ?></b>
    </p>

    <p>
        <?= $this->backToSearchButton() ?>
        <?= $this->confirmButton('', __('Lancer la suppression...', 'docalist-batch')) ?>
    </p>

    <?= $this->silentInput() ?>
</form>

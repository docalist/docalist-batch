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

use Docalist\Batch\Batch;

/**
 * Message affichée lorsque le traitement commence.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count
 */
?>
<p>
    <b><?= sprintf(__('Suppression de  %d notice(s).', 'docalist-batch'), $count) ?></b>
</p>

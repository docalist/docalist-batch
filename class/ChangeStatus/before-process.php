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
 * Message affiché lorsque le traitement commence.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count  Le nombre de notices à modifier.
 */
?>
<p>
    <b><?= sprintf(__('Changement du statut de publication dans %d notice(s)...', 'docalist-batch'), $count) ?></b>
</p>

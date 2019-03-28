<?php declare(strict_types=1);
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
namespace Docalist\Batch\MoveToDatabase;

use Docalist\Batch\Batch;

/**
 * Affiche le résultat du traitement.
 *
 * @var Batch $this Le traitement par lot en cours d'exécution.
 * @var int $count  Le nombre de notices modifiées.
 */
?>
<p>
    <b><?= sprintf(__('Terminé, %d notice(s) transférée(s).', 'docalist-batch'), $count) ?></b>
</p>

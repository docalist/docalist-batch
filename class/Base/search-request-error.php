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

namespace Docalist\Batch\Base;

use Docalist\Batch\Batch;

/**
 * Vue affichée lorsque l'exécution de la requête génère une erreur.
 *
 * @var Batch $this Le traitement par lot en cours d'exécution.
 */
?>
<p>
    <b><?= __('Une erreur est survenue lors de la recherche, impossible de continuer.', 'docalist-batch') ?></b>
</p>

<p>
    <?= $this->backToSearchButton($this::PRIMARY_BUTTON) ?>
</p>

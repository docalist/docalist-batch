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
namespace Docalist\Batch\Base;

use Docalist\Batch\Batch;

/**
 * Vue affichée lorsqu'aucune url de recherche n'a été fournie en paramètre.
 *
 * @var Batch $this Le traitement par lot en cours d'exécution.
 */
?>
<p>
    <b><?= __('Aucune recherche indiquée dans les paramètres, impossible de continuer.', 'docalist-batch') ?></b>
</p>

<p>
    <?= $this->backButton($this::PRIMARY_BUTTON) ?>
</p>

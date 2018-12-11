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
namespace Docalist\Batch\Base;

use Docalist\Batch\Batch;

/**
 * Vue affichée lorsque la requête initiale ne donne aucun résultat.
 *
 * @var Batch $this     Le traitement par lot en cours d'exécution.
 * @var bool  $modified False s'il s'agit de la requête initiale, true s'il s'agit de la requête modifiée.
 */
?>
<p>
    <b>
        <?=
            empty($modified)
            ? __('La recherche indiquée ne donne aucune réponse, impossible de continuer.', 'docalist-batch')
            : __('Il n\'y a plus de notices Docalist à traiter, impossible de continuer.', 'docalist-batch')
        ?>
    </b>
</p>

<p>
    <?= $this->backToSearchButton($this::PRIMARY_BUTTON) ?>
</p>

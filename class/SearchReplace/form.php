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
use Docalist\Forms\Container;

/**
 * Formulaire de saisie des paramètres.
 *
 * @var Batch   $this   Le traitement par lot en cours d'exécution.
 * @var int     $count  Nombre de notices qui vont être traitées.
 * @var string  $form   Le formulaire de paramétrage à afficher.
 */
?>
<style>
    select {
        width: 100%;
    }
    optgroup {
        font-weight: lighter;
    }
</style>
<form method="post" action="">
    <p>
        <b><?php
            printf(
                __('Vous allez lancer un chercher-remplacer dans %d notice(s).', 'docalist-batch'),
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

<h3><?= __('Documentation', 'docalist-batch') ?></h3>

<p><?= __(
    'Le formulaire ci-dessus vous permet de définir la <b>liste des opérations</b> à effectuer sur les notices
    sélectionnées. Vous pouvez ajouter autant d\'opérations que vous le souhaitez et vous pouvez avoir plusieurs
    opérations qui portent sur le même champ, par exemple pour modifier plusieurs mots-clés en une seule étape.',
    'docalist-batch'
) ?>
</p>

<p><?= __('Pour chaque opération, vous devez indiquer :', 'docalist-batch') ?></p>

<ul class="ul-square">
    <li><?= __('le <b>champ concerné</b> (à choisir dans la liste),', 'docalist-batch') ?></li>
    <li><?= __('le texte ou la <b>valeur recherchée</b> (optionnel),', 'docalist-batch') ?></li>
    <li><?= __('le texte ou la <b>valeur de remplacement</b> (optionnel).', 'docalist-batch') ?></li>
</ul>

<p><?= __(
    'La <b>liste des champs</b> indique le libellé et le nom de chaque champ et <b>mentionne son type</b> :',
    'docalist-batch'
) ?>
</p>

<ul class="ul-square">
    <li><?= __(
        '<b>Pour un champ de type "texte"</b>, le chercher/remplacer fonctionne comme dans un traitement
        de texte : il regarde dans le contenu du champ s\'il trouve la chaine de caractères indiquée dans
        la zone "rechercher" (n\'importe où) et s\'il la trouve, il la remplace par le texte indiqué
        dans la zone "remplacer par".<br />
        Par exemple si un champ titre contient la valeur "mon titre" et que vous remplacez "on t" par
        "on beau t", vous obtiendrez "mon beau titre".',
        'docalist-batch'
    ) ?>
    </li>
    <li><?= __(
        '<b>Pour un champ de type "value"</b>, c\'est la <b>valeur entière</b> qui est prise en compte :
        le chercher/remplacer ne sera effectué que si le champ a une valeur qui correspond très exactement
        à ce que vous indiquez dans la zone "rechercher".<br />
        En général, les champs de type "value" sont associés à des tables d\'autorité qui indiquent les
        valeurs autorisées et le champ contient <b>le code des valeurs</b> (et non pas pas le libellé
        associé).<br />
        Par exemple, pour changer le genre "Journal" par "Revue scientifique" dans quelques périodiques,
        vous devez savoir quelle est la table d\'autorité associée au champ "genre" et utiliser les codes
        adéquats pour remplacer "newspaper" par "academic-journal".',
        'docalist-batch'
    ) ?>
    </li>
    <li><?= __(
        'Dans tous les cas, la <b>casse des caractères est prise en compte</b> (majuscules, minuscules,
        accents, espaces...)',
        'docalist-batch'
    ) ?>
    </li>
</ul>

<p><?= __('<b>Texte recherché et texte de remplacement :</b>', 'docalist-batch') ?></p>

<p><?= __(
    'Les zones "rechercher" et "remplacer par" sont optionnelles. Le traitement qui sera effectué dépend
    de ce que vous indiquez :',
    'docalist-batch'
) ?>
</p>

<table class="widefat">
    <thead>
        <tr>
            <th><?= __('Rechercher', 'docalist-batch') ?></th>
            <th><?= __('Remplacer par', 'docalist-batch') ?></th>
            <th><?= __('Traitement effectué', 'docalist-batch') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="column-title"><?= __('renseigné', 'docalist-batch') ?></td>
            <td class="column-title"><?= __('renseigné', 'docalist-batch') ?></td>
            <td><?= __(
                '<b>Rechercher et remplacer des occurences.</b><br/>
                Pour un champ de type "texte", teste si le champ contient le texte indiqué dans la zone "rechercher"
                et remplace toutes les occurences trouvées par le texte indiqué dans la zone "remplacer par".<br />
                Pour un champ de type "value", teste si le champ contient la valeur exacte indiquée dans la zone
                "rechercher" et la remplace par la valeur exacte indiquée dans la zone "remplacer par".',
                'docalist-batch'
            ) ?>
            </td>
        </tr>
        <tr>
            <td class="column-title"><?= __('renseigné', 'docalist-batch') ?></td>
            <td class="column-title"><?= __('vide', 'docalist-batch') ?></td>
            <td><?= __(
                '<b>Supprimer des occurences.</b><br/>
                Pour un champ de type "texte", teste si le champ contient le texte indiqué dans la zone "rechercher"
                et supprime toutes les occurences trouvées.<br />
                Pour un champ de type "value", teste si le champ contient la valeur exacte indiquée dans la zone
                "rechercher" et la supprime.',
                'docalist-batch'
            ) ?>
            </td>
        </tr>
        <tr>
            <td class="column-title"><?= __('vide', 'docalist-batch') ?></td>
            <td class="column-title"><?= __('renseigné', 'docalist-batch') ?></td>
            <td><?= __(
                '<b>Injecter du contenu.</b><br/>
                Injecte dans le champ la valeur exacte indiquée dans la zone "remplacer par".<br />
                Si le champ est répétable (multivalué), ça ajoute au champ une nouvelle occurence contenant
                le texte ou la valeur exacte indiquée.<br />
                Si le champ est monovalué, ça remplace le contenu existant du champ (qu\'il soit vide ou non)
                par le texte ou la valeur exacte indiquée.',
                'docalist-batch'
            ) ?>
            </td>
        </tr>
        <tr>
            <td class="column-title"><?= __('vide', 'docalist-batch') ?></td>
            <td class="column-title"><?= __('vide', 'docalist-batch') ?></td>
            <td><?= __(
                '<b>Vider le champ.</b><br/>
                Supprime le contenu du champ (toutes les occurences dans le cas d\'un champ répétable).',
                'docalist-batch'
            ) ?>
            </td>
        </tr>
    </tbody>
</table>
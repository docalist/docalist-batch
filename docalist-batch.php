<?php
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 *
 * Plugin Name: Docalist Batch
 * Plugin URI:  https://docalist.org/
 * Description: Traitements par lot sur des notices Docalist.
 * Version:     1.0.0-dev
 * Author:      Daniel Ménard
 * Author URI:  http://docalist.org/
 * Text Domain: docalist-batch
 * Domain Path: /languages
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
declare(strict_types=1);

namespace Docalist\Batch;

use Docalist\Data\Plugin as DocalistData;
use Docalist\Search\QueryDSL;

/**
 * Version du plugin.
 */
define('DOCALIST_BATCH_VERSION', '1.0.0-dev'); // Garder synchro avec la version indiquée dans l'entête

/**
 * Path absolu du répertoire dans lequel le plugin est installé.
 *
 * Par défaut, on utilise la constante magique __DIR__ qui retourne le path réel du répertoire et résoud les liens
 * symboliques.
 *
 * Si le répertoire du plugin est un lien symbolique, la constante doit être définie manuellement dans le fichier
 * wp_config.php et pointer sur le lien symbolique et non sur le répertoire réel.
 */
!defined('DOCALIST_BATCH_DIR') && define('DOCALIST_BATCH_DIR', __DIR__);

/**
 * Path absolu du fichier principal du plugin.
 */
define('DOCALIST_BATCH', DOCALIST_BATCH_DIR . DIRECTORY_SEPARATOR . basename(__FILE__));

/**
 * Url de base du plugin.
 */
define('DOCALIST_BATCH_URL', plugins_url('', DOCALIST_BATCH));

/**
 * Initialise le plugin.
 */
add_action('plugins_loaded', function () {
    // Auto désactivation si les plugins dont on a besoin ne sont pas activés
    $dependencies = ['DOCALIST_CORE', 'DOCALIST_DATA', 'DOCALIST_SEARCH'];
    foreach ($dependencies as $dependency) {
        if (! defined($dependency)) {
            return add_action('admin_notices', function () use ($dependency) {
                deactivate_plugins(DOCALIST_BATCH);
                unset($_GET['activate']); // empêche wp d'afficher "extension activée"
                printf(
                    '<div class="%s"><p><b>%s</b> has been deactivated because it requires <b>%s</b>.</p></div>',
                    'notice notice-error is-dismissible',
                    'Docalist Batch',
                    ucwords(strtolower(strtr($dependency, '_', ' ')))
                );
            });
        }
    }

    // Ok
    docalist('autoloader')
        ->add(__NAMESPACE__, __DIR__ . '/class')
        ->add(__NAMESPACE__ . '\Tests', DOCALIST_BATCH_DIR . '/tests');

    // Initialise le plugin
    $docalistData = docalist('docalist-data'); /** @var DocalistData $docalistData */
    $searchEngine = docalist('docalist-search-engine'); /** @var SearchEngine $searchEngine */
    $queryDsl = docalist('elasticsearch-query-dsl'); /** @var QueryDSL $queryDsl */
    $plugin = new DocalistBatch();
    $plugin->initialize($docalistData, $searchEngine, $queryDsl);
}, 11); // Priorité 11 : on attend que docalist-data soit chargé car on en a besoin

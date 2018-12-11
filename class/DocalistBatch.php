<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch;

use Docalist\Data\Plugin as DocalistData;
use Docalist\Search\SearchEngine as SearchEngine;
use Docalist\Views;
use Docalist\Tools\ToolsList;
use Docalist\Batch\Widget\BatchWidget;
use Docalist\Batch\Delete\BatchDelete;
use Docalist\Batch\SearchReplace\BatchSearchReplace;
use Docalist\Batch\ChangeStatus\BatchChangeStatus;
use Docalist\Batch\ChangeAuthor\BatchChangeAuthor;
use Docalist\Batch\MoveToDatabase\BatchMoveToDatabase;
use Docalist\Search\QueryDSL;
use Docalist\Data\Database;

/**
 * Plugin docalist-batch.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class DocalistBatch
{
    /**
     * Le widget docalist batch.
     *
     * @var BatchWidget
     */
    private $widget;

    /**
     * Initialise le plugin.
     *
     * @param DocalistData  $docalistData   Le plugin docalist-data à utiliser.
     * @param SearchEngine  $searchEngine   SearchEngine à utiliser.
     * @param QueryDSL      $queryDsl       QueryDSL Elasticsearch.
     */
    public function initialize(DocalistData $docalistData, SearchEngine $searchEngine, QueryDSL $queryDsl): void
    {
        /*
         * On initialise le widget en deux temps car l'action widgets_init est générée *avant* l'action init.
         * Donc quand widgets_init est génére, docalist-database n'est pas encore initialisé (il utilise init avec
         * une priorité de 10) et donc on ne peut pas obtenir la liste des bases. Du coup pendant widgets_init,
         * on se contente de déclarer le widget dans WordPress (registerWidget) sans lui fournir d'outils.
         * Une fois que docalist-data est initialisé (on hook init avec une priorité de 11), on récupère la liste
         * des bases docalist, on contruit la liste des outils disponibles et on la fournit au widget lors de
         * l'appel à initialize().
         */

        // Déclare le widget
        add_action('widgets_init', function (): void {
            $this->widget = new BatchWidget();
            register_widget($this->widget);
        });

        add_action(
            'init',
            function () use ($docalistData, $searchEngine, $queryDsl) {
                // Charge les fichiers de traduction du plugin
                load_plugin_textdomain('docalist-batch', false, 'docalist-batch/languages');

                // Ajoute notre répertoire "class" au service "docalist-views".
                add_filter('docalist_service_views', function (Views $views) {
                    return $views->addDirectory('docalist-batch', DOCALIST_BATCH_DIR . '/class');
                });

                // Récupère la liste des traitements disponibles
                $batches = $this->getBatches($docalistData->databases(), $queryDsl);

                // Initialize les dépendances du widget
                $this->widget->initialize(new ToolsList($batches), $searchEngine);

                // Liste les traitements par lot sur la page "outils docalist"
                add_filter('docalist-tools', function (array $tools) use ($batches): array {
                    return $tools + $batches;
                });
            },
            11 // Priorité supérieure au init de docalist-data sinon les bases ne sont pas encore initialisées
        );
    }

    /**
     * Retourne la liste des traitements par lots disponibles.
     *
     * @param array     $databases  La liste des bases (postType => Database).
     * @param QueryDSL  $queryDsl   Query DSL Elasticsearch.
     *
     * @return array Un array de la forme 'batch-name' => factory.
     */
    private function getBatches(array $databases, QueryDSL $queryDsl): array
    {
        // Si on n'a aucune base docalist, aucun traitement par lot n'est possible
        if (empty($databases)) {
            return [];
        }

        // Initialise et retourne la liste
        return [
            'batch-change-status' => function () use ($databases, $queryDsl) {
                return new BatchChangeStatus($databases, $queryDsl);
            },
            'batch-change-author' => function () use ($databases, $queryDsl) {
                return new BatchChangeAuthor($databases, $queryDsl);
            },
//             'batch-search-replace' => function () use ($databases, $queryDsl) {
//                 return new BatchSearchReplace($databases, $queryDsl);
//             },
//             'batch-move-to-database' => function () use ($databases, $queryDsl) {
//                 return new BatchMoveToDatabase($databases, $queryDsl);
//             },
            'batch-delete' => function () use ($databases, $queryDsl) {
                return new BatchDelete($databases, $queryDsl);
            },
        ];
    }
}

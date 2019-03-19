<?php declare(strict_types=1);
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2018 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
namespace Docalist\Batch\Widget;

use WP_Widget;
use Docalist\Search\SearchEngine;
use Docalist\Search\SearchUrl;
use Docalist\Forms\Container;
use Docalist\Tools\Tools;
use Docalist\Tools\ToolsPage;

/**
 * Widget "traitements par lots".
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class BatchWidget extends WP_Widget
{
    /**
     * Liste des traitement par lots disponibles.
     *
     * @var Tools|null
     */
    private $tools = null;

    /**
     * Le moteur de recherche utilisé.
     *
     * @var SearchEngine|null
     */
    private $searchEngine = null;

    /**
     * Initialise le widget.
     */
    public function __construct()
    {
        // Identifiant du widget
        $id = 'docalist_batch';

        // Titre affiché dans le back office
        $title = __('Traitements par lots Docalist', 'docalist-batch');

        // Description affichée dans le back office
        $description = __(
            'Permet de lancer un traitement par lot sur les résultats d\'une recherche Docalist.',
            'docalist-batch'
        );

        // Paramètres
        $args  = [
            'classname' => 'docalist-batch',
            'description' => $description,
        ];

        // Options d'affichage dans le back-office
        $options = [
            'width' => 800,     // Largeur requise pour le formulaire de saisie des paramètres
            'height' => 800,
        ];

        // Initialise le widget
        parent::__construct($id, $title, $args, $options);
    }

    /**
     * Initialise les dépendances du widget.
     *
     * @param Tools $tools
     */
    public function initialize(Tools $tools, SearchEngine $searchEngine)
    {
        $this->tools = $tools;
        $this->searchEngine = $searchEngine;
    }

    /**
     * Retourne l'objet SearchUrl à utiliser pour créer les liens.
     *
     * La méthode vérifie :
     * - qu'on a une recherche en cours,
     * - que la recherche a été créée à partir d'une SearchUrl,
     * - qu'on a obtenu des réponses.
     *
     * @return SearchUrl|null La SearchUrl obtenue ou null si l'une des conditions n'est pas remplie.
     */
    private function getSearchUrl(): ?SearchUrl
    {
        // Vérifie que initialize() a été appellée
        if (empty($this->searchEngine)) {
            return null;
        }

        // Vérifie qu'on a une recherche en cours
        $searchRequest = $this->searchEngine->getSearchRequest();
        if (is_null($searchRequest)) {
            return null;
        }

        // Vérifie que la recherche a été créée à partir d'une SearchUrl
        $searchUrl = $searchRequest->getSearchUrl();
        if (is_null($searchUrl)) {
            return null;
        }

        // Vérifie qu'on a obtenu des réponses
        $searchResponse = $this->searchEngine->getSearchResponse();
        if (is_null($searchResponse) || 0 === $searchResponse->getHitsCount()) {
            return null;
        }

        // Ok
        return $searchUrl;
    }

    /**
     * Construit la liste des liens à afficher.
     *
     * @return string[] Un tableau de la forme url => libellé.
     */
    private function getLinks(): array
    {
        // Vérifie qu'on a une liste d'outils
        if (empty($this->tools)) {
            return [];
        }

        // Vérifie qu'on a une SearchUrl utilisable
        $searchUrl = $this->getSearchUrl();
        if (is_null($searchUrl)) {
            return [];
        }

        // Récupère l'url complète de la recherche en cours en ajoutant les types implicites éventuels
        $types = $searchUrl->getTypes();
        if ($searchUrl->hasFilter('in') || empty($types)) {
            $url = $searchUrl->getUrlForPage(1);
        } else {
            $url = $searchUrl->toggleFilter('in', $types);
        }

        // Url de base
        $url = admin_url('tools.php') . '?page=docalist-tools&m=Run&search-url=' . urlencode($url);

        // Construit la liste des liens
        $links = [];
        foreach ($this->tools->getList() as $toolName) {
            $tool = $this->tools->get($toolName);
            if (current_user_can($tool->getCapability())) {
                $links[$url . '&tool=' . $toolName] = $tool->getLabel();
            }
        }

        // Done
        return $links;
    }

    /**
     * Affiche le widget.
     *
     * @param array $context Les paramètres d'affichage du widget. Il s'agit des paramètres définis par le thème
     * lors de l'appel à la fonction WordPress. Le tableau passé en paramètre inclut notamment les clés :
     * - before_widget : code html à afficher avant le widget.
     * - after_widget : texte html à affiche après le widget.
     * - before_title : code html à générer avant le titre (exemple : '<h2>')
     * - after_title  : code html à générer après le titre (exemple : '</h2>')
     *
     * @param array $settings Les paramètres du widget que l'administrateur a saisi dans le formulaire.
     */
    public function widget($context, $settings)
    {
        // On ne fait rien si l'utilisateur ne peut pas accéder à la page "outils docalist"
        if (! current_user_can(ToolsPage::CAPABILITY)) {
            return;
        }

        // Construit la liste de liens à afficher
        $links = $this->getLinks();

        // On n'affiche pas le widget si on n'a aucun lien (pas les droits, pas de réponses, etc.)
        if (empty($links)) {
            return;
        }

        // Début du widget
        echo $context['before_widget'];

        // Titre du widget
        $title = $settings['widget-title'] ?? '';
        $title = apply_filters('widget_title', $title, $settings, $this->id_base);
        if ($title) {
            echo $context['before_title'], $title, $context['after_title'];
        }

        // Affiche la liste des opérations disponibles
        echo '<ul>';
        foreach ($links as $url => $label) {
            printf('<li><a href="%s">%s</a></li>', esc_attr($url), $label);
        }
        echo '</ul>';

        // Fin du widget
        echo $context['after_widget'];
    }

    /**
     * Affiche le formulaire qui permet de paramètrer le widget.
     *
     * @see WP_Widget::form()
     */
    public function form($instance)
    {
        // Récupère le formulaire à afficher
        $form = $this->getSettingsForm();

        // Lie le formulaire aux paramètres du widget
        $form->bind($instance ?: $this->getDefaultSettings());

        // Dans WordPress, les widget ont un ID et sont multi-instances. Le
        // formulaire doit donc avoir le même nom que le widget.
        // Par ailleurs, l'API Widgets de WordPress attend des noms
        // de champ de la forme "widget-id_base-[number][champ]". Pour générer
        // cela facilement, on donne directement le bon nom au formulaire.
        // Pour que les facettes soient orrectement clonées, le champ facets
        // définit explicitement repeatLevel=2 (cf. settingsForm)
        $name = 'widget-' . $this->id_base . '[' . $this->number . ']';
        $form->setName($name);

        // Affiche le formulaire
        $form->display('wordpress');
    }

    /**
     * Retourne les paramètres par défaut du widget.
     *
     * @return array
     */
    protected function getDefaultSettings(): array
    {
        return [
            'widget-title' => __('Traitements par lot', 'docalist-batch'),
            //             'before-facets' => '<ul class="facets">',
        //             'after-facets' => '</ul>',
        ];
    }

    /**
     * Crée le formulaire permettant de paramètrer le widget.
     *
     * @return Container
     */
    protected function getSettingsForm(): Container
    {
        $form = new Container();

        $form->input('widget-title')
            ->setAttribute('id', $this->get_field_id('title'))
            ->setLabel(__('Titre du widget', 'docalist-batch'))
            ->addClass('widefat');

        // remarque : on force l'ID pour que le widget affiche le bon titre en backoffice.
        // cf widgets.dev.js, fonction appendTitle(), L250

        //         $form->input('before-facets')
        //             ->setLabel(__('Avant la liste', 'docalist-search'))
        //             ->addClass('widefat');

        //         $form->input('after-facets')
        //             ->setLabel(__('Après la liste', 'docalist-search'))
        //             ->addClass('widefat');

        return $form;
    }

    /**
     * Enregistre les paramètres du widget.
     *
     * La méthode vérifie que les nouveaux paramètres sont valides et retourne
     * la version corrigée.
     *
     * @param array $new les nouveaux paramètres du widget.
     * @param array $old les anciens paramètres du widget
     *
     * @return array La version corrigée des paramètres.
     */
    public function update($new, $old)
    {
        $settings = $this->getSettingsForm()->bind($new)->getData();

        //         echo '<pre>$new=', htmlspecialchars(var_export($new,true)), '</pre>';
        //         echo '<pre>form data=', htmlspecialchars(var_export($settings,true)), '</pre>';
        //         die();

        return $settings;
    }
}

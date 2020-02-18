<?php
/**
 * This file is part of Docalist Data.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Batch;

/**
 * Installation/désinstallation du plugin.
 *
 * @author Daniel Ménard <daniel.menard@laposte.net>
 */
final class Installer
{
    /**
     * Retourne la liste des capacités qui seront créées/supprimées lors de
     * l'activation / la désactivation du plugin.
     *
     * @return string[]
     */
    private function getCapabilities(): array
    {
        return [
            'docalist_batch_change_status',
            'docalist_batch_change_author',
            'docalist_batch_search_replace',
            'docalist_batch_move_to_database',
            'docalist_batch_delete',
        ];
    }

    /**
     * Activation : enregistre les tables prédéfinies.
     */
    final public function activate(): void
    {
        $adminRole = wp_roles()->get_role('administrator');
        if (is_null($adminRole)) {
            return;
        }

        foreach ($this->getCapabilities() as $capability) {
            $adminRole->add_cap($capability);
        }
    }

    /**
     * Désactivation : supprime les tables prédéfinies.
     */
    final public function deactivate(): void
    {
        $adminRole = wp_roles()->get_role('administrator');
        if (is_null($adminRole)) {
            return;
        }

        foreach ($this->getCapabilities() as $capability) {
            $adminRole->remove_cap($capability);
        }
    }
}

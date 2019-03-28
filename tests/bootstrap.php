<?php
/**
 * This file is part of Docalist Batch.
 *
 * Copyright (C) 2012-2019 Daniel Ménard
 *
 * For copyright and license information, please view the
 * LICENSE file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Docalist\Batch\Tests;

// Environnement de test
$GLOBALS['wp_tests_options'] = [
    'active_plugins' => [
        'docalist-core/docalist-core.php',
        'docalist-data/docalist-data.php',
        'docalist-search/docalist-search.php',
        'docalist-batch/docalist-batch.php',
    ],
];

require_once __DIR__ . '/../../../../vendor/autoload.php';
require_once __DIR__ . '/../vendor/autoload.php';

// wordpress-tests doit être dans le include_path de php
// sinon, modifier le chemin d'accès ci-dessous
require_once 'wordpress-develop/tests/phpunit/includes/bootstrap.php';

<?php
/**
 * @package     mod_mandantenbrief
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Fallback-Autoloader: Stelle sicher, dass die Hauptklasse auch ohne Namespace-Autoload verfügbar ist
if (!class_exists('ModMandantenbrief\\Site\\Helper\\Module')) {
    $helperPath = __DIR__ . '/src/Helper/Module.php';
    if (is_file($helperPath)) {
        require_once $helperPath;
    } else {
        throw new RuntimeException('Mandantenbrief: src/Helper/Module.php nicht gefunden. Bitte Installation prüfen.');
    }
}

use ModMandantenbrief\Site\Helper\Module;

$helper = new Module($module, $params);
require JModuleHelper::getLayoutPath('mod_mandantenbrief', $params->get('layout', 'default'));

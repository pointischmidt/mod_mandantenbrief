<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mandantenbrief
 *
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use ModMandantenbrief\Site\Helper\Module;

// Initialize module
$moduleInstance = new Module($module, $params);
$articles = $moduleInstance->getArticles();
$moduleClasses = $moduleInstance->getModuleClasses();
$debugInfo = $moduleInstance->getDebugInfo();

// Load template
require ModuleHelper::getLayoutPath('mod_mandantenbrief', $params->get('layout', 'default'));

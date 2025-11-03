<?php
/**
 * @package     mod_mandantenbrief
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

use ModMandantenbrief\Site\Helper\Module;

$helper = new Module($module, $params);
require JModuleHelper::getLayoutPath('mod_mandantenbrief', $params->get('layout', 'default'));
<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mandantenbrief
 */

namespace ModMandantenbrief\Site\Helper;

defined('_JEXEC') or die;

// Ensure helper dependencies are loaded explicitly
require_once __DIR__ . '/ParserHelper.php';
require_once __DIR__ . '/CacheHelper.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

class Module
{
    // ... properties remain unchanged ...

    private function loadYOOthemeParameters()
    {
        // Content Settings
        $this->max_articles = (int)$this->params->get('max_articles', 12);
        
        // Robust normalization for checkboxes or CSV
        $show = $this->params->get('show_elements', 'title,excerpt,date,image');
        if (is_array($show)) {
            $this->show_elements = $show;
        } elseif (is_string($show)) {
            $this->show_elements = array_filter(array_map('trim', explode(',', $show)));
        } else {
            $this->show_elements = ['title','excerpt','date','image'];
        }
        
        $this->cache_ttl = (int)$this->params->get('cache_ttl', 7);
        
        // YOOtheme Layout
        $this->layout_type = $this->params->get('layout_type', 'grid');
        $this->grid_columns = $this->params->get('grid_columns', '1@s 2@m 3@l');
        $this->grid_gap = $this->params->get('grid_gap', 'medium');
        $this->grid_divider = $this->params->get('grid_divider', '');
        
        // YOOtheme Card Design
        $this->card_style = $this->params->get('card_style', 'default');
        $this->card_size = $this->params->get('card_size', 'medium');
        $this->image_transition = $this->params->get('image_transition', 'scale-up');
        
        // YOOtheme Integration
        $this->inherit_theme_colors = (bool)$this->params->get('inherit_theme_colors', 1);
        $this->use_theme_typography = (bool)$this->params->get('use_theme_typography', 1);
        $this->custom_css_class = $this->params->get('custom_css_class', '');
        
        // Debug
        $this->enable_debug = (bool)$this->params->get('enable_debug', 0);
        $this->debug_level = $this->params->get('debug_level', 'basic');
        $this->clear_cache = (bool)$this->params->get('clear_cache', 0);
    }

    // ... rest of class remains unchanged ...
}

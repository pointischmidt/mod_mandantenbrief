<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mandantenbrief
 *
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace ModMandantenbrief\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use ModMandantenbrief\Site\Helper\ParserHelper;
use ModMandantenbrief\Site\Helper\CacheHelper;

/**
 * YOOtheme-optimized Mandantenbrief Module Helper
 */
class Module
{
    private $module;
    private $params;
    private $config;
    private $articles = array();
    private $content;
    private $debug_info = array();
    private $is_single_article = false;
    
    // YOOtheme Integration Properties
    private $grid_columns;
    private $grid_gap;
    private $card_style;
    private $layout_type;
    private $show_elements;
    private $inherit_theme_colors;
    private $use_theme_typography;
    private $custom_css_class;
    
    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
        $this->loadYOOthemeParameters();
        $this->setupConfiguration();
        $this->processContent();
    }
    
    /**
     * Load YOOtheme-compatible parameters
     */
    private function loadYOOthemeParameters()
    {
        // Content Settings
        $this->max_articles = (int)$this->params->get('max_articles', 12);
        $this->show_elements = explode(',', $this->params->get('show_elements', 'title,excerpt,date,image'));
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
    
    /**
     * Setup module configuration
     */
    private function setupConfiguration()
    {
        $uri = Uri::getInstance();
        $joomla_subfolder = '';
        
        // Auto-detect Joomla subfolder
        if (strpos($uri->getPath(), '/khs-steuerberater/') !== false) {
            $joomla_subfolder = '/khs-steuerberater';
        }
        
        $this->config = array(
            'infodienst_url' => $this->params->get('infodienst_url', 'https://onlineinfodienst.de/meine-steuer/index/'),
            'max_articles' => $this->max_articles,
            'joomla_subfolder' => $joomla_subfolder,
            'cache_ttl_days' => $this->cache_ttl,
            'server_root' => '/var/customers/webs/djumla/djumla.dev',
            'joomla_install' => '/khs-steuerberater'
        );
    }
    
    /**
     * Process content based on page parameter
     */
    private function processContent()
    {
        try {
            $app = Factory::getApplication();
            $page = $app->input->get('page', '', 'string');
            
            if (!empty($page) && $page[0] !== '/') {
                $page = '/' . $page;
            }
            
            $this->is_single_article = (strpos($page, '/text/') !== false);
            $fetch_url = $this->config['infodienst_url'] . ($page ?: '');
            
            // Initialize debug info
            $this->initializeDebugInfo($fetch_url, $page);
            
            // Load content
            if (!empty($fetch_url)) {
                $this->content = @file_get_contents($fetch_url);
                $this->debug_info['content_loaded'] = !empty($this->content);
                $this->debug_info['content_length'] = strlen($this->content ?: '');
            }
            
            // Replace URLs
            $this->replaceUrls();
            
            // Parse articles for overview pages
            if (!$this->is_single_article && !empty($this->content)) {
                $this->parseArticles();
            }
            
        } catch (\Exception $e) {
            $this->debug_info['exception'] = $e->getMessage();
        }
    }
    
    /**
     * Initialize debug information
     */
    private function initializeDebugInfo($fetch_url, $page)
    {
        $this->debug_info = array(
            'timestamp' => date('H:i:s'),
            'fetch_url' => $fetch_url,
            'page' => $page,
            'is_single_article' => $this->is_single_article ? 'JA' : 'NEIN',
            'layout_type' => $this->layout_type,
            'grid_columns' => $this->grid_columns,
            'card_style' => $this->card_style,
            'show_elements' => implode(', ', $this->show_elements),
            'yootheme_integration' => $this->inherit_theme_colors ? 'JA' : 'NEIN',
            'content_loaded' => false,
            'content_length' => 0,
            'found_articles' => 0
        );
    }
    
    /**
     * Replace URLs in content
     */
    private function replaceUrls()
    {
        if (empty($this->content)) return;
        
        $current_url = Uri::getInstance()->toString(array('scheme', 'host', 'path'));
        
        $replacements = array(
            'http://www.meine-steuer.com/aktuelles.php?page=' => $current_url . '?page=',
            'www.meine-steuer.com/aktuelles.php?page=' => $current_url . '?page=',
            'aktuelles.php?page=' => $current_url . '?page='
        );
        
        foreach ($replacements as $search => $replace) {
            $this->content = str_replace($search, $replace, $this->content);
        }
    }
    
    /**
     * Parse articles from content
     */
    private function parseArticles()
    {
        $parser = new ParserHelper();
        $cache = new CacheHelper($this->config['cache_ttl_days']);
        
        // Check for tools page
        if (strpos($this->debug_info['fetch_url'], '/tools.html') !== false) {
            $this->articles = $parser->parseToolsPage($this->content, $this->config);
        } else {
            // Parse moses_index_item articles
            $this->articles = $parser->parseIndexPage($this->content, $this->config, $cache);
        }
        
        // Limit articles
        if (count($this->articles) > $this->max_articles) {
            $this->articles = array_slice($this->articles, 0, $this->max_articles);
        }
        
        $this->debug_info['found_articles'] = count($this->articles);
    }
    
    /**
     * Get articles for template
     */
    public function getArticles()
    {
        return $this->articles;
    }
    
    /**
     * Get YOOtheme-compatible CSS classes
     */
    public function getModuleClasses()
    {
        $classes = array();
        
        // Container classes
        $classes['container'] = array();
        if ($this->custom_css_class) {
            $classes['container'][] = $this->custom_css_class;
        }
        
        // Grid classes based on layout type
        $classes['grid'] = array('uk-grid');
        
        if ($this->layout_type === 'grid') {
            $classes['grid'][] = 'uk-grid-' . $this->grid_gap;
            if ($this->grid_divider) {
                $classes['grid'][] = $this->grid_divider;
            }
            
            // Parse YOOtheme grid format: "1@s 2@m 3@l"
            $responsive_classes = $this->parseGridColumns($this->grid_columns);
            $classes['grid'][] = $responsive_classes;
        }
        
        // Card classes
        $classes['card'] = array('uk-card');
        
        if ($this->card_style !== 'default') {
            $classes['card'][] = 'uk-card-' . $this->card_style;
        }
        
        if ($this->card_size !== 'medium') {
            $classes['card'][] = 'uk-card-' . $this->card_size;
        }
        
        $classes['card'][] = 'uk-card-body';
        
        // Image transition
        $classes['image'] = array();
        if ($this->image_transition) {
            $classes['image'][] = 'uk-transition-' . $this->image_transition;
        }
        
        return $classes;
    }
    
    /**
     * Parse YOOtheme grid column format
     */
    private function parseGridColumns($grid_columns)
    {
        // Convert "1@s 2@m 3@l" to "uk-child-width-1-1@s uk-child-width-1-2@m uk-child-width-1-3@l"
        $parts = explode(' ', trim($grid_columns));
        $classes = array();
        
        foreach ($parts as $part) {
            if (preg_match('/^(\d+)@([sml])$/', $part, $matches)) {
                $columns = $matches[1];
                $breakpoint = $matches[2];
                $classes[] = 'uk-child-width-1-' . $columns . '@' . $breakpoint;
            } elseif (is_numeric($part)) {
                $classes[] = 'uk-child-width-1-' . $part;
            }
        }
        
        return implode(' ', $classes);
    }
    
    /**
     * Get debug information
     */
    public function getDebugInfo()
    {
        return $this->enable_debug ? $this->debug_info : array();
    }
    
    /**
     * Check if element should be shown
     */
    public function showElement($element)
    {
        return in_array($element, $this->show_elements);
    }
    
    /**
     * Get single article content
     */
    public function getSingleArticleContent()
    {
        return $this->is_single_article ? $this->content : '';
    }
    
    /**
     * Check if this is a single article view
     */
    public function isSingleArticle()
    {
        return $this->is_single_article;
    }
    
    /**
     * Get layout type
     */
    public function getLayoutType()
    {
        return $this->layout_type;
    }
}
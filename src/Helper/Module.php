<?php
namespace ModMandantenbrief\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

class Module
{
    private $module;
    private $params;
    private $config;
    private $content;
    private $articles;
    private $show_title;
    private $module_title;
    private $show_date;
    private $show_excerpt;
    private $fallback_image;
    private $image_height;
    private $show_debug;
    private $is_single_article;
    private $debug_info;
    private $card_classes;
    private $grid_classes;
    private $responsive_classes;
    private $wrapper_classes;

    public function __construct($module, $params)
    {
        $this->module = $module;
        $this->params = $params;
        $this->loadParameters();
        $this->processContent();
    }

    private function loadParameters()
    {
        // PARAMETER LADEN
        $infodienst_url = $this->params->get('infodienst_url', '');
        $this->show_title = (int)$this->params->get('show_title', 1);
        $this->module_title = $this->params->get('module_title', 'Aktuelle Steuerinformationen');
        $max_articles = (int)$this->params->get('max_articles', 12);
        $layout_style = $this->params->get('layout_style', 'grid');
        $grid_gap = $this->params->get('grid_gap', 'medium');
        $responsive_mobile_portrait = (int)$this->params->get('responsive_mobile_portrait', 1);
        $responsive_tablet = (int)$this->params->get('responsive_tablet', 2);
        $responsive_desktop = (int)$this->params->get('responsive_desktop', 3);
        $card_style = $this->params->get('card_style', 'default');
        $this->show_date = (int)$this->params->get('show_date', 1);
        $this->show_excerpt = (int)$this->params->get('show_excerpt', 1);
        $this->fallback_image = $this->params->get('fallback_image', '/images/mandantenbrief/fallback.png');
        $enable_image_extraction = (int)$this->params->get('enable_image_extraction', 1);
        $this->image_height = (int)$this->params->get('image_height', 200);
        $this->show_debug = (int)$this->params->get('show_debug', 0);
        $this->wrapper_classes = $this->params->get('wrapper_margin', 'uk-margin');

        // CSS-KLASSEN - YOOTHEME KOMPATIBEL
        $this->card_classes = 'uk-card uk-card-' . $card_style . ' uk-card-body uk-light';
        $this->grid_classes = 'uk-grid uk-grid-' . $grid_gap;
        $this->responsive_classes = "uk-child-width-1-{$responsive_mobile_portrait} uk-child-width-1-{$responsive_tablet}@m uk-child-width-1-{$responsive_desktop}@l";

        // PFAD-SETUP - KORRIGIERT BASIEREND AUF AKEEBA-INFO
        $uri = Uri::getInstance();
        $joomla_subfolder = '';
        if (strpos($uri->getPath(), '/khs-steuerberater/') !== false) {
            $joomla_subfolder = '/khs-steuerberater';
        }

        if (strpos($this->fallback_image, '/images/') === 0) {
            $this->fallback_image = $joomla_subfolder . $this->fallback_image;
        }

        // Store for processing
        $this->config = array(
            'infodienst_url' => $infodienst_url,
            'max_articles' => $max_articles,
            'enable_image_extraction' => $enable_image_extraction,
            'joomla_subfolder' => $joomla_subfolder,
            // ✅ KORRIGIERTER CACHE-PFAD BASIEREND AUF AKEEBA
            'server_root' => '/var/customers/webs/djumla/djumla.dev',
            'joomla_install' => '/khs-steuerberater'
        );
    }

    private function processContent()
    {
        try {
            // PAGE HANDLING
            $app = Factory::getApplication();
            $page = $app->input->get('page', '', 'string');
            if (!empty($page) && $page[0] !== '/') {
                $page = '/' . $page;
            }
            $this->is_single_article = (strpos($page, '/text/') !== false);

            // CONTENT LOADING
            $fetch_url = $this->config['infodienst_url'] . ($page ?: '');

            // DEBUG INFO
            $this->debug_info = array(
                'timestamp' => date('H:i:s'),
                'fetch_url' => $fetch_url,
                'page' => $page,
                'is_single_article' => $this->is_single_article ? 'JA' : 'NEIN',
                'content_loaded' => false,
                'content_length' => 0,
                'found_links' => 0,
                'image_extraction_enabled' => $this->config['enable_image_extraction'] ? 'JA' : 'NEIN',
                'fallback_image_path' => $this->fallback_image,
                'server_root' => $this->config['server_root'],
                'joomla_install' => $this->config['joomla_install'],
                'image_debug' => array()
            );

            if (!empty($fetch_url)) {
                $this->content = @file_get_contents($fetch_url);
                $this->debug_info['content_loaded'] = !empty($this->content);
                $this->debug_info['content_length'] = strlen($this->content ?: '');
            }

            $current_url = Uri::getInstance()->toString(array('scheme', 'host', 'path'));

            // LINK-REPLACEMENT
            if (!empty($this->content)) {
                $replacements = array(
                    'http://www.meine-steuer.com/aktuelles.php?page=' => $current_url . '?page=',
                    'www.meine-steuer.com/aktuelles.php?page=' => $current_url . '?page=',
                    'aktuelles.php?page=' => $current_url . '?page='
                );
                foreach ($replacements as $search => $replace) {
                    $this->content = str_replace($search, $replace, $this->content);
                }
            }

            // PARSE ARTICLES
            if (!$this->is_single_article && !empty($this->content)) {
                $this->parseArticles();
            }
        } catch (\Exception $e) {
            $this->debug_info['exception'] = $e->getMessage();
        }
    }

    private function parseArticles()
    {
        // MOSES_INDEX_ITEM PARSING
        if (preg_match_all('/class="moses_index_item"[^>]*>(.*?)<\/div>/s', $this->content, $matches)) {
            $this->articles = array();
            $processed_count = 0;

            foreach ($matches[1] as $item_content) {
                if ($processed_count >= $this->config['max_articles']) {
                    break;
                }

                $article = $this->parseArticleItem($item_content);
                if ($article) {
                    $this->articles[] = $article;
                    $processed_count++;
                }
            }

            $this->debug_info['found_links'] = count($this->articles);
        }
    }

    private function parseArticleItem($item_content)
    {
        $article = array(
            'title' => '',
            'link' => '',
            'date' => '',
            'excerpt' => '',
            'image' => $this->fallback_image,
            'has_image' => false
        );

        // TITLE UND LINK EXTRAHIEREN
        if (preg_match('/<a[^>]+href=["\']([^"\']+)["\'][^>]*>(.*?)<\/a>/s', $item_content, $link_matches)) {
            $article['link'] = htmlspecialchars_decode($link_matches[1]);
            $article['title'] = strip_tags($link_matches[2]);
        }

        // DATUM EXTRAHIEREN
        if (preg_match('/(\d{2}\.\d{2}\.\d{4})/', $item_content, $date_matches)) {
            $article['date'] = $date_matches[1];
        }

        // TEXT CONTENT
        $text_content = strip_tags($item_content);
        $text_content = preg_replace('/\s+/', ' ', $text_content);
        $text_content = trim($text_content);

        if (!empty($text_content)) {
            $sentences = preg_split('/[.!?]+/', $text_content);
            $excerpt_parts = array();
            $char_count = 0;

            foreach ($sentences as $sentence) {
                $sentence = trim($sentence);
                if (empty($sentence)) continue;

                if ($char_count + strlen($sentence) > 150) {
                    break;
                }
                $excerpt_parts[] = $sentence;
                $char_count += strlen($sentence) + 2; // +2 für ". "
            }

            if (!empty($excerpt_parts)) {
                $article['excerpt'] = implode('. ', $excerpt_parts) . '.';
            }
        }

        // IMAGE EXTRACTION
        if ($this->config['enable_image_extraction'] && !empty($article['link'])) {
            $extracted_image = $this->extractImageFromLink($article['link']);
            if ($extracted_image) {
                $article['image'] = $extracted_image;
                $article['has_image'] = true;
            }
        }

        return (!empty($article['title']) && !empty($article['link'])) ? $article : null;
    }

    private function extractImageFromLink($link)
    {
        try {
            $page_content = @file_get_contents($link);
            if (!$page_content) return null;

            // SUCHE NACH BILDERN IN VERSCHIEDENEN FORMATEN
            $image_patterns = array(
                '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
                '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\'][^>]*>/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\'][^>]*>/i'
            );

            foreach ($image_patterns as $pattern) {
                if (preg_match($pattern, $page_content, $img_matches)) {
                    $img_url = $img_matches[1];
                    
                    // RELATIVE URLS KORRIGIEREN
                    if (strpos($img_url, 'http') !== 0) {
                        $base_url = preg_replace('/\/[^\/]*$/', '/', $link);
                        $img_url = $base_url . ltrim($img_url, '/');
                    }

                    // IMAGE VALIDATION
                    if ($this->isValidImage($img_url)) {
                        return $img_url;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->debug_info['image_debug'][] = "Fehler bei Bildextraktion für {$link}: " . $e->getMessage();
        }

        return null;
    }

    private function isValidImage($url)
    {
        $valid_extensions = array('.jpg', '.jpeg', '.png', '.gif', '.webp');
        $url_lower = strtolower($url);
        
        foreach ($valid_extensions as $ext) {
            if (strpos($url_lower, $ext) !== false) {
                return true;
            }
        }
        return false;
    }

    // PUBLIC METHODS
    public function getArticles()
    {
        return $this->articles ?: array();
    }

    public function getContent()
    {
        return $this->content ?: '';
    }

    public function getModuleTitle()
    {
        return $this->show_title ? $this->module_title : '';
    }

    public function showDate()
    {
        return $this->show_date;
    }

    public function showExcerpt()
    {
        return $this->show_excerpt;
    }

    public function getFallbackImage()
    {
        return $this->fallback_image;
    }

    public function getImageHeight()
    {
        return $this->image_height;
    }

    public function showDebug()
    {
        return $this->show_debug;
    }

    public function getDebugInfo()
    {
        return $this->debug_info ?: array();
    }

    public function isSingleArticle()
    {
        return $this->is_single_article;
    }

    public function getCardClasses()
    {
        return $this->card_classes;
    }

    public function getGridClasses()
    {
        return $this->grid_classes;
    }

    public function getResponsiveClasses()
    {
        return $this->responsive_classes;
    }

    public function getWrapperClasses()
    {
        return $this->wrapper_classes;
    }
}

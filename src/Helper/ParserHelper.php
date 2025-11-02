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

use Joomla\CMS\Uri\Uri;

/**
 * Enhanced Content Parser Helper
 */
class ParserHelper
{
    const EXCERPT_LENGTH = 200;
    const MIN_IMAGE_WIDTH = 200;
    
    /**
     * Parse index page with moses_index_item articles
     */
    public function parseIndexPage($content, $config, $cache)
    {
        $articles = array();
        
        // Parse moses_index_item pattern
        $pattern = '/class="moses_index_item"[^>]*>(.*?)<\/div>/s';
        if (!preg_match_all($pattern, $content, $matches)) {
            return $articles;
        }
        
        foreach ($matches[1] as $index => $item_html) {
            $article = $this->parseIndexItem($item_html, $config, $cache, $index);
            if ($article) {
                $articles[] = $article;
            }
        }
        
        return $articles;
    }
    
    /**
     * Parse single moses_index_item
     */
    private function parseIndexItem($html, $config, $cache, $index)
    {
        $article = array(
            'title' => '',
            'excerpt' => '',
            'link' => '',
            'date' => '',
            'image' => '',
            'cached_image' => '',
            'debug' => array()
        );
        
        // Extract title
        if (preg_match('/<a[^>]*>([^<]+)<\/a>/', $html, $title_match)) {
            $article['title'] = trim(strip_tags($title_match[1]));
        }
        
        // Extract link
        if (preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/', $html, $link_match)) {
            $article['link'] = $link_match[1];
        }
        
        // Extract H4 teaser
        if (preg_match('/<h4[^>]*>([^<]+)<\/h4>/', $html, $h4_match)) {
            $article['excerpt'] = trim(strip_tags($h4_match[1]));
        } else {
            // Fallback: extract text content
            $article['excerpt'] = $this->extractExcerpt($html);
        }
        
        // Extract and cache image
        $original_image = $this->extractFeaturedImage($html);
        if ($original_image) {
            $cached_result = $cache->cacheImage($original_image);
            $article['image'] = $original_image;
            $article['cached_image'] = $cached_result['cached_url'] ?: '';
            $article['debug']['image_cache'] = $cached_result['debug'];
        }
        
        // Extract date (if available)
        if (preg_match('/([0-9]{1,2}\.[0-9]{1,2}\.[0-9]{4})/', $html, $date_match)) {
            $article['date'] = $date_match[1];
        }
        
        return !empty($article['title']) ? $article : null;
    }
    
    /**
     * Parse tools page with H3 structure
     */
    public function parseToolsPage($content, $config)
    {
        $tools = array();
        
        // Find all H3 headings with following content
        $pattern = '/<h3[^>]*>([^<]+)<\/h3>\s*([\s\S]*?)(?=<h3|<\/body|$)/i';
        if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            return $tools;
        }
        
        foreach ($matches as $match) {
            $title = trim(strip_tags($match[1]));
            $content_block = $match[2];
            
            // Extract description from following paragraphs
            $description = '';
            if (preg_match('/<p[^>]*>([^<]+)<\/p>/', $content_block, $desc_match)) {
                $description = trim(strip_tags($desc_match[1]));
            }
            
            // Extract link if available
            $link = '';
            if (preg_match('/<a[^>]*href=["\']([^"\']+)["\'][^>]*>/', $content_block, $link_match)) {
                $link = $link_match[1];
            }
            
            if (!empty($title)) {
                $tools[] = array(
                    'title' => $title,
                    'excerpt' => $description,
                    'link' => $link,
                    'type' => 'tool',
                    'image' => '',
                    'cached_image' => '',
                    'date' => ''
                );
            }
        }
        
        return $tools;
    }
    
    /**
     * Extract featured image from HTML
     */
    public function extractFeaturedImage($html, $min_width = self::MIN_IMAGE_WIDTH)
    {
        if (empty($html)) {
            return null;
        }
        
        $html = htmlspecialchars_decode($html, ENT_QUOTES);
        
        // Find img tags
        $pattern = '/<img[^>]*\s+src=["\']([^"\']+)["\'][^>]*>/i';
        if (!preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            return null;
        }
        
        foreach ($matches as $match) {
            $src = $match[1];
            $full_tag = $match[0];
            
            // Skip data URIs, SVGs, and small icons
            if (strpos($src, 'data:') === 0 || 
                strpos($src, '.svg') !== false ||
                strpos($src, 'icon') !== false) {
                continue;
            }
            
            // Check width attribute if present
            if (preg_match('/width=["\']?(\d+)["\']?/i', $full_tag, $width_match)) {
                $width = (int)$width_match[1];
                if ($width < $min_width) {
                    continue;
                }
            }
            
            // Validate and return first suitable image
            if ($this->isValidImageUrl($src)) {
                return $src;
            }
        }
        
        return null;
    }
    
    /**
     * Extract title from HTML
     */
    public function extractTitle($html)
    {
        if (empty($html)) {
            return null;
        }
        
        $html = htmlspecialchars_decode($html, ENT_QUOTES);
        
        // Try headings in order
        $headings = ['h1', 'h2', 'h3', 'h4'];
        foreach ($headings as $tag) {
            $pattern = '/<' . $tag . '[^>]*>([^<]+)<\/' . $tag . '>/i';
            if (preg_match($pattern, $html, $match)) {
                $title = trim(strip_tags($match[1]));
                if (!empty($title)) {
                    return $title;
                }
            }
        }
        
        // Fallback: first link text
        if (preg_match('/<a[^>]*>([^<]+)<\/a>/', $html, $match)) {
            $title = trim(strip_tags($match[1]));
            if (!empty($title)) {
                return $title;
            }
        }
        
        return null;
    }
    
    /**
     * Extract excerpt from HTML
     */
    public function extractExcerpt($html, $length = self::EXCERPT_LENGTH)
    {
        if (empty($html)) {
            return null;
        }
        
        // Remove scripts and styles
        $text = preg_replace('/<(script|style)[^>]*>.*?<\/\1>/si', '', $html);
        
        // Remove HTML tags
        $text = strip_tags($text);
        
        // Clean whitespace
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (empty($text)) {
            return null;
        }
        
        // Truncate at word boundary
        if (strlen($text) > $length) {
            $text = substr($text, 0, $length);
            $last_space = strrpos($text, ' ');
            if ($last_space !== false) {
                $text = substr($text, 0, $last_space);
            }
            $text .= '...';
        }
        
        return $text;
    }
    
    /**
     * Validate image URL
     */
    private function isValidImageUrl($url)
    {
        if (empty($url)) {
            return false;
        }
        
        // Check for valid image extensions
        $valid_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
        
        return in_array($extension, $valid_extensions) || 
               strpos($url, '/image') !== false ||
               preg_match('/\.(jpg|jpeg|png|gif|webp)(\?|$)/i', $url);
    }
    
    /**
     * Clean and validate URL
     */
    public function cleanUrl($url)
    {
        if (empty($url)) {
            return '';
        }
        
        // Remove multiple slashes
        $url = preg_replace('/([^:])(\/+)/', '$1/', $url);
        
        // Ensure proper protocol
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        } elseif (strpos($url, '/') === 0) {
            // Relative URL - need base URL
            $base = 'https://onlineinfodienst.de';
            $url = $base . $url;
        }
        
        return $url;
    }
}
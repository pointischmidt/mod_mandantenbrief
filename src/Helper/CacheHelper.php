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

use Joomla\CMS\Log\Log;

/**
 * Robust Image Cache Helper
 */
class CacheHelper
{
    const CACHE_DIR = '/images/mandantenbrief/cache';
    const FALLBACK_IMAGE = '/images/mandantenbrief/fallback.png';
    const MAX_FILE_SIZE = 5242880; // 5MB
    const DOWNLOAD_TIMEOUT = 5; // seconds
    
    private $cache_ttl;
    private $config;
    
    public function __construct($cache_ttl_days = 7)
    {
        $this->cache_ttl = $cache_ttl_days * 86400; // Convert to seconds
        $this->ensureCacheDir();
    }
    
    /**
     * Ensure cache directory exists
     */
    private function ensureCacheDir()
    {
        $cache_path = JPATH_ROOT . self::CACHE_DIR;
        
        if (!is_dir($cache_path)) {
            if (!@mkdir($cache_path, 0755, true)) {
                Log::add('Cache directory could not be created: ' . $cache_path, Log::ERROR, 'mod_mandantenbrief');
                return false;
            }
        }
        
        return is_writable($cache_path);
    }
    
    /**
     * Generate hash filename for cache
     */
    private function getHashFilename($url)
    {
        $hash = substr(sha1($url), 0, 10);
        $extension = $this->getImageExtension($url);
        return 'cache_' . $hash . '.' . $extension;
    }
    
    /**
     * Extract image extension from URL
     */
    private function getImageExtension($url)
    {
        $parsed_url = parse_url($url);
        $path = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        return in_array($ext, $allowed) ? ($ext === 'jpeg' ? 'jpg' : $ext) : 'jpg';
    }
    
    /**
     * Main function: Cache or retrieve image
     */
    public function cacheImage($image_url, $fallback_on_error = true)
    {
        $debug = array(
            'original_url' => $image_url,
            'cached_url' => '',
            'cache_hit' => false,
            'download_attempted' => false,
            'download_success' => false,
            'error' => ''
        );
        
        // Security checks
        if (empty($image_url)) {
            $debug['error'] = 'Empty image URL';
            return array(
                'cached_url' => $fallback_on_error ? $this->getFallbackImage() : null,
                'debug' => $debug
            );
        }
        
        // Handle relative URLs
        if (strpos($image_url, 'http') !== 0 && strpos($image_url, '//') !== 0) {
            if (file_exists(JPATH_ROOT . $image_url)) {
                $debug['cached_url'] = $image_url;
                $debug['cache_hit'] = true;
                return array('cached_url' => $image_url, 'debug' => $debug);
            }
            
            $debug['error'] = 'Local file not found';
            return array(
                'cached_url' => $fallback_on_error ? $this->getFallbackImage() : null,
                'debug' => $debug
            );
        }
        
        $cache_filename = $this->getHashFilename($image_url);
        $cache_path = JPATH_ROOT . self::CACHE_DIR . '/' . $cache_filename;
        $cache_url = self::CACHE_DIR . '/' . $cache_filename;
        
        // Check if cache exists and is valid
        if (file_exists($cache_path)) {
            $file_age = time() - filemtime($cache_path);
            
            if ($file_age < $this->cache_ttl) {
                $debug['cached_url'] = $cache_url;
                $debug['cache_hit'] = true;
                return array('cached_url' => $cache_url, 'debug' => $debug);
            }
            
            // Cache too old - delete
            @unlink($cache_path);
        }
        
        // Download external file
        $debug['download_attempted'] = true;
        if ($this->downloadImage($image_url, $cache_path)) {
            $debug['download_success'] = true;
            $debug['cached_url'] = $cache_url;
            return array('cached_url' => $cache_url, 'debug' => $debug);
        }
        
        // Download failed
        $debug['error'] = 'Download failed';
        return array(
            'cached_url' => $fallback_on_error ? $this->getFallbackImage() : null,
            'debug' => $debug
        );
    }
    
    /**
     * Download external image file
     */
    private function downloadImage($url, $destination)
    {
        // Try cURL first
        if (function_exists('curl_init')) {
            return $this->downloadWithCurl($url, $destination);
        }
        
        // Fallback to file_get_contents
        return $this->downloadWithFileGetContents($url, $destination);
    }
    
    /**
     * Download with cURL
     */
    private function downloadWithCurl($url, $destination)
    {
        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::DOWNLOAD_TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_USERAGENT => 'Mozilla/5.0 (Mandantenbrief-Cache/1.0)',
                CURLOPT_MAXFILESIZE => self::MAX_FILE_SIZE,
            ]);
            
            $data = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            if ($http_code !== 200 || empty($data) || !empty($error)) {
                return false;
            }
            
            // Size check
            if (strlen($data) > self::MAX_FILE_SIZE) {
                return false;
            }
            
            // Write to file
            if (file_put_contents($destination, $data) === false) {
                return false;
            }
            
            return $this->validateImageMime($destination);
            
        } catch (\Exception $e) {
            Log::add('cURL download error: ' . $e->getMessage(), Log::WARNING, 'mod_mandantenbrief');
            return false;
        }
    }
    
    /**
     * Download with file_get_contents
     */
    private function downloadWithFileGetContents($url, $destination)
    {
        try {
            $context = stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
                'http' => [
                    'timeout' => self::DOWNLOAD_TIMEOUT,
                    'max_redirects' => 3,
                    'user_agent' => 'Mozilla/5.0 (Mandantenbrief-Cache/1.0)',
                ]
            ]);
            
            $data = @file_get_contents($url, false, $context);
            
            if ($data === false || empty($data)) {
                return false;
            }
            
            // Size check
            if (strlen($data) > self::MAX_FILE_SIZE) {
                return false;
            }
            
            // Write to file
            if (file_put_contents($destination, $data) === false) {
                return false;
            }
            
            return $this->validateImageMime($destination);
            
        } catch (\Exception $e) {
            Log::add('file_get_contents download error: ' . $e->getMessage(), Log::WARNING, 'mod_mandantenbrief');
            return false;
        }
    }
    
    /**
     * Validate image MIME type
     */
    private function validateImageMime($filepath)
    {
        if (!file_exists($filepath)) {
            return false;
        }
        
        // Use getimagesize if available
        if (function_exists('getimagesize')) {
            $info = @getimagesize($filepath);
            if (is_array($info) && isset($info[2])) {
                // Valid image types: JPEG, PNG, GIF, WebP
                return in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP]);
            }
        }
        
        // Fallback: Check file header
        $handle = fopen($filepath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 12);
        fclose($handle);
        
        // Check magic bytes
        if (substr($header, 0, 3) === "\xFF\xD8\xFF" || // JPEG
            substr($header, 0, 4) === "\x89PNG" ||       // PNG
            substr($header, 0, 6) === "GIF87a" ||        // GIF87a
            substr($header, 0, 6) === "GIF89a" ||        // GIF89a
            substr($header, 8, 4) === "WEBP") {          // WebP
            return true;
        }
        
        // Invalid image - delete file
        @unlink($filepath);
        return false;
    }
    
    /**
     * Get fallback image path
     */
    public function getFallbackImage()
    {
        $fallback = JPATH_ROOT . self::FALLBACK_IMAGE;
        
        if (file_exists($fallback)) {
            return self::FALLBACK_IMAGE;
        }
        
        // Fallback doesn't exist - log error
        Log::add('Fallback image not found: ' . $fallback, Log::ERROR, 'mod_mandantenbrief');
        
        // Return placeholder URL
        return 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPk5vIEltYWdlPC90ZXh0Pjwvc3ZnPg==';
    }
    
    /**
     * Clear cache (admin function)
     */
    public function clearCache()
    {
        $cache_path = JPATH_ROOT . self::CACHE_DIR;
        
        if (!is_dir($cache_path)) {
            return false;
        }
        
        $files = glob($cache_path . '/cache_*.{jpg,png,gif,webp}', GLOB_BRACE);
        
        $deleted = 0;
        foreach ($files as $file) {
            if (@unlink($file)) {
                $deleted++;
            }
        }
        
        return $deleted;
    }
    
    /**
     * Get cache statistics
     */
    public function getCacheStats()
    {
        $cache_path = JPATH_ROOT . self::CACHE_DIR;
        
        if (!is_dir($cache_path)) {
            return ['files' => 0, 'size' => 0, 'oldest' => null, 'newest' => null];
        }
        
        $files = glob($cache_path . '/cache_*.{jpg,png,gif,webp}', GLOB_BRACE);
        $size = 0;
        $oldest = null;
        $newest = null;
        
        foreach ($files as $file) {
            $filesize = filesize($file);
            $filemtime = filemtime($file);
            
            $size += $filesize;
            
            if ($oldest === null || $filemtime < $oldest) {
                $oldest = $filemtime;
            }
            
            if ($newest === null || $filemtime > $newest) {
                $newest = $filemtime;
            }
        }
        
        return [
            'files' => count($files),
            'size' => round($size / (1024 * 1024), 2), // MB
            'oldest' => $oldest ? date('Y-m-d H:i:s', $oldest) : null,
            'newest' => $newest ? date('Y-m-d H:i:s', $newest) : null
        ];
    }
}
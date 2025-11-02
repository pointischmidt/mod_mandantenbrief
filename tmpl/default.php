<?php
/**
 * @package     mod_mandantenbrief
 * @subpackage  site
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// Module instance und UI-Klassen
$module_title = $helper->getModuleTitle();
$articles = $helper->getArticles();
$content = $helper->getContent();
$is_single = $helper->isSingleArticle();
$show_date = $helper->showDate();
$show_excerpt = $helper->showExcerpt();
$show_debug = $helper->showDebug();
$debug_info = $helper->getDebugInfo();
$card_classes = $helper->getCardClasses();
$grid_classes = $helper->getGridClasses();
$responsive_classes = $helper->getResponsiveClasses();
$wrapper_classes = $helper->getWrapperClasses();

// DEBUG AUSGABE
if ($show_debug && !empty($debug_info)) {
    echo '<div class="uk-alert uk-alert-warning uk-margin">';
    echo '<h3>🐛 Debug Informationen</h3>';
    echo '<div class="uk-grid uk-grid-small uk-child-width-1-2@m" uk-grid>';
    
    foreach ($debug_info as $key => $value) {
        if (is_array($value)) continue;
        echo '<div>';
        echo '<strong>' . ucwords(str_replace('_', ' ', $key)) . ':</strong><br>';
        echo '<code>' . htmlspecialchars(is_bool($value) ? ($value ? 'JA' : 'NEIN') : (string)$value) . '</code>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // EINZELARTIKEL DEBUG
    if (isset($debug_info['image_debug']) && !empty($debug_info['image_debug'])) {
        echo '<h4>Bild-Debug:</h4>';
        echo '<ul>';
        foreach ($debug_info['image_debug'] as $img_debug) {
            echo '<li>' . htmlspecialchars($img_debug) . '</li>';
        }
        echo '</ul>';
    }
    
    echo '</div>';
}

// EINZELARTIKEL-ANZEIGE
if ($is_single) {
    if (!empty($content)) {
        echo '<div class="mandantenbrief-single ' . $wrapper_classes . '">';
        echo $content;
        echo '</div>';
    }
    return;
}

// MODULE TITLE
if (!empty($module_title)) {
    echo '<h2 class="uk-heading-line uk-text-center"><span>' . htmlspecialchars($module_title) . '</span></h2>';
}

// KEIN INHALT
if (empty($articles)) {
    echo '<div class="uk-alert uk-alert-primary">';
    echo '<p>Keine Artikel gefunden oder Verbindung zum Infodienst nicht möglich.</p>';
    echo '</div>';
    return;
}

// ARTIKEL-GRID AUSGABE
echo '<div class="mandantenbrief-wrapper ' . $wrapper_classes . '">';
echo '<div class="' . $grid_classes . ' ' . $responsive_classes . '" uk-grid uk-height-match="target: > div > .uk-card">';

foreach ($articles as $index => $article) {
    echo '<div>';
    echo '<div class="' . $card_classes . ' uk-card-hover uk-height-1-1">';
    
    // ARTIKEL-BILD
    if (!empty($article['image']) && $article['image'] !== $helper->getFallbackImage()) {
        echo '<div class="uk-card-media-top uk-overflow-hidden uk-position-relative">';
        echo '<div class="uk-transition-toggle" tabindex="0">';
        
        echo '<img src="' . htmlspecialchars($article['image']) . '" ';
        echo 'alt="' . htmlspecialchars($article['title']) . '" ';
        echo 'class="uk-width-1-1 uk-transition-scale-up uk-transition-opaque" ';
        echo 'style="height: ' . $helper->getImageHeight() . 'px; object-fit: cover;" ';
        echo 'loading="lazy" uk-img>';
        
        // OVERLAY bei Hover
        if (!empty($article['link'])) {
            echo '<div class="uk-position-cover uk-transition-fade uk-background-primary uk-light uk-flex uk-flex-center uk-flex-middle" style="background: rgba(0,0,0,.5);">';
            echo '<span class="uk-button uk-button-default uk-button-small uk-border-rounded">Artikel lesen</span>';
            echo '</div>';
        }
        
        echo '</div></div>';
    }
    
    // ARTIKEL-INHALT
    echo '<div class="uk-card-body">';
    
    // Titel
    if (!empty($article['title'])) {
        echo '<h3 class="uk-card-title uk-margin-remove-bottom uk-text-bold">';
        if (!empty($article['link'])) {
            echo '<a href="' . htmlspecialchars($article['link']) . '" class="uk-link-reset">';
            echo htmlspecialchars($article['title']);
            echo '</a>';
        } else {
            echo htmlspecialchars($article['title']);
        }
        echo '</h3>';
    }
    
    // Datum
    if ($show_date && !empty($article['date'])) {
        echo '<p class="uk-article-meta uk-margin-small-top uk-margin-remove-bottom">';
        echo '<time class="uk-text-muted uk-text-small">';
        echo '<span uk-icon="icon: calendar; ratio: 0.8" class="uk-margin-small-right"></span>';
        echo htmlspecialchars($article['date']);
        echo '</time>';
        echo '</p>';
    }
    
    // Excerpt
    if ($show_excerpt && !empty($article['excerpt'])) {
        echo '<div class="uk-margin-small-top">';
        echo '<p class="uk-text-small uk-text-muted uk-margin-remove">';
        echo htmlspecialchars($article['excerpt']);
        echo '</p>';
        echo '</div>';
    }
    
    // Read More Link
    if (!empty($article['link'])) {
        echo '<div class="uk-margin-small-top">';
        echo '<a href="' . htmlspecialchars($article['link']) . '" ';
        echo 'class="uk-button uk-button-text uk-button-small uk-text-primary">';
        echo 'Weiterlesen <span uk-icon="icon: arrow-right; ratio: 0.8" class="uk-margin-small-left"></span>';
        echo '</a>';
        echo '</div>';
    }
    
    echo '</div>'; // uk-card-body
    echo '</div>'; // uk-card
    echo '</div>'; // grid item
}

echo '</div>'; // grid
echo '</div>'; // wrapper
?>

<style>
/* YOOTHEME INTEGRATION - INLINE CSS FÜR BESSERE KOMPATIBILITÄT */
.mandantenbrief-wrapper {
    --uk-card-default-background: var(--uk-background-default, #fff);
    --uk-card-default-color: var(--uk-color-default, #666);
    --uk-card-default-title-color: var(--uk-color-emphasis, #333);
    --uk-card-default-border: var(--uk-border-width, 1px) solid var(--uk-border, #e5e5e5);
    --uk-card-default-border-radius: var(--uk-border-radius, 4px);
    
    --uk-card-primary-background: var(--uk-background-primary, #1e87f0);
    --uk-card-primary-color: var(--uk-color-primary, #fff);
    
    --uk-card-secondary-background: var(--uk-background-secondary, #222);
    --uk-card-secondary-color: var(--uk-color-secondary, #fff);
}

/* RESPONSIVE GRID IMPROVEMENTS */
.mandantenbrief-wrapper .uk-grid > * {
    margin-bottom: var(--uk-grid-gutter-vertical, 30px);
}

/* CARD HOVER EFFECTS */
.mandantenbrief-wrapper .uk-card-hover {
    transition: all 0.3s ease;
}

.mandantenbrief-wrapper .uk-card-hover:hover {
    transform: translateY(-5px);
    box-shadow: var(--uk-box-shadow-large, 0 14px 25px rgba(0,0,0,.16));
}

/* IMAGE ASPECT RATIO */
.mandantenbrief-wrapper .uk-card-media-top {
    position: relative;
    overflow: hidden;
    background: var(--uk-background-muted, #f8f8f8);
}

/* TITLE LINK STYLING */
.mandantenbrief-wrapper .uk-card-title .uk-link-reset {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.mandantenbrief-wrapper .uk-card-title .uk-link-reset:hover {
    color: var(--uk-color-primary, #1e87f0);
}

/* BUTTON STYLING */
.mandantenbrief-wrapper .uk-button-text {
    color: var(--uk-color-primary, #1e87f0);
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0;
}

.mandantenbrief-wrapper .uk-button-text:hover {
    color: var(--uk-color-primary-hover, #0f7ae5);
    text-decoration: none;
}

/* METADATA STYLING */
.mandantenbrief-wrapper .uk-article-meta {
    font-size: 0.8rem;
    color: var(--uk-text-muted, #999);
}

/* DEBUG SECTION */
.mandantenbrief-wrapper .uk-alert-warning {
    background: var(--uk-background-warning, #fdf6e3);
    border: 1px solid var(--uk-border-warning, #f4e5a5);
    color: var(--uk-color-warning, #8d6e06);
}

/* DARK MODE SUPPORT */
@media (prefers-color-scheme: dark) {
    .mandantenbrief-wrapper {
        --uk-card-default-background: #2b2b2b;
        --uk-card-default-color: #ccc;
        --uk-card-default-title-color: #fff;
        --uk-card-default-border: 1px solid #444;
    }
}

/* RESPONSIVE IMPROVEMENTS */
@media (max-width: 640px) {
    .mandantenbrief-wrapper .uk-card-body {
        padding: 20px 15px;
    }
    
    .mandantenbrief-wrapper .uk-card-title {
        font-size: 1rem;
        line-height: 1.3;
    }
    
    .mandantenbrief-wrapper .uk-text-small {
        font-size: 0.8rem;
    }
}

/* ACCESSIBILITY */
.mandantenbrief-wrapper .uk-card:focus,
.mandantenbrief-wrapper .uk-card-title a:focus {
    outline: 2px solid var(--uk-color-primary, #1e87f0);
    outline-offset: 2px;
}

/* LOADING STATE (falls Bilder noch laden) */
.mandantenbrief-wrapper img[uk-img]:not([src]) {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

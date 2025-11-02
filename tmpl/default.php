<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_mandantenbrief
 *
 * @copyright   Copyright (C) 2025 djumla.dev
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// Load UIkit if not already loaded (YOOtheme compatibility)
if (!defined('UIKIT_LOADED')) {
    HTMLHelper::_('script', 'media/system/js/core.js', ['version' => 'auto']);
    define('UIKIT_LOADED', true);
}

// Get module classes and data
$moduleClasses = $moduleInstance->getModuleClasses();
$articles = $moduleInstance->getArticles();
$debugInfo = $moduleInstance->getDebugInfo();
$layoutType = $moduleInstance->getLayoutType();
$isSingleArticle = $moduleInstance->isSingleArticle();

// Container classes
$containerClasses = implode(' ', $moduleClasses['container']);

// Debug output (if enabled)
if (!empty($debugInfo)) {
    echo '<div class="uk-alert uk-alert-warning uk-margin-small">';
    echo '<h5 class="uk-heading-bullet">Debug Info</h5>';
    echo '<div class="uk-grid uk-grid-small" uk-grid>';
    
    foreach ($debugInfo as $key => $value) {
        if ($key === 'show_elements') continue; // Skip long arrays
        echo '<div class="uk-width-1-2@s">';
        echo '<strong>' . ucwords(str_replace('_', ' ', $key)) . ':</strong> ';
        echo is_bool($value) ? ($value ? 'JA' : 'NEIN') : htmlspecialchars($value);
        echo '</div>';
    }
    
    echo '</div></div>';
}

// Single article view
if ($isSingleArticle) {
    $content = $moduleInstance->getSingleArticleContent();
    if (!empty($content)) {
        echo '<div class="uk-container ' . $containerClasses . '">';
        echo $content;
        echo '</div>';
    }
    return;
}

// No articles found
if (empty($articles)) {
    echo '<div class="uk-alert uk-alert-primary">';
    echo '<p>' . Text::_('MOD_MANDANTENBRIEF_NO_ARTICLES') . '</p>';
    echo '</div>';
    return;
}

// Article grid/list layout
?>
<div class="mod-mandantenbrief <?php echo $containerClasses; ?>">
    
    <?php if ($layoutType === 'grid'): ?>
        <!-- Grid Layout -->
        <div class="<?php echo implode(' ', $moduleClasses['grid']); ?>" uk-grid uk-height-match="target: .uk-card">
            <?php foreach ($articles as $article): ?>
                <div>
                    <?php if ($moduleInstance->showElement('image') && !empty($article['cached_image'])): ?>
                        <!-- Article with Image -->
                        <div class="<?php echo implode(' ', $moduleClasses['card']); ?> uk-card-hover">
                            <div class="uk-card-media-top uk-overflow-hidden">
                                <div class="uk-position-relative uk-display-block uk-transition-toggle" tabindex="0">
                                    <img src="<?php echo htmlspecialchars($article['cached_image']); ?>" 
                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                         class="uk-width-1-1 <?php echo implode(' ', $moduleClasses['image']); ?>"
                                         uk-img loading="lazy">
                                    
                                    <?php if (!empty($article['link'])): ?>
                                        <div class="uk-position-cover uk-transition-fade uk-light uk-flex uk-flex-center uk-flex-middle" style="background: rgba(0,0,0,.3);">
                                            <span class="uk-button uk-button-primary uk-border-rounded">
                                                <?php echo Text::_('MOD_MANDANTENBRIEF_READ_MORE'); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="uk-card-body uk-padding-small">
                                <?php if ($moduleInstance->showElement('title')): ?>
                                    <h3 class="uk-card-title uk-margin-remove-bottom">
                                        <?php if (!empty($article['link'])): ?>
                                            <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                               class="uk-link-reset uk-text-decoration-none">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        <?php endif; ?>
                                    </h3>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('date') && !empty($article['date'])): ?>
                                    <p class="uk-article-meta uk-margin-small-top uk-margin-remove-bottom">
                                        <time class="uk-text-muted uk-text-small">
                                            <span uk-icon="calendar"></span> <?php echo htmlspecialchars($article['date']); ?>
                                        </time>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('excerpt') && !empty($article['excerpt'])): ?>
                                    <div class="uk-margin-small-top">
                                        <p class="uk-text-small uk-margin-remove">
                                            <?php echo htmlspecialchars($article['excerpt']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Article without Image -->
                        <div class="<?php echo implode(' ', $moduleClasses['card']); ?> uk-card-hover">
                            <div class="uk-card-body uk-padding-small">
                                <?php if ($moduleInstance->showElement('title')): ?>
                                    <h3 class="uk-card-title uk-margin-remove-bottom">
                                        <?php if (!empty($article['link'])): ?>
                                            <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                               class="uk-link-reset uk-text-decoration-none">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        <?php endif; ?>
                                    </h3>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('date') && !empty($article['date'])): ?>
                                    <p class="uk-article-meta uk-margin-small-top uk-margin-remove-bottom">
                                        <time class="uk-text-muted uk-text-small">
                                            <span uk-icon="calendar"></span> <?php echo htmlspecialchars($article['date']); ?>
                                        </time>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('excerpt') && !empty($article['excerpt'])): ?>
                                    <div class="uk-margin-small-top">
                                        <p class="uk-text-small uk-margin-remove">
                                            <?php echo htmlspecialchars($article['excerpt']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('readmore') && !empty($article['link'])): ?>
                                    <div class="uk-margin-small-top">
                                        <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                           class="uk-button uk-button-text uk-button-small">
                                            <?php echo Text::_('MOD_MANDANTENBRIEF_READ_MORE'); ?> 
                                            <span uk-icon="arrow-right"></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        
    <?php else: ?>
        <!-- List Layout -->
        <div class="uk-grid uk-grid-small uk-flex-column" uk-grid>
            <?php foreach ($articles as $article): ?>
                <div class="uk-width-1-1">
                    <div class="<?php echo implode(' ', $moduleClasses['card']); ?> uk-card-hover">
                        <div class="uk-grid uk-grid-small uk-flex-middle" uk-grid>
                            
                            <?php if ($moduleInstance->showElement('image') && !empty($article['cached_image'])): ?>
                                <div class="uk-width-auto">
                                    <div class="uk-position-relative uk-transition-toggle" tabindex="0">
                                        <img src="<?php echo htmlspecialchars($article['cached_image']); ?>" 
                                             alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                             class="uk-border-rounded <?php echo implode(' ', $moduleClasses['image']); ?>"
                                             width="80" height="60" uk-img loading="lazy">
                                    </div>
                                </div>
                            <?php endif; ?>
                            
                            <div class="uk-width-expand">
                                <?php if ($moduleInstance->showElement('title')): ?>
                                    <h4 class="uk-margin-remove-bottom">
                                        <?php if (!empty($article['link'])): ?>
                                            <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                               class="uk-link-reset uk-text-decoration-none">
                                                <?php echo htmlspecialchars($article['title']); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo htmlspecialchars($article['title']); ?>
                                        <?php endif; ?>
                                    </h4>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('date') && !empty($article['date'])): ?>
                                    <p class="uk-article-meta uk-margin-small-top uk-margin-remove-bottom">
                                        <time class="uk-text-muted uk-text-small">
                                            <span uk-icon="calendar"></span> <?php echo htmlspecialchars($article['date']); ?>
                                        </time>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($moduleInstance->showElement('excerpt') && !empty($article['excerpt'])): ?>
                                    <p class="uk-text-small uk-margin-small-top uk-margin-remove">
                                        <?php echo htmlspecialchars($article['excerpt']); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($moduleInstance->showElement('readmore') && !empty($article['link'])): ?>
                                <div class="uk-width-auto">
                                    <a href="<?php echo htmlspecialchars($article['link']); ?>" 
                                       class="uk-button uk-button-primary uk-button-small">
                                        <span uk-icon="arrow-right"></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
</div>
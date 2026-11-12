<?php
declare(strict_types=1);

namespace Visacom\Plugin\Content\FlexextArticlesFree\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

/**
 * Content - FlexExt Articles Hub for Joomla 4, 5, and 6.
 * Provides Reading Time indicator, Top Reading Progress Bar, Social Sharing, and Author Box.
 *
 * @package     plg_content_flexext_articles_free
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
final class FlexextArticlesFree extends CMSPlugin implements SubscriberInterface
{
    public const VERSION = '1.0.2';

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead' => 'onBeforeCompileHead',
            'onContentPrepare'    => 'onContentPrepare',
        ];
    }

    public function onBeforeCompileHead($event = null): void
    {
        $app = $this->getApplication() ?: Factory::getApplication();
        $doc = $app->getDocument();

        if (!$doc || !$app->isClient('site')) {
            return;
        }

        $mediaUrl = Uri::root(true) . '/plugins/content/flexext_articles_free/media';
        $doc->addStyleSheet($mediaUrl . '/css/articles-free.css?v=' . self::VERSION);

        if ((bool) $this->params->get('enable_progress_bar', 1)) {
            $doc->addScript($mediaUrl . '/js/articles-free.js?v=' . self::VERSION);
            $barColor = htmlspecialchars((string) $this->params->get('progress_bar_color', '#1980F7'));
            $doc->addStyleDeclaration(':root { --flx-reading-bar-color: ' . $barColor . '; }');
        }
    }

    public function onContentPrepare($eventOrContext, &$item = null, &$params = null, int $page = 0): void
    {
        $context = '';
        $subject = null;

        if ($eventOrContext instanceof \Joomla\Event\EventInterface) {
            $context = method_exists($eventOrContext, 'getContext')
                ? $eventOrContext->getContext()
                : ($eventOrContext->getArgument('context') ?? '');

            if (method_exists($eventOrContext, 'getItem')) {
                $subject = $eventOrContext->getItem();
            } elseif ($eventOrContext->hasArgument('subject')) {
                $subject = $eventOrContext->getArgument('subject');
            } elseif ($eventOrContext->hasArgument('item')) {
                $subject = $eventOrContext->getArgument('item');
            }
        } elseif (is_string($eventOrContext)) {
            $context = $eventOrContext;
            $subject = $item;
        }

        if (!is_object($subject) || $context !== 'com_content.article') {
            return;
        }

        $targetProp = isset($subject->text) ? 'text' : (isset($subject->introtext) ? 'introtext' : null);
        if (!$targetProp) {
            return;
        }

        $content = (string) $subject->{$targetProp};
        $readingTimeHtml = '';
        $socialShareHtml = '';
        $authorBoxHtml   = '';

        // Reading Time
        if ((bool) $this->params->get('enable_reading_time', 1)) {
            $wpm = (int) $this->params->get('words_per_minute', 200);
            if ($wpm < 50) $wpm = 200;
            $cleanText = strip_tags($content);
            $wordCount = preg_match_all('/\S+/u', $cleanText, $matches);
            $minutes = max(1, (int) ceil($wordCount / $wpm));
            $readingTimeHtml = '<div class="flx-reading-time"><span class="flx-rt-icon">⏱️</span> <span>' . $minutes . ' min czytania / ' . $minutes . ' min read</span></div>';
        }

        // Social Share
        if ((bool) $this->params->get('enable_social_share', 1)) {
            $pageUrl = urlencode(Uri::getInstance()->toString());
            $pageTitle = urlencode($subject->title ?? '');

            $socialShareHtml = '<div class="flx-social-share">
                <span class="flx-share-label">Udostępnij:</span>
                <a href="https://www.facebook.com/sharer/sharer.php?u=' . $pageUrl . '" target="_blank" rel="noopener" class="flx-btn-share flx-fb" title="Facebook">FB</a>
                <a href="https://twitter.com/intent/tweet?url=' . $pageUrl . '&text=' . $pageTitle . '" target="_blank" rel="noopener" class="flx-btn-share flx-tw" title="X (Twitter)">X</a>
                <a href="https://www.linkedin.com/sharing/share-offsite/?url=' . $pageUrl . '" target="_blank" rel="noopener" class="flx-btn-share flx-in" title="LinkedIn">in</a>
                <a href="https://api.whatsapp.com/send?text=' . $pageTitle . '%20' . $pageUrl . '" target="_blank" rel="noopener" class="flx-btn-share flx-wa" title="WhatsApp">WA</a>
            </div>';
        }

        // Author Box
        if ((bool) $this->params->get('enable_author_box', 1) && !empty($subject->author)) {
            $authorName = htmlspecialchars((string) $subject->author);
            $authorBoxHtml = '<div class="flx-author-box">
                <div class="flx-author-avatar">✍️</div>
                <div class="flx-author-info">
                    <span class="flx-author-title">Autor artykułu</span>
                    <h4 class="flx-author-name">' . $authorName . '</h4>
                </div>
            </div>';
        }

        $subject->{$targetProp} = $readingTimeHtml . $content . $socialShareHtml . $authorBoxHtml;
    }
}

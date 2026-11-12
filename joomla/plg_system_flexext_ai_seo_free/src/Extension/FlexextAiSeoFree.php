<?php
declare(strict_types=1);

namespace Visacom\Plugin\System\FlexextAiSeoFree\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Event\SubscriberInterface;
use Visacom\Plugin\System\FlexextAiSeoFree\LlmsTxtGenerator;
use Visacom\Plugin\System\FlexextAiSeoFree\BotShieldManager;

require_once dirname(__DIR__) . '/LlmsTxtGenerator.php';
require_once dirname(__DIR__) . '/BotShieldManager.php';

/**
 * System - FlexExt AI SEO Readiness for Joomla 4, 5, and 6.
 * Generates standard /llms.txt, AI Bot Crawling rules, and JSON-LD structured schemas.
 *
 * @package     plg_system_flexext_ai_seo_free
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
final class FlexextAiSeoFree extends CMSPlugin implements SubscriberInterface
{
    public const VERSION = '1.0.2';

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onAfterInitialise'         => 'onAfterInitialise',
            'onBeforeCompileHead'       => 'onBeforeCompileHead',
            'onAjaxFlexext_ai_seo_free' => 'onAjaxFlexext_ai_seo_free',
        ];
    }

    public function onAfterInitialise($event = null): void
    {
        $uri  = Uri::getInstance();
        $path = trim($uri->getPath(), '/');
        $app  = $this->getApplication() ?: Factory::getApplication();
        $inp  = $app->getInput();
        $opt  = $inp ? $inp->getCmd('option', '') : '';
        $plg  = $inp ? $inp->getCmd('plugin', '') : '';
        $task = ($opt === 'com_ajax' && $plg === 'flexext_ai_seo_free' && $inp) ? $inp->getCmd('task', '') : '';

        // Check virtual /llms.txt or AJAX task
        if (str_ends_with($path, 'llms.txt') || $task === 'llmstxt') {
            if ((bool) $this->params->get('enable_llms_txt', 1)) {
                LlmsTxtGenerator::output($this->params);
                exit;
            }
        }

        if (str_ends_with($path, 'robots-ai.txt') || $task === 'botshield') {
            if ((bool) $this->params->get('enable_bot_shield', 1)) {
                BotShieldManager::output($this->params);
                exit;
            }
        }
    }

    public function onBeforeCompileHead($event = null): void
    {
        $app = $this->getApplication() ?: Factory::getApplication();
        $doc = $app->getDocument();

        if (!$doc || !$app->isClient('site')) {
            return;
        }

        // Link alternate for LLMs
        if ((bool) $this->params->get('enable_llms_txt', 1)) {
            $llmsUrl = Uri::root() . 'index.php?option=com_ajax&plugin=flexext_ai_seo_free&group=system&task=llmstxt';
            $doc->addCustomTag('<link rel="alternate" type="text/markdown" href="' . htmlspecialchars($llmsUrl) . '" title="LLMs.txt" />');
        }

        // JSON-LD Schema.org
        if ((bool) $this->params->get('enable_schema_org', 1)) {
            $orgName = $this->params->get('org_name') ?: $app->get('sitename', 'Joomla Website');
            $orgType = $this->params->get('org_type', 'Organization');
            $siteUrl = Uri::root();

            $schema = [
                '@context' => 'https://schema.org',
                '@type'    => $orgType,
                'name'     => $orgName,
                'url'      => $siteUrl,
            ];

            $doc->addCustomTag('<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>');
        }
    }

    public function onAjaxFlexext_ai_seo_free($event = null): void
    {
        $app  = $this->getApplication() ?: Factory::getApplication();
        $inp  = $app->getInput();
        $task = $inp ? $inp->getCmd('task', '') : '';
        if ($task === 'llmstxt') {
            LlmsTxtGenerator::output($this->params);
            exit;
        }
        if ($task === 'botshield') {
            BotShieldManager::output($this->params);
            exit;
        }

        echo new JsonResponse(['status' => 'ok', 'version' => self::VERSION, 'plugin' => 'flexext_ai_seo_free']);
        exit;
    }
}

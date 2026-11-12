<?php
declare(strict_types=1);

namespace Visacom\Plugin\System\EaaguardFree\Extension;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Event\SubscriberInterface;
use Visacom\Plugin\System\EaaguardFree\DeclarationGenerator;

require_once dirname(__DIR__) . '/DeclarationGenerator.php';

/**
 * System - FlexExt EAA Accessibility Guard for Joomla 4, 5, and 6.
 * WCAG 2.2 / EAA Accessibility Toolbar & Declaration Generator.
 *
 * @package     plg_system_eaaguard_free
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
final class EaaguardFree extends CMSPlugin implements SubscriberInterface
{
    public const VERSION = '1.0.1';

    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return [
            'onBeforeCompileHead'   => 'onBeforeCompileHead',
            'onAfterInitialise'     => 'onAfterInitialise',
            'onContentPrepare'      => 'onContentPrepare',
            'onAjaxEaaguard_free'   => 'onAjaxEaaguard_free',
        ];
    }

    public function onAfterInitialise($event = null): void
    {
        $app = $this->getApplication() ?: Factory::getApplication();
        $input = $app->getInput();
        $option = $input ? $input->getCmd('option', '') : '';
        $plugin = $input ? $input->getCmd('plugin', '') : '';
        $task   = $input ? $input->getCmd('task', '') : '';

        if ($option === 'com_ajax' && ($plugin === 'eaaguard_free' || $plugin === 'system_eaaguard_free')) {
            $this->handleAjaxEndpoint($task);
        }
    }

    public function onBeforeCompileHead($event = null): void
    {
        $app = $this->getApplication() ?: Factory::getApplication();
        $doc = $app->getDocument();
        if (!$doc || !$app->isClient('site')) {
            return;
        }

        $frontendBadge = (bool) $this->params->get('frontend_badge', 1);
        if ($frontendBadge) {
            $rootUrl = Uri::root(true);
            $pluginMediaUrl = $rootUrl . '/plugins/system/eaaguard_free/media';

            $doc->addStyleSheet($pluginMediaUrl . '/css/frontend-wcag-badge.css?v=' . self::VERSION);
            $doc->addScript($pluginMediaUrl . '/js/frontend-wcag-badge.js?v=' . self::VERSION);

            $badgeConfig = [
                'position'       => (string) $this->params->get('badge_position', 'bottom-right'),
                'declarationUrl' => $rootUrl . '/index.php?option=com_ajax&plugin=eaaguard_free&group=system&task=declaration&format=html',
            ];
            $doc->addScriptDeclaration('window.FlxWcagConfig = ' . json_encode($badgeConfig) . ';');
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

        if (!is_object($subject)) {
            return;
        }

        $targetProp = null;
        if (isset($subject->text)) {
            $targetProp = 'text';
        } elseif (isset($subject->introtext)) {
            $targetProp = 'introtext';
        }

        if ($targetProp && str_contains((string) $subject->{$targetProp}, '{eaaguard_declaration}')) {
            $declarationHtml = DeclarationGenerator::render($this->params);
            $subject->{$targetProp} = str_replace('{eaaguard_declaration}', $declarationHtml, (string) $subject->{$targetProp});
        }
    }

    public function onAjaxEaaguard_free($event = null): void
    {
        $app  = $this->getApplication() ?: Factory::getApplication();
        $inp  = $app->getInput();
        $task = $inp ? $inp->getCmd('task', '') : '';
        $this->handleAjaxEndpoint($task);
    }

    private function handleAjaxEndpoint(string $task): void
    {
        $app = $this->getApplication() ?: Factory::getApplication();

        if ($task === 'declaration') {
            $format = $app->getInput()->getCmd('format', 'html');

            if ($format === 'html' || $format === 'raw') {
                header('Content-Type: text/html; charset=utf-8');
                $cssUrl = Uri::root(true) . '/plugins/system/eaaguard_free/media/css/frontend-wcag-badge.css';
                echo '<!DOCTYPE html><html lang="pl"><head><meta charset="utf-8"><title>Deklaracja Dostępności Cyfrowej</title>';
                echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
                echo '<link rel="stylesheet" href="' . $cssUrl . '">';
                echo '<style>body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f8fafc; color: #0f172a; padding: 40px 15px; margin: 0; } .eaaguard-declaration-wrapper { max-width: 900px; margin: 0 auto; background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; }</style>';
                echo '</head><body>';
                echo '<main id="main-content" role="main">';
                echo DeclarationGenerator::render($this->params);
                echo '</main>';
                echo '</body></html>';
                exit;
            }

            echo new JsonResponse(['html' => DeclarationGenerator::render($this->params)]);
            exit;
        }

        echo new JsonResponse(['status' => 'ok', 'version' => self::VERSION]);
        exit;
    }
}

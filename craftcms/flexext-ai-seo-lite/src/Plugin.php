<?php
declare(strict_types=1);

namespace flexext\aiseolite;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\Application as WebApplication;
use craft\web\Response as CraftResponse;
use craft\web\UrlManager;
use flexext\aiseolite\models\Settings;
use yii\base\Event;
use yii\web\Response;

/**
 * @method Settings getSettings()
 */
class Plugin extends BasePlugin
{
    public const VERSION = '1.0.0';

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;
    public bool $hasCpSection = true;

    public function init(): void
    {
        parent::init();

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            static function (RegisterUrlRulesEvent $event): void {
                $event->rules['flexext-ai-seo-lite'] = 'flexext-ai-seo-lite/cp/index';
            }
        );

        if (Craft::$app->getRequest()->getIsConsoleRequest()) {
            return;
        }

        if (Craft::$app->getRequest()->getIsSiteRequest()) {
            Event::on(
                WebApplication::class,
                WebApplication::EVENT_BEFORE_REQUEST,
                [$this, 'handleSiteBeforeRequest']
            );

            Event::on(
                CraftResponse::class,
                Response::EVENT_AFTER_PREPARE,
                [$this, 'handleResponseAfterPrepare']
            );
        }
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        if ($item !== null) {
            $item['label'] = 'AI SEO Lite';
            $item['url'] = 'flexext-ai-seo-lite';
        }
        return $item;
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('flexext-ai-seo-lite/settings', [
            'plugin'   => $this,
            'settings' => $this->getSettings(),
            'cpUrl'    => UrlHelper::cpUrl('flexext-ai-seo-lite'),
        ]);
    }

    public function handleSiteBeforeRequest(): void
    {
        $settings = $this->getSettings();
        if (!$settings->enabled || !$settings->enableLlmsTxt) {
            return;
        }

        $path = trim((string)Craft::$app->getRequest()->getPathInfo(), '/');
        if ($path !== 'llms.txt') {
            return;
        }

        $res = Craft::$app->getResponse();
        $res->setStatusCode(200);
        $res->format = Response::FORMAT_RAW;
        $res->headers->set('Content-Type', 'text/markdown; charset=UTF-8');
        $res->headers->set('X-Robots-Tag', 'all');
        $res->content = $this->buildLlmsTxt();
        $res->send();
        Craft::$app->end();
    }

    public function handleResponseAfterPrepare(Event $event): void
    {
        $settings = $this->getSettings();
        if (!$settings->enabled) {
            return;
        }

        /** @var CraftResponse $res */
        $res = $event->sender;
        if ($res->getStatusCode() !== 200 || !in_array($res->format, [Response::FORMAT_HTML, 'template'], true)) {
            return;
        }

        if ($settings->enableLlmsTxt) {
            $res->headers->add('Link', '</llms.txt>; rel="llms-txt"; type="text/markdown"');
        }

        if ($settings->enableJsonLd && is_string($res->content) && stripos($res->content, '</head>') !== false && stripos($res->content, 'application/ld+json') === false) {
            $baseUrl = $this->resolveBaseUrl();
            $siteName = (string)(Craft::$app->getSystemName() ?: 'Craft CMS Site');
            $graph = [
                '@context' => 'https://schema.org',
                '@graph'   => [
                    [
                        '@type' => 'Organization',
                        '@id'   => $baseUrl . '/#organization',
                        'name'  => $siteName,
                        'url'   => $baseUrl . '/',
                    ],
                    [
                        '@type'     => 'WebSite',
                        '@id'       => $baseUrl . '/#website',
                        'name'      => $siteName,
                        'url'       => $baseUrl . '/',
                        'publisher' => ['@id' => $baseUrl . '/#organization'],
                    ],
                ],
            ];
            $script = '<script type="application/ld+json">' . json_encode($graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n</head>";
            $res->content = preg_replace('/<\/head>/i', $script, $res->content, 1) ?? $res->content;
        }
    }

    public function resolveBaseUrl(): string
    {
        $site = Craft::$app->getSites()->getCurrentSite();
        $base = (string)($site->getBaseUrl() ?? '');
        if ($base === '' && !Craft::$app->getRequest()->getIsConsoleRequest()) {
            $base = (string)Craft::$app->getRequest()->getHostInfo() . Craft::$app->getRequest()->getBaseUrl();
        }
        return rtrim($base ?: 'http://localhost', '/');
    }

    public function buildLlmsTxt(): string
    {
        $settings = $this->getSettings();
        $baseUrl = $this->resolveBaseUrl();
        $siteName = (string)(Craft::$app->getSystemName() ?: 'Craft CMS Website');
        $summary = trim($settings->siteSummary) !== ''
            ? trim($settings->siteSummary)
            : "Structured index of published entries on {$siteName}.";
        $limit = max(5, min(50, (int)$settings->entryLimit));

        $out = "# {$siteName}\n\n> {$summary}\n\n";
        $out .= "## Primary Endpoints\n\n";
        $out .= "- [Home]({$baseUrl}/): Main website\n";
        $out .= "- [LLM Index]({$baseUrl}/llms.txt): Structured Markdown index\n\n";

        $entries = Entry::find()
            ->status('live')
            ->siteId(Craft::$app->getSites()->getCurrentSite()->id)
            ->uri(':notempty:')
            ->orderBy(['postDate' => SORT_DESC])
            ->limit($limit)
            ->all();

        if (!empty($entries)) {
            $out .= "## Published Entries\n\n";
            foreach ($entries as $entry) {
                $url = $entry->getUrl() ?: ($baseUrl . '/' . ltrim((string)$entry->uri, '/'));
                $title = trim((string)$entry->title) ?: $entry->slug;
                $out .= "- [{$title}]({$url})\n";
            }
        }

        return $out;
    }
}

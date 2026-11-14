<?php
declare(strict_types=1);

namespace flexext\eaaguardlite;

use Craft;
use craft\base\Model;
use craft\base\Plugin as BasePlugin;
use craft\elements\Entry;
use craft\events\RegisterUrlRulesEvent;
use craft\helpers\UrlHelper;
use craft\web\Application as WebApplication;
use craft\web\UrlManager;
use flexext\eaaguardlite\models\Settings;
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
                $event->rules['flexext-eaa-guard-lite'] = 'flexext-eaa-guard-lite/cp/index';
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
        }
    }

    public function getCpNavItem(): ?array
    {
        $item = parent::getCpNavItem();
        if ($item !== null) {
            $item['label'] = 'EAA Guard Lite';
            $item['url'] = 'flexext-eaa-guard-lite';
        }
        return $item;
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }

    protected function settingsHtml(): ?string
    {
        return Craft::$app->getView()->renderTemplate('flexext-eaa-guard-lite/settings', [
            'plugin'   => $this,
            'settings' => $this->getSettings(),
            'cpUrl'    => UrlHelper::cpUrl('flexext-eaa-guard-lite'),
        ]);
    }

    public function handleSiteBeforeRequest(): void
    {
        $settings = $this->getSettings();
        if (!$settings->enabled || !$settings->enableStatementRoute) {
            return;
        }

        $path = trim((string)Craft::$app->getRequest()->getPathInfo(), '/');
        if ($path !== 'accessibility-statement') {
            return;
        }

        $org = trim($settings->organizationName) !== ''
            ? trim($settings->organizationName)
            : (string)(Craft::$app->getSystemName() ?: 'This Website');
        $email = trim($settings->contactEmail);
        $escOrg = htmlspecialchars($org, ENT_QUOTES, 'UTF-8');
        $escEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');

        $res = Craft::$app->getResponse();
        $res->setStatusCode(200);
        $res->format = Response::FORMAT_RAW;
        $res->headers->set('Content-Type', 'text/html; charset=UTF-8');
        $res->content = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Accessibility Statement — ' . $escOrg . '</title>'
            . '<style>:root{--bg:#f8fafc;--card:#ffffff;--fg:#0f172a;--muted:#334155;--bd:#cbd5e1;--lk:#0369a1}'
            . '@media(prefers-color-scheme:dark){:root{--bg:#0f172a;--card:#1e293b;--fg:#f8fafc;--muted:#cbd5e1;--bd:#334155;--lk:#38bdf8}}'
            . 'body{margin:0;padding:32px 16px;font-family:system-ui,-apple-system,sans-serif;background:var(--bg);color:var(--fg);line-height:1.65}'
            . 'main{max-width:760px;margin:0 auto;background:var(--card);color:var(--fg);border:1px solid var(--bd);border-radius:12px;padding:28px}'
            . 'a{color:var(--lk)}</style></head><body><main>'
            . '<h1>Accessibility Statement</h1>'
            . '<p><strong>' . $escOrg . '</strong> is committed to ensuring digital accessibility in alignment with <strong>WCAG 2.2 Level AA</strong> and <strong>EN 301 549 (European Accessibility Act)</strong>.</p>'
            . '<h2>Measures Taken</h2><ul>'
            . '<li>Semantic HTML5 landmarks and logical heading structures</li>'
            . '<li>Text alternatives for meaningful images and media</li>'
            . '<li>Keyboard navigation and WCAG AA contrast ratios in light and dark themes</li></ul>'
            . ($escEmail !== '' ? '<h2>Feedback &amp; Contact</h2><p>If you encounter an accessibility barrier, please contact <a href="mailto:' . $escEmail . '">' . $escEmail . '</a>.</p>' : '')
            . '</main></body></html>';
        $res->send();
        Craft::$app->end();
    }

    public function scanRecentEntries(int $limit = 25): array
    {
        $entries = Entry::find()
            ->status('live')
            ->siteId(Craft::$app->getSites()->getCurrentSite()->id)
            ->orderBy(['dateUpdated' => SORT_DESC])
            ->limit(max(1, min(25, $limit)))
            ->all();

        $results = [];
        foreach ($entries as $entry) {
            $html = '';
            foreach ($entry->getFieldValues() as $val) {
                if (is_string($val) || (is_object($val) && method_exists($val, '__toString'))) {
                    $str = (string)$val;
                    if (str_contains($str, '<')) {
                        $html .= "\n" . $str;
                    }
                }
            }
            $issues = $this->auditHtmlSnippet($html);
            $results[] = [
                'id'         => $entry->id,
                'title'      => $entry->title ?: $entry->slug,
                'cpEditUrl'  => $entry->getCpEditUrl(),
                'issueCount' => count($issues),
                'issues'     => $issues,
            ];
        }
        return $results;
    }

    public function auditHtmlSnippet(string $html): array
    {
        if (trim($html) === '') {
            return [];
        }
        $issues = [];

        if (preg_match_all('/<img\b[^>]*>/i', $html, $imgMatches)) {
            foreach ($imgMatches[0] as $tag) {
                if (!preg_match('/\balt\s*=/i', $tag)) {
                    $issues[] = 'WCAG 1.1.1: <img> element is missing an alt attribute.';
                }
            }
        }

        if (preg_match_all('/<a\b[^>]*>([\s\S]*?)<\/a>/i', $html, $linkMatches, PREG_SET_ORDER)) {
            foreach ($linkMatches as $lm) {
                $inner = trim(strip_tags($lm[1]));
                if ($inner === '' && !preg_match('/aria-label\s*=/i', $lm[0]) && !preg_match('/<img\b/i', $lm[1])) {
                    $issues[] = 'WCAG 2.4.4: Empty <a> link without discernible text or aria-label.';
                }
            }
        }

        if (preg_match_all('/<h([1-6])\b/i', $html, $hMatches)) {
            $prev = 0;
            foreach ($hMatches[1] as $lvlStr) {
                $lvl = (int)$lvlStr;
                if ($prev > 0 && $lvl > $prev + 1) {
                    $issues[] = "WCAG 1.3.1: Skipped heading level from H{$prev} to H{$lvl}.";
                }
                $prev = $lvl;
            }
        }

        return $issues;
    }
}

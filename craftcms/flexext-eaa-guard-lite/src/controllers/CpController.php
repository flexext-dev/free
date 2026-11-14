<?php
declare(strict_types=1);

namespace flexext\eaaguardlite\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flexext\eaaguardlite\Plugin;
use yii\web\Response;

class CpController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requirePermission('accessPlugin-flexext-eaa-guard-lite');
        $plugin = Plugin::getInstance();
        $site = Craft::$app->getSites()->getCurrentSite();
        $baseUrl = rtrim((string)($site->getBaseUrl() ?? ''), '/');

        return $this->renderTemplate('flexext-eaa-guard-lite/dashboard', [
            'plugin'      => $plugin,
            'settings'    => $plugin->getSettings(),
            'baseUrl'     => $baseUrl,
            'scanRows'    => $plugin->scanRecentEntries(25),
            'settingsUrl' => UrlHelper::cpUrl('settings/plugins/flexext-eaa-guard-lite'),
        ]);
    }
}

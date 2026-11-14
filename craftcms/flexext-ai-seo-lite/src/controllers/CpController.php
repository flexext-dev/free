<?php
declare(strict_types=1);

namespace flexext\aiseolite\controllers;

use Craft;
use craft\helpers\UrlHelper;
use craft\web\Controller;
use flexext\aiseolite\Plugin;
use yii\web\Response;

class CpController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requirePermission('accessPlugin-flexext-ai-seo-lite');
        $plugin = Plugin::getInstance();
        $baseUrl = $plugin->resolveBaseUrl();

        return $this->renderTemplate('flexext-ai-seo-lite/dashboard', [
            'plugin'      => $plugin,
            'settings'    => $plugin->getSettings(),
            'baseUrl'     => $baseUrl,
            'llmsPreview' => $plugin->buildLlmsTxt(),
            'settingsUrl' => UrlHelper::cpUrl('settings/plugins/flexext-ai-seo-lite'),
        ]);
    }
}

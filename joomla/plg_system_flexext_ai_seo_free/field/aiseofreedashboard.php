<?php
/**
 * @package     FlexExt
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

class JFormFieldAiseofreedashboard extends FormField
{
    protected $type = 'aiseofreedashboard';

    protected function getInput(): string
    {
        $llmsUrl = Uri::root() . 'index.php?option=com_ajax&plugin=flexext_ai_seo_free&group=system&task=llmstxt';
        $botUrl  = Uri::root() . 'index.php?option=com_ajax&plugin=flexext_ai_seo_free&group=system&task=botshield';

        return '
        <div style="background: linear-gradient(135deg, #07152B 0%, #0F2A4A 100%); border: 1px solid #1E3A8A; border-radius: 14px; padding: 24px; color: #F8FAFC; margin-bottom: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">🤖</span>
                    <div>
                        <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">FlexExt AI SEO &amp; Agent Readiness <span style="background: #10B981; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 800; vertical-align: middle;">FREE EDITION</span></h3>
                        <p style="margin: 3px 0 0 0; color: #94A3B8; font-size: 13px;">Optymalizacja witryny Joomla dla wyszukiwarek sztucznej inteligencji, modeli LLM oraz robotów AI.</p>
                    </div>
                </div>
                <a href="https://flexext.dev/en-gb/product/flexext-ai-seo-agent-readiness-joomla" target="_blank" style="background: linear-gradient(135deg, #1980F7 0%, #0066E0 100%); color: #fff; text-decoration: none; font-weight: 800; font-size: 13px; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(25,128,247,0.4);">Przejdź do wersji PRO ➔</a>
            </div>

            <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                <a href="' . $llmsUrl . '" target="_blank" style="background: rgba(25,128,247,0.2); border: 1px solid #1980F7; color: #60A5FA; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700;">📄 Wyświetl Twój plik /llms.txt</a>
                <a href="' . $botUrl . '" target="_blank" style="background: rgba(16,185,129,0.2); border: 1px solid #10B981; color: #34D399; padding: 8px 16px; border-radius: 8px; text-decoration: none; font-size: 13px; font-weight: 700;">🛡️ Wyświetl reguły AI robots.txt</a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #34D399;">✅ Aktywne w Twojej wersji Free:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Automatyczny standard /llms.txt</strong> – ustrukturyzowany spis treści strony dla OpenAI, Claude, Perplexity i Gemini.</li>
                        <li><strong>Tarcza BotShield dla robotów AI</strong> – generowanie selektywnych reguł crawlowania dla GPTBot, ClaudeBot, PerplexityBot i CCBot.</li>
                        <li><strong>Mikrodane JSON-LD Schema.org</strong> – automatyczne struktury danych Organization i WebSite.</li>
                        <li><strong>Pełna zgodność z Joomla 4, 5 i 6</strong>.</li>
                    </ul>
                </div>
                <div style="background: rgba(25,128,247,0.08); border: 1px solid rgba(56,189,248,0.25); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #38BDF8;">🚀 Dostępne wyłącznie w edycji PRO:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Negocjacja treści Markdown (Accept: text/markdown)</strong> – natychmiastowe serwowanie czystego markdownu dla agentów AI zamiast ciężkiego HTML.</li>
                        <li><strong>Kompletna baza wiedzy /llms-full.txt</strong> – automatyczny eksport wszystkich artykułów z optymalizacją tokenów.</li>
                        <li><strong>Protokół WebMCP (Model Context Protocol JSON-RPC)</strong> – interfejs narzędziowy dla autonomicznych agentów AI.</li>
                        <li><strong>Analityka ruchu robotów AI w czasie rzeczywistym</strong> – wykresy i statystyki wizyt GPTBot, ClaudeBot i innych.</li>
                    </ul>
                </div>
            </div>
        </div>
        ';
    }
}

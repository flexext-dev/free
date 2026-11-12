<?php
/**
 * @package     FlexExt Web Accessibility & EAA Guard Free
 * @subpackage  plg_system_eaaguard_free
 *
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

class JFormFieldEaaguardfreedashboard extends FormField
{
    protected $type = 'eaaguardfreedashboard';

    protected function getInput(): string
    {
        return '
        <div style="background: linear-gradient(135deg, #07152B 0%, #0F2A4A 100%); border: 1px solid #1E3A8A; border-radius: 14px; padding: 24px; color: #F8FAFC; margin-bottom: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">♿</span>
                    <div>
                        <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">FlexExt Web Accessibility &amp; EAA Guard <span style="background: #10B981; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 800; vertical-align: middle;">FREE EDITION</span></h3>
                        <p style="margin: 3px 0 0 0; color: #94A3B8; font-size: 13px;">Dostępność cyfrowa stron Joomla 4, 5 i 6 zgodna z normą WCAG 2.2 AA oraz Europejskim Aktem o Dostępności (EAA 2025).</p>
                    </div>
                </div>
                <a href="https://flexext.dev/en-gb/product/real-time-wcag-eaa-editor-guard-joomla" target="_blank" style="background: linear-gradient(135deg, #1980F7 0%, #0066E0 100%); color: #fff; text-decoration: none; font-weight: 800; font-size: 13px; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(25,128,247,0.4);">Przejdź do wersji PRO ➔</a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 18px;">
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #34D399;">✅ Aktywne w Twojej wersji Free:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Pływający pasek dostępności (Frontend Toolbar)</strong> – natychmiastowe narzędzia dla użytkowników ze szczególnymi potrzebami.</li>
                        <li><strong>Tryb Wysokiego Kontrastu (WCAG AAA)</strong> – odwracanie kolorów i maksymalna czytelność.</li>
                        <li><strong>Czcionka dla dyslektyków (OpenDyslexic)</strong> oraz skalowanie tekstu (100% / 120% / 140%).</li>
                        <li><strong>Podświetlanie linków</strong> oraz zatrzymywanie animacji CSS.</li>
                        <li><strong>Generator Deklaracji Dostępności</strong> z shortcode <code>{eaaguard_declaration}</code>.</li>
                    </ul>
                </div>
                <div style="background: rgba(25,128,247,0.08); border: 1px solid rgba(56,189,248,0.25); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #38BDF8;">🚀 Dostępne wyłącznie w edycji PRO:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Pre-Publish Hard Gatekeeper</strong> – blokada publikacji artykułów z krytycznymi błędami WCAG 2.2 w edytorze TinyMCE.</li>
                        <li><strong>Silnik 1-Click Auto-Fix</strong> – automatyczna naprawa kontrastu, altów i hierarchii nagłówków.</li>
                        <li><strong>Głęboki skaner binarny plików PDF</strong> – analiza Tagged PDF (/StructTreeRoot) oraz OCR.</li>
                        <li><strong>Audytor całego serwisu (PageAuditor)</strong> – kompleksowy raport zgodności WCAG 2.2 AA dla stron sklepu i artykułów.</li>
                    </ul>
                </div>
            </div>
        </div>
        ';
    }
}

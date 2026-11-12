<?php
/**
 * @package     FlexExt
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;

class JFormFieldArticlesfreedashboard extends FormField
{
    protected $type = 'articlesfreedashboard';

    protected function getInput(): string
    {
        return '
        <div style="background: linear-gradient(135deg, #07152B 0%, #0F2A4A 100%); border: 1px solid #1E3A8A; border-radius: 14px; padding: 24px; color: #F8FAFC; margin-bottom: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.3);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 14px;">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">📰</span>
                    <div>
                        <h3 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">FlexExt Articles Hub <span style="background: #10B981; color: #fff; font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 800; vertical-align: middle;">FREE EDITION</span></h3>
                        <p style="margin: 3px 0 0 0; color: #94A3B8; font-size: 13px;">Wzbogać swoje artykuły Joomla o wskaźnik czasu czytania, pasek postępu, przyciski udostępniania i wizytówkę autora.</p>
                    </div>
                </div>
                <a href="https://flexext.dev/en-gb/product/articles-hub-joomla" target="_blank" style="background: linear-gradient(135deg, #1980F7 0%, #0066E0 100%); color: #fff; text-decoration: none; font-weight: 800; font-size: 13px; padding: 10px 20px; border-radius: 10px; box-shadow: 0 4px 14px rgba(25,128,247,0.4);">Przejdź do wersji PRO ➔</a>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #34D399;">✅ Aktywne w Twojej wersji Free:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Szacunkowy czas czytania</strong> – estymacja w minutach na podstawie liczby słów.</li>
                        <li><strong>Górny pasek postępu czytania</strong> – płynny indykator scrollowania dopasowany do Twojego motywu.</li>
                        <li><strong>Przyciski udostępniania Social Share</strong> – Facebook, X / Twitter, LinkedIn, WhatsApp.</li>
                        <li><strong>Wizytówka autora artykułu (Author Box)</strong> pod treścią wpisu.</li>
                    </ul>
                </div>
                <div style="background: rgba(25,128,247,0.08); border: 1px solid rgba(56,189,248,0.25); border-radius: 12px; padding: 16px;">
                    <h4 style="margin: 0 0 10px 0; font-size: 15px; color: #38BDF8;">🚀 Dostępne wyłącznie w edycji PRO:</h4>
                    <ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #CBD5E1; font-size: 13px;">
                        <li><strong>Osadzanie siatek artykułów przez Shortcode</strong> – <code>{flexext_articles layout=grid count=6}</code> w dowolnym miejscu.</li>
                        <li><strong>Układy Magazine i Carousel</strong> – efektowne kafelki, magazynowy news ticker i suwaki.</li>
                        <li><strong>Silnik inteligentnych artykułów powiązanych</strong> – powiązania semantyczne i po tagach.</li>
                        <li><strong>Filtrowanie AJAX bez przeładowania strony</strong>.</li>
                    </ul>
                </div>
            </div>
        </div>
        ';
    }
}

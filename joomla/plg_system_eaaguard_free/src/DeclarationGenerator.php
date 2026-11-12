<?php
/**
 * @package     FlexExt Web Accessibility & EAA Guard Free
 * @subpackage  plg_system_eaaguard_free
 *
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

namespace Visacom\Plugin\System\EaaguardFree;

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Automatyczny Generator Deklaracji Dostępności (Accessibility Statement)
 * Zgodny z normą EN 301 549, WCAG 2.2 AA, Dyrektywą UE 2016/2102 oraz Ustawą o dostępności cyfrowej.
 */
final class DeclarationGenerator
{
    public static function render(Registry $params): string
    {
        $orgName         = htmlspecialchars($params->get('org_name', 'Instytucja / Przedsiębiorstwo'));
        $siteUrl         = htmlspecialchars($params->get('site_url', $_SERVER['HTTP_HOST'] ?? ''));
        $pubDate         = htmlspecialchars($params->get('pub_date', date('Y-01-01')));
        $updateDate      = htmlspecialchars($params->get('update_date', date('Y-m-d')));
        $complianceStatus= $params->get('compliance_status', 'partial');
        $contactPerson   = htmlspecialchars($params->get('contact_person', 'Koordynator ds. Dostępności Cyfrowej'));
        $contactEmail    = htmlspecialchars($params->get('contact_email', 'dostepnosc@' . ($_SERVER['HTTP_HOST'] ?? 'example.com')));
        $contactPhone    = htmlspecialchars($params->get('contact_phone', '+48 22 000 00 00'));
        $unaccessibleDesc= htmlspecialchars($params->get('unaccessible_desc', 'Niektóre starsze dokumenty PDF opublikowane przed wejściem w życie przepisów EAA mogą nie zawierać pełnej struktury znaczników Tagged PDF. Sukcesywnie wdrażana jest ich konwersja.'));
        $archDesc        = htmlspecialchars($params->get('arch_desc', 'Budynek wyposażony jest w podjazd dla wózków inwalidzkich, windę z komunikatami głosowymi oraz oznaczenia w alfabecie Braille\'a. Do budynku można wejść z psem asystującym.'));

        $statusBadge = '<span class="eaaguard-status-badge" style="background:#0284c7; color:#fff; padding:6px 14px; border-radius:20px; font-weight:700;">Częściowo zgodna z normą WCAG 2.2 AA</span>';
        if ($complianceStatus === 'full') {
            $statusBadge = '<span class="eaaguard-status-badge" style="background:#16a34a; color:#fff; padding:6px 14px; border-radius:20px; font-weight:700;">W pełni zgodna z normą WCAG 2.2 AA / EN 301 549</span>';
        } elseif ($complianceStatus === 'none') {
            $statusBadge = '<span class="eaaguard-status-badge" style="background:#dc2626; color:#fff; padding:6px 14px; border-radius:20px; font-weight:700;">Niezgodna z normą WCAG 2.2</span>';
        }

        ob_start();
        ?>
        <article class="eaaguard-declaration-wrapper" lang="pl" aria-labelledby="declaration-main-heading">
            <header style="margin-bottom: 24px; border-bottom: 2px solid #e2e8f0; padding-bottom: 20px;">
                <h1 id="declaration-main-heading" style="font-size: 28px; margin: 0 0 10px 0; color: #0f172a;">Deklaracja Dostępności Cyfrowej</h1>
                <p style="color: #64748b; margin: 0 0 14px 0;">
                    <strong>Podmiot:</strong> <?php echo $orgName; ?> | 
                    <strong>Strona internetowa:</strong> <a href="//<?php echo $siteUrl; ?>" rel="noopener noreferrer"><?php echo $siteUrl; ?></a>
                </p>
                <div><?php echo $statusBadge; ?></div>
            </header>

            <section style="margin-bottom: 24px;">
                <h2 style="font-size: 20px; color: #1e293b;">Wstęp Deklaracji</h2>
                <p><?php echo $orgName; ?> zobowiązuje się zapewnić dostępność swojej strony internetowej zgodnie z przepisami ustawy z dnia 4 kwietnia 2019 r. o dostępności cyfrowej stron internetowych i aplikacji mobilnych podmiotów publicznych oraz Europejskim Aktem o Dostępności (EAA / Dyrektywa 2019/882).</p>
                <p>Oświadczenie w sprawie dostępności ma zastosowanie do strony internetowej: <strong><?php echo $siteUrl; ?></strong>.</p>
                <p>Data publikacji strony internetowej: <strong><?php echo $pubDate; ?></strong>.<br>Data ostatniej istotnej aktualizacji: <strong><?php echo $updateDate; ?></strong>.</p>
            </section>

            <section style="margin-bottom: 24px;">
                <h2 style="font-size: 20px; color: #1e293b;">Stan Dostępności Cyfrowej</h2>
                <p>Strona internetowa jest <?php echo ($complianceStatus === 'full' ? 'zgodna' : 'częściowo zgodna'); ?> z wytycznymi dla dostępności treści internetowych (WCAG 2.2) na poziomie AA z powodu ewentualnych wyłączeń wymienionych poniżej:</p>
                <div style="background: #f1f5f9; padding: 16px; border-radius: 8px; border-left: 4px solid #0284c7;">
                    <?php echo nl2br($unaccessibleDesc); ?>
                </div>
            </section>

            <section style="margin-bottom: 24px;">
                <h2 style="font-size: 20px; color: #1e293b;">Informacje Zwrotne i Dane Kontaktowe</h2>
                <p>W przypadku problemów z dostępnością strony internetowej prosimy o kontakt:</p>
                <ul style="line-height: 1.8;">
                    <li><strong>Osoba odpowiedzialna:</strong> <?php echo $contactPerson; ?></li>
                    <li><strong>E-mail:</strong> <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a></li>
                    <li><strong>Telefon:</strong> <a href="tel:<?php echo $contactPhone; ?>"><?php echo $contactPhone; ?></a></li>
                </ul>
            </section>

            <section style="margin-bottom: 24px;">
                <h2 style="font-size: 20px; color: #1e293b;">Dostępność Architektoniczna</h2>
                <div style="background: #f8fafc; padding: 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <?php echo nl2br($archDesc); ?>
                </div>
            </section>

            <footer style="margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 14px; font-size: 13px; color: #64748b;">
                Wygenerowano automatycznie przez moduł <strong>FlexExt Web Accessibility &amp; EAA Guard</strong>.
            </footer>
        </article>
        <?php
        return (string) ob_get_clean();
    }
}

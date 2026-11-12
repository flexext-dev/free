<?php
declare(strict_types=1);

namespace Visacom\WpEaaGuard;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Real-Time & Backend WCAG 2.2 AA / EAA Content Auditor & 1-Click Auto-Fix Remediation Engine.
 *
 * @package     wp-eaaguard
 * @author      Visacom / FlexExt <contact@flexext.dev>
 * @copyright   (C) 2026 Visacom / FlexExt. All rights reserved.
 * @license     Commercial Proprietary
 */
final class ContentAuditor
{
    /**
     * Audit HTML string and return array of violations
     *
     * @param string $html
     * @return array{passed: bool, violations: array, counts: array{critical: int, warning: int}}
     */
    public static function audit(string $html): array
    {
        $violations = [];
        $trimmed = trim($html);

        if ($trimmed === '') {
            return [
                'passed'     => true,
                'violations' => [],
                'counts'     => ['critical' => 0, 'warning' => 0]
            ];
        }

        // 1. Sprawdzenie obrazów (<img>) pod kątem tekstu alternatywnego (WCAG 1.1.1)
        if (preg_match_all('/<img\b([^>]*)>/i', $html, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[1] as $idx => $match) {
                $attrs = $match[0];
                $fullTag = $matches[0][$idx][0];

                if (!preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $attrs, $altMatch)) {
                    $violations[] = [
                        'code'       => 'WCAG_1_1_1_MISSING_ALT',
                        'criterion'  => 'WCAG 1.1.1 (Non-text Content)',
                        'severity'   => 'critical',
                        'message'    => 'Znaleziono obraz (<img) bez wymaganego atrybutu "alt". Każdy obraz musi posiadać opis alternatywny.',
                        'suggestion' => 'Dodaj atrybut alt="Opis tego co przedstawia obraz" lub alt="" z role="presentation" jeśli to grafika czysto dekoracyjna.',
                        'snippet'    => htmlspecialchars(substr($fullTag, 0, 100))
                    ];
                } else {
                    $altVal = trim($altMatch[2]);
                    $dummyPatterns = ['image', 'obraz', 'obrazek', 'zdjęcie', 'photo', 'pic', 'picture', 'grafika', 'bez nazwy', 'untitled', 'dsc_', 'img_'];
                    $isDummy = false;
                    foreach ($dummyPatterns as $dp) {
                        if (strcasecmp($altVal, $dp) === 0 || str_starts_with(strtolower($altVal), $dp)) {
                            $isDummy = true;
                            break;
                        }
                    }

                    if ($isDummy) {
                        $violations[] = [
                            'code'       => 'WCAG_1_1_1_DUMMY_ALT',
                            'criterion'  => 'WCAG 1.1.1 (Non-text Content)',
                            'severity'   => 'warning',
                            'message'    => 'Opis alternatywny obrazu zawiera generyczną lub bezużyteczną nazwę ("' . htmlspecialchars($altVal) . '").',
                            'suggestion' => 'Zastąp generyczny opis precyzyjną informacją wyjaśniającą treść i kontekst obrazu.',
                            'snippet'    => htmlspecialchars(substr($fullTag, 0, 100))
                        ];
                    }
                }
            }
        }

        // 2. Sprawdzenie hierarchii nagłówków (<h1> - <h6>) (WCAG 1.3.1 / 2.4.6)
        if (preg_match_all('/<h([1-6])\b([^>]*)>(.*?)<\/h\1>/is', $html, $hMatches)) {
            $lastLevel = 1; // Główny tytuł wpisu w WordPress to H1
            $h1Count = 0;

            foreach ($hMatches[1] as $idx => $lvl) {
                $level = (int) $lvl;
                $hText = trim(strip_tags($hMatches[3][$idx]));
                $fullHTag = $hMatches[0][$idx];

                if ($level === 1) {
                    $h1Count++;
                    if ($h1Count > 1) {
                        $violations[] = [
                            'code'       => 'WCAG_1_3_1_MULTIPLE_H1',
                            'criterion'  => 'WCAG 1.3.1 (Info and Relationships)',
                            'severity'   => 'warning',
                            'message'    => 'Wykryto wielokrotny nagłówek H1 w treści wpisu. Tytuł wpisu pełni rolę głównego H1.',
                            'suggestion' => 'Zmień dodatkowe nagłówki H1 na H2 lub H3, aby zachować jednolitą semantykę strony.',
                            'snippet'    => htmlspecialchars(substr($fullHTag, 0, 80))
                        ];
                    }
                }

                if ($hText === '' || $hText === '&nbsp;') {
                    $violations[] = [
                        'code'       => 'WCAG_1_3_1_EMPTY_HEADING',
                        'criterion'  => 'WCAG 1.3.1 (Info and Relationships)',
                        'severity'   => 'critical',
                        'message'    => 'Znaleziono pusty nagłówek H' . $level . ' pozbawiony tekstu.',
                        'suggestion' => 'Usuń pusty nagłówek lub uzupełnij go tekstem opisującym sekcję.',
                        'snippet'    => htmlspecialchars(substr($fullHTag, 0, 80))
                    ];
                }

                if ($lastLevel > 0 && $level > $lastLevel + 1) {
                    $violations[] = [
                        'code'       => 'WCAG_1_3_1_HEADING_LEVEL_SKIPPED',
                        'criterion'  => 'WCAG 1.3.1 (Info and Relationships)',
                        'severity'   => 'critical',
                        'message'    => 'Błąd hierarchii nagłówków: po nagłówku H' . $lastLevel . ' następuje H' . $level . ' (pominięto poziom pośredni).',
                        'suggestion' => 'Zmień nagłówek H' . $level . ' na H' . ($lastLevel + 1) . ', aby czytniki ekranu poprawnie interpretowały strukturę treści.',
                        'snippet'    => htmlspecialchars(substr($fullHTag, 0, 80))
                    ];
                }

                $lastLevel = $level;
            }
        }

        // 3. Sprawdzenie pustych linków i odnośników bez kontekstu (WCAG 2.4.4 / 4.1.2)
        if (preg_match_all('/<a\b([^>]*)>(.*?)<\/a>/is', $html, $linkMatches)) {
            foreach ($linkMatches[1] as $idx => $linkAttrs) {
                $linkBody = trim(strip_tags($linkMatches[2][$idx], '<img><svg>'));
                $textOnly = trim(strip_tags($linkMatches[2][$idx]));
                $fullLink = $linkMatches[0][$idx];

                $hasAriaLabel = preg_match('/\baria-label\s*=\s*(["\'])(.*?)\1/i', $linkAttrs, $ariaMatch) && trim($ariaMatch[2]) !== '';
                $hasTitle = preg_match('/\btitle\s*=\s*(["\'])(.*?)\1/i', $linkAttrs, $titleMatch) && trim($titleMatch[2]) !== '';
                $hasImgWithAlt = preg_match('/<img\b[^>]*\balt\s*=\s*(["\'])(.+?)\1/i', $linkMatches[2][$idx]);

                // Całkowicie pusty link
                if ($linkBody === '' && !$hasAriaLabel && !$hasTitle && !$hasImgWithAlt) {
                    $violations[] = [
                        'code'       => 'WCAG_2_4_4_EMPTY_LINK',
                        'criterion'  => 'WCAG 2.4.4 (Link Purpose in Context)',
                        'severity'   => 'critical',
                        'message'    => 'Znaleziono pusty odnośnik (<a ...></a>) bez tekstu widocznego ani etykiety dostępności (aria-label).',
                        'suggestion' => 'Wprowadź tekst odnośnika lub usuń niepotrzebny znacznik <a>.',
                        'snippet'    => htmlspecialchars(substr($fullLink, 0, 100))
                    ];
                }

                // Generyczny/niedostępny tekst linku ("kliknij tutaj", "więcej", itp.)
                $genericWords = ['kliknij tutaj', 'tutaj', 'więcej', 'czytaj więcej', 'zobacz więcej', 'link', 'pobierz', 'click here', 'more', 'read more', 'download'];
                if (in_array(mb_strtolower($textOnly), $genericWords, true) && !$hasAriaLabel && !$hasTitle) {
                    $violations[] = [
                        'code'       => 'WCAG_2_4_4_AMBIGUOUS_ANCHOR',
                        'criterion'  => 'WCAG 2.4.4 (Link Purpose in Context)',
                        'severity'   => 'warning',
                        'message'    => 'Niejednoznaczny tekst odnośnika ("' . htmlspecialchars($textOnly) . '"). Użytkownicy czytników ekranu nie znają celu linku poza kontekstem akapitu.',
                        'suggestion' => 'Wzbogać tekst odnośnika (np. "Pobierz raport EAA za rok 2026") lub dodaj atrybut aria-label z pełnym opisem.',
                        'snippet'    => htmlspecialchars(substr($fullLink, 0, 100))
                    ];
                }

                // Odnośnik z href="#" bez roli przycisku
                if (preg_match('/\bhref\s*=\s*(["\'])\s*(#|javascript:void\(0\);?)\s*\1/i', $linkAttrs)) {
                    if (!preg_match('/\brole\s*=\s*(["\'])button\1/i', $linkAttrs)) {
                        $violations[] = [
                            'code'       => 'WCAG_2_1_1_DUMMY_HREF',
                            'criterion'  => 'WCAG 2.1.1 (Keyboard Accessible)',
                            'severity'   => 'warning',
                            'message'    => 'Odnośnik z pustym adresem href="#" lub javascript:void(0). Jeśli element wywołuje akcję JS, powinien być przyciskiem (<button>).',
                            'suggestion' => 'Użyj znacznika <button type="button"> lub dodaj role="button" z obsługą klawiszy Enter i Space.',
                            'snippet'    => htmlspecialchars(substr($fullLink, 0, 100))
                        ];
                    }
                }
            }
        }

        // 4. Sprawdzenie tabel (<table>) (WCAG 1.3.1)
        if (preg_match_all('/<table\b([^>]*)>(.*?)<\/table>/is', $html, $tableMatches)) {
            foreach ($tableMatches[2] as $idx => $tableContent) {
                $hasTh = (bool) preg_match('/<th\b([^>]*)>/i', $tableContent);
                $fullTable = $tableMatches[0][$idx];

                if (!$hasTh) {
                    $violations[] = [
                        'code'       => 'WCAG_1_3_1_TABLE_NO_HEADERS',
                        'criterion'  => 'WCAG 1.3.1 (Info and Relationships)',
                        'severity'   => 'critical',
                        'message'    => 'Tabela danych nie posiada zdefiniowanych komórek nagłówka (<th>). Czytniki ekranu nie są w stanie powiązać komórek danych z ich kolumnami.',
                        'suggestion' => 'Zdefiniuj wiersz nagłówkowy przy pomocy <thead> i komórek <th scope="col">.',
                        'snippet'    => htmlspecialchars(substr($fullTable, 0, 120)) . '...'
                    ];
                } else {
                    if (!preg_match('/<th\b[^>]*\bscope\s*=\s*(["\'])(col|row|colgroup|rowgroup)\1/i', $tableContent)) {
                        $violations[] = [
                            'code'       => 'WCAG_1_3_1_TABLE_TH_NO_SCOPE',
                            'criterion'  => 'WCAG 1.3.1 (Info and Relationships)',
                            'severity'   => 'warning',
                            'message'    => 'Komórki nagłówka tabeli (<th>) nie posiadają atrybutu "scope" określającego kierunek nagłówka.',
                            'suggestion' => 'Dodaj atrybut scope="col" dla nagłówków kolumn lub scope="row" dla nagłówków wierszy.',
                            'snippet'    => htmlspecialchars(substr($fullTable, 0, 120)) . '...'
                        ];
                    }
                }
            }
        }

        // 5. Sprawdzenie inline kontrastu i wielkości czcionki (WCAG 1.4.3 AA & 1.4.4)
        self::checkInlineStyles($html, $violations);

        $critCount = 0;
        $warnCount = 0;
        foreach ($violations as $v) {
            if ($v['severity'] === 'critical') {
                $critCount++;
            } else {
                $warnCount++;
            }
        }

        return [
            'passed'     => ($critCount === 0),
            'violations' => $violations,
            'counts'     => [
                'critical' => $critCount,
                'warning'  => $warnCount
            ]
        ];
    }

    /**
     * Parse inline styles, check font sizes, and compute contrast ratios with inheritance
     */
    private static function checkInlineStyles(string $html, array &$violations): void
    {
        // 1. Sprawdzenie bloków Gutenberg z atrybutami kolorów w komentarzach JSON
        if (preg_match_all('/<!--\s*wp:[a-z0-9\/-]+\s*(\{.*?\})\s*-->/is', $html, $blockMatches)) {
            foreach ($blockMatches[1] as $bJson) {
                $blockData = json_decode($bJson, true);
                if (is_array($blockData)) {
                    $textColor = $blockData['textColor'] ?? ($blockData['style']['color']['text'] ?? null);
                    $bgColor   = $blockData['backgroundColor'] ?? ($blockData['style']['color']['background'] ?? null);

                    if ($textColor !== null) {
                        $parsedFg = self::parseCssColor($textColor);
                        $parsedBg = $bgColor ? self::parseCssColor($bgColor) : [255, 255, 255];

                        if ($parsedFg && $parsedBg) {
                            $ratio = self::calculateContrastRatio($parsedFg, $parsedBg);
                            if ($ratio < 4.50) {
                                $violations[] = [
                                    'code'       => 'WCAG_1_4_3_CONTRAST_LOW',
                                    'criterion'  => 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                    'severity'   => 'critical',
                                    'message'    => sprintf('Niedozwolony niski kontrast w bloku Gutenberg (tekst: %s, tło: %s) – współczynnik: %.2f:1 (wymagany minimum 4.50:1). Tekst zlewa się z tłem.', $textColor, $bgColor ?: 'domyślne białe', $ratio),
                                    'suggestion' => 'Zmień kolor tekstu na kontrastowy (np. ciemny na jasnym tle) w panelu ustawień bloku po prawej stronie edytora.',
                                    'snippet'    => htmlspecialchars(substr($bJson, 0, 100))
                                ];
                            }
                        }
                    }
                }
            }
        }

        try {
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8" ?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            $xpath = new \DOMXPath($dom);
            $elements = $xpath->query('//*[@style or contains(@class, "has-white-color") or contains(@class, "has-base-color") or contains(@class, "has-background-color") or contains(@class, "has-text-color")]');

            if ($elements && $elements->length > 0) {
                foreach ($elements as $el) {
                    if (!($el instanceof \DOMElement)) {
                        continue;
                    }

                    $style = $el->getAttribute('style');
                    $class = $el->getAttribute('class');
                    $text = trim($el->textContent);
                    if ($text === '') {
                        continue;
                    }

                    // A. Weryfikacja rozmiaru czcionki (WCAG 1.4.4 / EAA)
                    if (preg_match('/font-size\s*:\s*([^;]+)/i', $style, $fMatch)) {
                        $fsStr = strtolower(trim($fMatch[1]));
                        $val = (float) $fsStr;
                        if ((str_contains($fsStr, 'px') && $val < 11.0) || (str_contains($fsStr, 'pt') && $val < 8.5)) {
                            $violations[] = [
                                'code'       => 'WCAG_1_4_4_FONT_TOO_SMALL',
                                'criterion'  => 'WCAG 1.4.4 (Resize Text)',
                                'severity'   => 'critical',
                                'message'    => sprintf('Zbyt mały rozmiar czcionki (%s) na tekście "%s". Norma WCAG 1.4.4 / EAA wymaga minimalnego rozmiaru co najmniej 11px (zalecane 14-16px dla treści wpisu).', $fsStr, htmlspecialchars(mb_substr($text, 0, 30))),
                                'suggestion' => 'Zwiększ rozmiar czcionki do minimum 12px lub usuń wymuszoną właściwość font-size ze stylu tekstu.',
                                'snippet'    => htmlspecialchars(mb_substr($text, 0, 80))
                            ];
                        }
                    }

                    // B. Weryfikacja kontrastu z dziedziczeniem kolorów w drzewie DOM oraz klasami Gutenberg
                    $fgColor = null;
                    $bgColor = null;
                    $explicitStyle = false;

                    if (str_contains($class, 'has-white-color') || str_contains($class, 'has-base-color') || str_contains($class, 'has-background-color')) {
                        $fgColor = [255, 255, 255];
                        $explicitStyle = true;
                    } elseif (preg_match('/\bhas-([a-z0-9-]+)-color\b/i', $class, $cm)) {
                        $parsed = self::parseCssColor($cm[1]);
                        if ($parsed) {
                            $fgColor = $parsed;
                            $explicitStyle = true;
                        }
                    }

                    if (str_contains($class, 'has-white-background-color') || str_contains($class, 'has-base-background-color')) {
                        $bgColor = [255, 255, 255];
                        $explicitStyle = true;
                    } elseif (preg_match('/\bhas-([a-z0-9-]+)-background-color\b/i', $class, $bm)) {
                        $parsed = self::parseCssColor($bm[1]);
                        if ($parsed) {
                            $bgColor = $parsed;
                            $explicitStyle = true;
                        }
                    }

                    $curr = $el;
                    while ($curr && $curr instanceof \DOMElement && strtolower($curr->tagName) !== 'body') {
                        $st = $curr->getAttribute('style');
                        $cl = $curr->getAttribute('class');

                        if ($fgColor === null) {
                            if (str_contains($cl, 'has-white-color') || str_contains($cl, 'has-base-color') || str_contains($cl, 'has-background-color')) {
                                $fgColor = [255, 255, 255];
                                $explicitStyle = true;
                            } elseif (preg_match('/\bhas-([a-z0-9-]+)-color\b/i', $cl, $cMatch)) {
                                $parsed = self::parseCssColor($cMatch[1]);
                                if ($parsed) {
                                    $fgColor = $parsed;
                                    $explicitStyle = true;
                                }
                            } elseif (preg_match('/(?<!-)\bcolor\s*:\s*([^;]+)/i', $st, $cMatch)) {
                                $parsed = self::parseCssColor($cMatch[1]);
                                if ($parsed) {
                                    $fgColor = $parsed;
                                    $explicitStyle = true;
                                }
                            }
                        }

                        if ($bgColor === null) {
                            if (str_contains($cl, 'has-white-background-color') || str_contains($cl, 'has-base-background-color')) {
                                $bgColor = [255, 255, 255];
                                $explicitStyle = true;
                            } elseif (preg_match('/\bhas-([a-z0-9-]+)-background-color\b/i', $cl, $bgMatch)) {
                                $parsed = self::parseCssColor($bgMatch[1]);
                                if ($parsed) {
                                    $bgColor = $parsed;
                                    $explicitStyle = true;
                                }
                            } elseif (preg_match('/\bbackground(-color)?\s*:\s*([^;]+)/i', $st, $bgMatch)) {
                                $parsed = self::parseCssColor($bgMatch[2]);
                                if ($parsed) {
                                    $bgColor = $parsed;
                                    $explicitStyle = true;
                                }
                            }
                        }
                        $curr = $curr->parentNode;
                    }

                    if ($explicitStyle) {
                        if ($bgColor === null) {
                            $bgColor = [255, 255, 255];
                        }
                        if ($fgColor === null) {
                            $fgColor = [33, 37, 41];
                        }

                        $ratio = self::calculateContrastRatio($fgColor, $bgColor);
                        if ($ratio < 4.50) {
                            $violations[] = [
                                'code'       => 'WCAG_1_4_3_CONTRAST_LOW',
                                'criterion'  => 'WCAG 1.4.3 (Contrast Minimum - AA)',
                                'severity'   => 'critical',
                                'message'    => sprintf('Niedozwolony kontrast koloru tekstu ("%s") – obliczony współczynnik: %.2f:1 (wymagany minimum 4.50:1 dla normy WCAG 2.2 AA). Tekst zlewa się z tłem i jest niewidoczny dla czytelnika.', htmlspecialchars(mb_substr($text, 0, 30)), $ratio),
                                'suggestion' => 'Zwiększ kontrast między kolorem tekstu a tłem (np. ciemny tekst na jasnym tle lub jasny tekst na ciemnym tle) albo usuń wymuszone style kolorystyczne.',
                                'snippet'    => htmlspecialchars(mb_substr($text, 0, 80))
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            // fallback below
        }

        // Regex fallback
        if (preg_match_all('/<([a-z0-9]+)\b[^>]*\bstyle\s*=\s*(["\'])(.*?)\2[^>]*>(.*?)<\/\1>/is', $html, $styleMatches)) {
            foreach ($styleMatches[3] as $idx => $styleContent) {
                $text = trim(strip_tags($styleMatches[4][$idx]));
                if ($text === '') {
                    continue;
                }

                if (preg_match('/font-size\s*:\s*([^;]+)/i', $styleContent, $fMatch)) {
                    $fsStr = strtolower(trim($fMatch[1]));
                    $val = (float) $fsStr;
                    if ((str_contains($fsStr, 'px') && $val < 11.0) || (str_contains($fsStr, 'pt') && $val < 8.5)) {
                        $violations[] = [
                            'code'       => 'WCAG_1_4_4_FONT_TOO_SMALL',
                            'criterion'  => 'WCAG 1.4.4 (Resize Text)',
                            'severity'   => 'critical',
                            'message'    => sprintf('Zbyt mały rozmiar czcionki (%s) na tekście "%s".', $fsStr, htmlspecialchars(mb_substr($text, 0, 30))),
                            'suggestion' => 'Zwiększ rozmiar czcionki do minimum 12px.',
                            'snippet'    => htmlspecialchars(mb_substr($text, 0, 80))
                        ];
                    }
                }

                $fgColor = null;
                $bgColor = null;
                $explicit = false;

                if (preg_match('/(?<!-)\bcolor\s*:\s*([^;]+)/i', $styleContent, $cMatch)) {
                    $fgColor = self::parseCssColor(trim($cMatch[1]));
                    if ($fgColor) $explicit = true;
                }
                if (preg_match('/\bbackground(-color)?\s*:\s*([^;]+)/i', $styleContent, $bgMatch)) {
                    $bgColor = self::parseCssColor(trim($bgMatch[2]));
                    if ($bgColor) $explicit = true;
                }

                if ($explicit) {
                    if ($bgColor === null) $bgColor = [255, 255, 255];
                    if ($fgColor === null) $fgColor = [33, 37, 41];

                    $ratio = self::calculateContrastRatio($fgColor, $bgColor);
                    if ($ratio < 4.50) {
                        $violations[] = [
                            'code'       => 'WCAG_1_4_3_CONTRAST_LOW',
                            'criterion'  => 'WCAG 1.4.3 (Contrast Minimum - AA)',
                            'severity'   => 'critical',
                            'message'    => sprintf('Niedozwolony kontrast tekstu ("%s") – współczynnik: %.2f:1 (wymagany minimum 4.50:1).', htmlspecialchars(mb_substr($text, 0, 30)), $ratio),
                            'suggestion' => 'Zwiększ kontrast między tekstem a tłem.',
                            'snippet'    => htmlspecialchars(mb_substr($text, 0, 80))
                        ];
                    }
                }
            }
        }
    }

    /**
     * 1-Click Auto-Fix Remediation Engine (Automatyczna naprawa WCAG 2.2 AA)
     *
     * @param string $html Original HTML
     * @return array{repairedHtml: string, fixes: array, fixCount: int}
     */
    public static function autoRemediate(string $html): array
    {
        $fixes = [];
        $repaired = $html;

        // 1. Auto-Fix Font Size (< 11px -> 16px)
        $repaired = preg_replace_callback('/(<[^>]+style=["\'][^"\']*font-size\s*:\s*)([0-9.]+(?:px|pt))([^"\']*["\'][^>]*>)/i', function ($m) use (&$fixes) {
            $val = (float) $m[2];
            $unit = str_contains(strtolower($m[2]), 'pt') ? 'pt' : 'px';
            if (($unit === 'px' && $val < 11.0) || ($unit === 'pt' && $val < 8.5)) {
                $fixes[] = [
                    'type'   => 'font_size',
                    'title'  => 'Zbyt mały rozmiar czcionki',
                    'detail' => sprintf('Zwiększono rozmiar czcionki z %s do 16px dla zapewnienia czytelności', $m[2]),
                    'icon'   => '🔤'
                ];
                return $m[1] . '16px' . $m[3];
            }
            return $m[0];
        }, $repaired);

        // 2. Auto-Fix Low Contrast (replace low-contrast colors with high-contrast defaults)
        $repaired = preg_replace_callback('/<([a-z0-9]+)\b([^>]*\bstyle=["\'])([^"\']*)(["\'][^>]*)>(.*?)<\/\1>/is', function ($m) use (&$fixes) {
            $tag = $m[1];
            $prefix = $m[2];
            $style = $m[3];
            $suffix = $m[4];
            $body = $m[5];

            $newStyle = $style;
            $changed = false;

            // Remove unreadable light grey colors on default/light backgrounds
            if (preg_match('/(?<!-)\bcolor\s*:\s*(#[a-f0-9]{3,6}|rgba?\([^)]+\)|white|lightgray|grey|yellow)\b/i', $newStyle, $cm)) {
                $parsed = self::parseCssColor($cm[1]);
                if ($parsed) {
                    $lum = self::calculateRelativeLuminance($parsed);
                    // If text is too bright (lum > 0.45) and no explicit dark background is defined
                    if ($lum > 0.45 && !preg_match('/background(-color)?\s*:\s*(#0|#1|#2|#3|black|dark)/i', $newStyle)) {
                        $newStyle = preg_replace('/(?<!-)\bcolor\s*:\s*[^;]+;?/i', 'color: #111827;', $newStyle);
                        $changed = true;
                        $fixes[] = [
                            'type'   => 'contrast',
                            'title'  => 'Niedozwolony kontrast barwny',
                            'detail' => sprintf('Przywrócono wysoki kontrast WCAG AA (zastąpiono jasny kolor tekstu %s czytelnym kolorem #111827)', $cm[1]),
                            'icon'   => '🎨'
                        ];
                    }
                }
            }

            if ($changed) {
                return "<{$tag}{$prefix}{$newStyle}{$suffix}>{$body}</{$tag}>";
            }
            return $m[0];
        }, $repaired);

        // Auto-Fix Gutenberg white color classes & block comments
        if (str_contains($repaired, 'has-base-color') || str_contains($repaired, 'has-white-color') || preg_match('/"textColor"\s*:\s*"(base|white)"/i', $repaired)) {
            $repaired = str_replace(['has-base-color', 'has-white-color'], 'has-contrast-color', $repaired);
            $repaired = preg_replace('/"textColor"\s*:\s*"(base|white)"/i', '"textColor":"contrast"', $repaired);
            $fixes[] = [
                'type'   => 'contrast',
                'title'  => 'Niedozwolony biały tekst w blokach Gutenberg',
                'detail' => 'Zamieniono kolor tekstu na kontrastowy (has-contrast-color / textColor: contrast) dla zapewnienia normy WCAG 2.2 AA',
                'icon'   => '🎨'
            ];
        }

        // 3. Auto-Fix Images: missing alt attribute or dummy alt
        $repaired = preg_replace_callback('/<img\b([^>]*)>/i', function ($m) use (&$fixes) {
            $attrs = $m[1];
            $full = $m[0];

            if (!preg_match('/\balt\s*=\s*(["\'])(.*?)\1/i', $attrs, $am)) {
                // Determine contextual name from src
                $altText = 'Ilustracja artykułu';
                if (preg_match('/src=["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
                    $base = pathinfo(urldecode($srcMatch[1]), PATHINFO_FILENAME);
                    $cleaned = preg_replace('/[-_]+/', ' ', $base);
                    if (strlen($cleaned) > 2 && !preg_match('/^(dsc|img|photo|pic)/i', $cleaned)) {
                        $altText = ucfirst(trim($cleaned));
                    }
                }
                $fixes[] = [
                    'type'   => 'alt_missing',
                    'title'  => 'Brakujący atrybut alt w obrazie',
                    'detail' => sprintf('Dodano opis alternatywny alt="%s" dla grafiki', htmlspecialchars($altText)),
                    'icon'   => '🖼️'
                ];
                return '<img ' . trim($attrs) . ' alt="' . htmlspecialchars($altText, ENT_QUOTES) . '">';
            }

            $currentAlt = trim($am[2]);
            $dummyPatterns = ['image', 'obraz', 'obrazek', 'zdjęcie', 'photo', 'pic', 'picture', 'grafika', 'bez nazwy', 'untitled', 'dsc_', 'img_'];
            foreach ($dummyPatterns as $dp) {
                if (strcasecmp($currentAlt, $dp) === 0 || str_starts_with(strtolower($currentAlt), $dp)) {
                    $newAlt = 'Ilustracja graficzna – ' . ucfirst($currentAlt);
                    $newAttrs = preg_replace('/\balt\s*=\s*(["\']).*?\1/i', 'alt="' . htmlspecialchars($newAlt, ENT_QUOTES) . '"', $attrs);
                    $fixes[] = [
                        'type'   => 'alt_dummy',
                        'title'  => 'Generyczny opis alt w obrazie',
                        'detail' => sprintf('Zastąpiono pozorny opis alt="%s" precyzyjniejszym alt="%s"', htmlspecialchars($currentAlt), htmlspecialchars($newAlt)),
                        'icon'   => '🖼️'
                    ];
                    return '<img ' . trim($newAttrs) . '>';
                }
            }

            return $full;
        }, $repaired);

        // 4. Auto-Fix Headings: skipped levels (e.g. H1 followed immediately by H3, H4)
        $currentLevel = 1;
        $repaired = preg_replace_callback('/<h([1-6])\b([^>]*)>(.*?)<\/h\1>/is', function ($m) use (&$currentLevel, &$fixes) {
            $lvl = (int) $m[1];
            $attrs = $m[2];
            $content = $m[3];

            if ($currentLevel > 0 && $lvl > $currentLevel + 1) {
                $targetLvl = $currentLevel + 1;
                $fixes[] = [
                    'type'   => 'heading_skipped',
                    'title'  => 'Pominięty poziom w hierarchii nagłówków',
                    'detail' => sprintf('Skorygowano nagłówek <h%d> na poprawny poziom <h%d> (zapobieganie przeskokom w strukturze)', $lvl, $targetLvl),
                    'icon'   => '📑'
                ];
                $currentLevel = $targetLvl;
                return "<h{$targetLvl}{$attrs}>{$content}</h{$targetLvl}>";
            }

            $currentLevel = $lvl;
            return $m[0];
        }, $repaired);

        // 5. Auto-Fix Tables: headless tables converted to <thead> and <th scope="col">
        $repaired = preg_replace_callback('/<table\b([^>]*)>(.*?)<\/table>/is', function ($m) use (&$fixes) {
            $attrs = $m[1];
            $content = $m[2];

            if (!preg_match('/<th\b/i', $content)) {
                // Convert first <tr><td> into <thead><tr><th scope="col">
                $content = preg_replace_callback('/<tr\b([^>]*)>(.*?)<\/tr>/is', function ($trM) use (&$fixes) {
                    static $firstTr = true;
                    if ($firstTr) {
                        $firstTr = false;
                        $headerRow = preg_replace('/<td\b([^>]*)>(.*?)<\/td>/is', '<th scope="col"$1>$2</th>', $trM[2]);
                        $fixes[] = [
                            'type'   => 'table_headers',
                            'title'  => 'Tabela bez komórek nagłówkowych',
                            'detail' => 'Przekształcono pierwszy wiersz tabeli w nagłówki <th scope="col"> dla czytników ekranu',
                            'icon'   => '📊'
                        ];
                        return "<thead><tr{$trM[1]}>{$headerRow}</tr></thead>";
                    }
                    return $trM[0];
                }, $content);
            } else {
                // Add scope="col" to <th> missing scope
                $content = preg_replace_callback('/<th\b([^>]*)>/i', function ($thM) use (&$fixes) {
                    if (!preg_match('/\bscope\s*=/i', $thM[1])) {
                        $fixes[] = [
                            'type'   => 'table_scope',
                            'title'  => 'Brak atrybutu scope w komórce <th>',
                            'detail' => 'Dodano atrybut scope="col" do nagłówka tabeli',
                            'icon'   => '📊'
                        ];
                        return '<th scope="col" ' . trim($thM[1]) . '>';
                    }
                    return $thM[0];
                }, $content);
            }

            return "<table{$attrs}>{$content}</table>";
        }, $repaired);

        // 6. Auto-Fix Links: empty links and generic anchor text
        $repaired = preg_replace_callback('/<a\b([^>]*)>(.*?)<\/a>/is', function ($m) use (&$fixes) {
            $attrs = $m[1];
            $body = $m[2];
            $textOnly = trim(strip_tags($body));

            // Completely empty link -> remove or add placeholder
            if (trim($body) === '' && !preg_match('/\baria-label\s*=/i', $attrs)) {
                $fixes[] = [
                    'type'   => 'empty_link',
                    'title'  => 'Pusty martwy link',
                    'detail' => 'Usunięto pusty odnośnik <a> pozbawiony tekstu i etykiety',
                    'icon'   => '🔗'
                ];
                return '';
            }

            // Ambiguous link text ("kliknij tutaj", "more")
            $genericWords = ['kliknij tutaj', 'tutaj', 'więcej', 'czytaj więcej', 'zobacz więcej', 'link', 'pobierz', 'click here', 'more', 'read more', 'download'];
            if (in_array(mb_strtolower($textOnly), $genericWords, true) && !preg_match('/\baria-label\s*=/i', $attrs)) {
                $newAttrs = trim($attrs) . ' aria-label="' . htmlspecialchars('Szczegółowe informacje: ' . $textOnly, ENT_QUOTES) . '"';
                $fixes[] = [
                    'type'   => 'ambiguous_link',
                    'title'  => 'Niejednoznaczny tekst linku',
                    'detail' => sprintf('Dodano atrybut aria-label dla odnośnika "%s"', htmlspecialchars($textOnly)),
                    'icon'   => '🔗'
                ];
                return "<a {$newAttrs}>{$body}</a>";
            }

            return $m[0];
        }, $repaired);

        return [
            'repairedHtml' => $repaired,
            'fixes'        => $fixes,
            'fixCount'     => count($fixes)
        ];
    }

    /**
     * Calculate relative luminance according to W3C WCAG formula
     */
    public static function calculateRelativeLuminance(array $rgb): float
    {
        $r = $rgb[0] / 255.0;
        $g = $rgb[1] / 255.0;
        $b = $rgb[2] / 255.0;

        $r = ($r <= 0.03928) ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
        $g = ($g <= 0.03928) ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
        $b = ($b <= 0.03928) ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);

        return (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);
    }

    /**
     * Compute contrast ratio: (L1 + 0.05) / (L2 + 0.05)
     */
    public static function calculateContrastRatio(array $c1, array $c2): float
    {
        $l1 = self::calculateRelativeLuminance($c1);
        $l2 = self::calculateRelativeLuminance($c2);

        $lighter = max($l1, $l2);
        $darker  = min($l1, $l2);

        return ($lighter + 0.05) / ($darker + 0.05);
    }

    /**
     * Helper to parse hex or rgb() css color into [r, g, b] array
     */
    public static function parseCssColor(string $str): ?array
    {
        $str = strtolower(trim($str, " \t\n\r\0\x0B;\"'"));

        if (preg_match('/^#([a-f0-9]{3}|[a-f0-9]{6})$/i', $str, $m)) {
            $hex = $m[1];
            if (strlen($hex) === 3) {
                return [
                    hexdec($hex[0] . $hex[0]),
                    hexdec($hex[1] . $hex[1]),
                    hexdec($hex[2] . $hex[2])
                ];
            }
            return [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2))
            ];
        }

        if (preg_match('/rgba?\s*\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})/i', $str, $m)) {
            return [(int) $m[1], (int) $m[2], (int) $m[3]];
        }

        $names = [
            'white'      => [255, 255, 255],
            'base'       => [255, 255, 255],
            'background' => [255, 255, 255],
            'accent-5'   => [251, 250, 243],
            'contrast'   => [17, 17, 17],
            'black'      => [0, 0, 0],
            'gray'       => [128, 128, 128],
            'grey'       => [128, 128, 128],
            'silver'     => [192, 192, 192],
            'darkgray'   => [169, 169, 169],
            'darkgrey'   => [169, 169, 169],
            'lightgray'  => [211, 211, 211],
            'lightgrey'  => [211, 211, 211],
            'red'        => [255, 0, 0],
            'green'      => [0, 128, 0],
            'blue'       => [0, 0, 255],
            'yellow'     => [255, 255, 0],
            'orange'     => [255, 165, 0],
            'purple'     => [128, 0, 128],
            'navy'       => [0, 0, 128],
            'teal'       => [0, 128, 128]
        ];

        return $names[$str] ?? null;
    }
}

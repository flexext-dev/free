=== FlexExt Darowizny dla Przelewy24 Lite ===
Contributors: flexext
Donate link: https://flexext.dev
Tags: darowizna, darowizny, przelewy24, blik, fundacja
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.2.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Szybkie darowizny online w Polsce przez Przelewy24 (BLIK, karty, szybki przelew). Lekki widżet dla fundacji, stowarzyszeń i twórców.

== Description ==

**FlexExt Darowizny dla Przelewy24 Lite** to lekka, bezpieczna i dostępna cyfrowo wtyczka WordPress stworzona do zbierania **darowizn i wpłat online** w Polsce za pośrednictwem oficjalnej bramki płatniczej **Przelewy24 (PayPro SA)**.

Zaprojektowana specjalnie dla polskich organizacji pozarządowych (NGO), **fundacji**, **stowarzyszeń**, parafii, zbiórek charytatywnych, twórców internetowych oraz projektów open-source. Odwiedzający mogą przekazać wsparcie błyskawicznie za pomocą najpopularniejszych metod płatności w Polsce: **BLIK**, szybkich przelewów online (pay-by-link ze wszystkich polskich banków) oraz kart płatniczych (Visa, Mastercard, Apple Pay, Google Pay).

Darczyńca wybiera jeden z trzech skonfigurowanych progów kwotowych w polskich złotych (PLN) i jednym kliknięciem przechodzi bezpośrednio do bezpiecznej płatności Przelewy24.

### Główne możliwości (Wersja Lite)

* **0% Dodatkowych Prowizji:** Wtyczka nie pobiera żadnych opłat pośrednich ani prowizji od wpłat. 100% zebranych środków trafia bezpośrednio na Twoje konto sprzedawcy w Przelewy24.
* **BLIK i wszystkie polskie banki:** Natywna obsługa płatności BLIK, mBank, PKO BP, Santander, ING, Bank Pekao, Alior Bank, Millennium oraz wszystkich banków obsługiwanych przez Przelewy24.
* **3 Konfigurowalne Kwoty Darowizn:** Ustaw dowolne trzy progi kwotowe w PLN (np. 10 zł, 20 zł, 50 zł lub 20 zł, 50 zł, 100 zł), aby maksymalnie ułatwić darczyńcom szybką wpłatę.
* **Niezwykle lekka i szybka:** 100% lokalne renderowanie w WordPressie bez zewnętrznych skryptów śledzących i bez obciążania strony.
* **Pełna dostępność cyfrowa (WCAG 2.1 AA):** Wysoki kontrast kolorów, pełna obsługa klawiatury (wyraźny focus) oraz semantyczne atrybuty ARIA.
* **Tryb testowy (Sandbox):** Wbudowana obsługa środowiska testowego Przelewy24 Sandbox pozwala przetestować cały proces wpłaty przed uruchomieniem zbiórki na żywo.
* **Prosty Shortcode:** Osadź widżet darowizn na dowolnej stronie, we wpisie lub w sekcji za pomocą krótkiego kodu `[flexext_lite]`.

### Potrzebujesz większych możliwości? Sprawdź wersję FlexExt PRO

Dla rozbudowanych kampanii zbiórkowych odwiedź [stronę FlexExt Darowizny Przelewy24 PRO](https://flexext.dev/en-gb/product/p24-donations-pro-wordpress?utm_source=wporg&utm_medium=readme&utm_campaign=p24-lite), aby poznać **FlexExt Darowizny Przelewy24 PRO**:
* Pole własnej kwoty darowizny (darczyńca może wpisać dowolną kwotę)
* Obsługa wielu walut (PLN, EUR, USD, GBP)
* Pasek postępu celu zbiórki z licznikiem wpłat na żywo
* Pływający widżet darowizn (floating widget) na urządzeniach mobilnych i komputerach
* Publiczna lista darczyńców z opcją wpłaty anonimowej i komentarzem wspierającego
* Własne logo, pełna personalizacja kolorów oraz dedykowane strony podziękowania

---

### English Summary / Description in English

**FlexExt Darowizny dla Przelewy24 Lite** is a lightweight, secure, and accessible WordPress donation plugin built for collecting online donations (**darowizna / darowizny**) in Poland through the **Przelewy24 (PayPro SA)** payment gateway.

Designed for Polish non-profit organizations, foundations (**fundacje**), associations (**stowarzyszenia**), charities, parishes, content creators, and open-source projects. Visitors can donate instantly using Poland's most popular payment methods: **BLIK**, fast online bank transfers (pay-by-link), and credit/debit cards (Visa, Mastercard).

* **0% Platform Fees:** 100% of donation funds go directly to your Przelewy24 merchant account.
* **3 Configurable Fixed Amounts:** Set three preset donation tiers in Polish Zloty (PLN).
* **Accessible (WCAG 2.1 AA) & Lightweight:** Local rendering with zero tracking scripts.
* **Sandbox Mode:** Test transactions via `sandbox.przelewy24.pl` before going live.
* **Easy Shortcode:** Place `[flexext_lite]` on any page or post.

== External Services ==

Wtyczka komunikuje się bezpośrednio z bramką płatniczą **Przelewy24** (prowadzoną przez PayPro SA, ul. Pastelowa 8, 60-198 Poznań) w celu rejestracji i procesowania transakcji płatniczej w momencie kliknięcia przycisku wpłaty przez darczyńcę.

This plugin communicates directly with the **Przelewy24 Payment Gateway** (operated by PayPro SA, ul. Pastelowa 8, 60-198 Poznań, Poland) to register and process payment transactions when a donor submits a donation:
* **Dostawca usługi / Service Provider:** PayPro SA (Przelewy24)
* **Wykorzystywane adresy API / Endpoints Used:**
  * Produkcja / Production: `https://secure.przelewy24.pl/api/v1/transaction/register`
  * Testy / Sandbox Testing: `https://sandbox.przelewy24.pl/api/v1/transaction/register`
* **Przesyłane dane / Data Transmitted:** ID sprzedawcy (Merchant ID), POS ID, identyfikator sesji transakcji (session ID), kwota darowizny, waluta (PLN), opis darowizny, kod języka oraz adres e-mail konta sprzedawcy.
* **Dostawca / Provider:** This service is provided by "PayPro SA (Przelewy24)": [terms of use](https://www.przelewy24.pl/regulamin) (Regulamin Przelewy24), [privacy policy](https://www.przelewy24.pl/obowiazek-informacyjny-rodo-platnicy) (Obowiązek informacyjny RODO - płatnicy).

Żadne dane osobowe odwiedzających ani klucze płatności nie są wysyłane do serwerów flexext.dev ani innych podmiotów trzecich. Wszystkie zapytania API odbywają się bezpośrednio między Twoim serwerem a PayPro SA. (No personal visitor data or payment credentials are sent to flexext.dev or any other third-party servers.)

== Installation ==

1. Zainstaluj wtyczkę bezpośrednio z katalogu WordPress lub prześlij archiwum `.zip` w menu **Wtyczki > Dodaj nową > Wyślij wtyczkę na serwer** (`/wp-content/plugins/`).
2. Aktywuj wtyczkę w menu **Wtyczki** w panelu WordPress.
3. Przejdź do zakładki **P24 Darowizny** w bocznym menu administratora WordPress.
4. Wpisz swoje dane z panelu Przelewy24: **ID Sprzedawcy (Merchant ID)**, **Klucz do CRC**, **Klucz do raportów (API)** oraz **Adres e-mail płatności**.
5. Zaznacz opcję **Włącz widżet** i kliknij **Zapisz ustawienia**.
6. Wstaw shortcode `[flexext_lite]` na dowolnej stronie lub we wpisie, w którym ma wyświetlać się widżet darowizn.

== Frequently Asked Questions ==

= Czy potrzebuję konta sprzedawcy w Przelewy24? (Do I need a Przelewy24 merchant account?) =
Tak. Aby przyjmować darowizny, potrzebujesz aktywnego konta w serwisie Przelewy24 (PayPro SA) lub bezpłatnego konta testowego Przelewy24 Sandbox do testów.

= Czy wtyczka pobiera prowizję od darowizn? (Does the plugin charge any transaction fees?) =
Nie. Wtyczka FlexExt pobiera 0% prowizji. Rozliczasz się wyłącznie bezpośrednio z operatorem Przelewy24 zgodnie ze swoją tabelą opłat.

= Czy darczyńcy mogą wpłacać przez BLIK? (Can donors pay using BLIK?) =
Tak! Bramka Przelewy24 natywnie obsługuje szybkie płatności BLIK, przelewy online z polskich banków oraz karty płatnicze.

= Gdzie znajdę Merchant ID, klucz CRC oraz klucz do raportów (API)? =
Zaloguj się do swojego panelu Przelewy24 i przejdź do zakładki **Moje konto > Dane konta** — tam skopiujesz ID Sprzedawcy (Merchant ID), Klucz do CRC oraz Klucz do raportów (API).

= Jak przetestować płatności przed uruchomieniem zbiórki? =
Włącz opcję **Tryb testowy (Sandbox)** w ustawieniach wtyczki i wprowadź dane ze swojego konta testowego Przelewy24 Sandbox.

= Czy mogę zmienić 3 domyślne kwoty darowizn? =
Tak, w ustawieniach wtyczki (**P24 Darowizny**) możesz wpisać dowolne 3 kwoty w PLN (np. 20, 50, 100).

== Changelog ==

= 1.2.2 =
* Dokumentacja zewnętrznej usługi: linki do regulaminu (terms of use) i polityki prywatności (privacy policy) Przelewy24 w formacie wymaganym przez WordPress.org. / External services section: terms of use and privacy policy links in the format required by WordPress.org.

= 1.2.1 =
* Dodano dwujęzyczny opis PL/EN w pliku readme.txt oraz pełne polskie tłumaczenia interfejsu (pl_PL.po, pl_PL.mo, pl_PL.l10n.php).
* Zmieniono nazwę i slug wtyczki na "FlexExt Darowizny dla Przelewy24 Lite" (flexext-darowizny-dla-przelewy24-lite) zgodnie z wytycznymi katalogu WordPress.org.
* Usunięto link w stopce widżetu zgodnie z punktem 10 wytycznych WordPress.org.
* Ujednolicono domenę tekstową we wszystkich funkcjach tłumaczeń.

= 1.2.0 =
* Pełna zgodność z audytem WordPress.org Plugin Check (PCP).
* 100% lokalne renderowanie widżetu oraz wydzielenie zasobów do widget.css i widget.js.
* Dostępność cyfrowa zgodna ze standardem WCAG 2.1 AA.
* Dokumentacja zewnętrznej usługi Przelewy24 (PayPro SA).

= 1.1.1 =
* Aktualizacja polskich tłumaczeń.

= 1.0.0 =
* Pierwsze wydanie publiczne.

# Why We Engineered a Zero-Bloat Native E-Commerce Suite for Joomla 5 & 6 (Architecture & Benchmarks)

> **Summary:** How modern PHP 8.2+, Joomla 5/6 Dependency Injection, EU Omnibus 30-day price tracking, and zero-jQuery Vanilla JS deliver sub-100ms storefront responses.
>
> 🔗 **Original Article:** [https://flexext.dev/en-gb/blog/how-to-build-online-store-in-joomla-6-step-by-step-guide](https://flexext.dev/en-gb/blog/how-to-build-online-store-in-joomla-6-step-by-step-guide) | 🌐 **Official Store:** [https://flexext.dev](https://flexext.dev) | 🛒 **Joomla Store:** [https://flexext.store](https://flexext.store) | 🔍 **Free AI Scanner:** [https://aichecker.flexext.dev](https://aichecker.flexext.dev)

---

Joomla 5 and Joomla 6 quietly became one of the cleanest, fastest PHP 8.2+ content management architectures on the market: native PSR-4 autoloading, a proper Dependency Injection Container, WebAssetManager, strong core ACL, and rock-solid security without needing 35 third-party plugins just to add custom fields or multilingual routing.

Yet whenever an organization, university, publisher, or B2B merchant running Joomla needed e-commerce, they hit the same wall: **legacy cart components carrying 10–15 years of Joomla 2.5/3 backward-compatibility layers, inline jQuery scripts, and outdated Bootstrap 2/3 markup**.

That gap is why we built **[FlexExt Store](https://flexext.store/)** — a modern, modular native e-commerce suite engineered directly for **Joomla 4, 5, and 6**.

---

## 1. Pure PSR-4 Services & Zero Legacy Overhead

Instead of wrapping legacy global helper classes, every subsystem in FlexExt Store (pricing, tax calculation, EU Omnibus history, cart sessions, digital licensing, and payment webhooks) runs through clean namespaced services on PHP 8.1–8.4:

- **Zero Composer `vendor/` conflicts:** Everything ships using Joomla's native APIs and clean, self-contained classes so updates never collide with other site extensions.
- **Vanilla JS Storefront:** Cart drawers, live variant price recalculation, and 1-page checkout run without forcing jQuery onto your frontend Core Web Vitals.

---

## 2. Built-In EU Omnibus 30-Day Lowest Price Compliance

Across the European Union, the **Omnibus Directive** requires merchants displaying a promotional discount to show the lowest price from the 30 days preceding the reduction.

Rather than bolting this on via slow runtime log scans, FlexExt Store records price transitions atomically at save time — including edits made inside the **Excel-like Bulk Price & Inventory Manager** — so rendering the verified 30-day lowest price adds zero extra query overhead on high-traffic category pages.

---

## 3. Native Digital Products, Software Licenses & AI Discovery

Because many Joomla sites sell memberships, courses, PDFs, tickets, or software alongside physical goods, we built:
- **Encrypted Digital Downloads & License Key Generation** right into the core product editor.
- **Automatic `Schema.org Product` + `FAQPage` JSON-LD** and AI Agent Markdown endpoints (`/llms.txt` ready — testable via [AI Website Checker](https://aichecker.flexext.dev/)).
- **WCAG 2.2 AA Light & Dark Contrast Compliance** across both the Joomla Administrator UI and customer storefront templates.

---

## Try the Free Starter Edition or Explore the Docs

We maintain a **100% Free Basic Starter Edition** listed on the official Joomla Extensions Directory (JED) alongside commercial Standard, Pro, and Multi-Site Ultimate tiers:

- **Overview, Live Demo & Editions:** [https://flexext.store/](https://flexext.store/)
- **FlexExt Store Basic Free (Joomla 4/5/6):** [https://flexext.dev/en-gb/product/flexext-store-basic-free-joomla-ecommerce](https://flexext.dev/en-gb/product/flexext-store-basic-free-joomla-ecommerce)
- **FlexExt Store Pro (Joomla E-Commerce):** [https://flexext.dev/en-gb/product/flexext-store-pro-joomla-ecommerce](https://flexext.dev/en-gb/product/flexext-store-pro-joomla-ecommerce)
- **FlexExt Store Ultimate Multi-Site:** [https://flexext.dev/en-gb/product/flexext-store-ultimate-joomla-ecommerce](https://flexext.dev/en-gb/product/flexext-store-ultimate-joomla-ecommerce)
- **Step-by-Step Joomla 6 Store Guide:** [https://flexext.dev/en-gb/blog/how-to-build-online-store-in-joomla-6-step-by-step-guide](https://flexext.dev/en-gb/blog/how-to-build-online-store-in-joomla-6-step-by-step-guide)
- **Full Developer & Admin Documentation:** [https://docs.flexext.store/](https://docs.flexext.store/)

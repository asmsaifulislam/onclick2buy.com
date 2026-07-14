# OnClick2Buy — Beginner Store-Launch Roadmap

A clear, step-by-step path from "just installed" to "ready to sell". Everything below uses only the
**free, built-in features** already in this Laravel store. Paid add-ons (AI recommendations, EMI
calculators, premium themes) can come later, once you're earning.

## Phase 1 — Foundations (do first)
1. **Store settings** — Admin → Store Settings: store name, tagline, contact, social links, announcement bar.
2. **Categories** — Admin → Categories: create the main departments (e.g. Electronics, Fashion, Home).
3. **Payment methods** — Admin → Payment Methods: enable bkash / nagad / rocket / COD as needed.
4. **Shipping methods** — Admin → Shipping: add at least one method (a default "Standard Delivery" already exists; free over ৳500).
5. **Tax rates** — Admin → Tax Rates: set VAT/rate (defaults to 0% — change only if required).

## Phase 2 — Add products (free PDP features)
For each product (Admin → Products → Create):
- Title, description, price, **SKU**, **stock**
- **Image gallery** (multiple photos, zoom + 360° view on the page)
- **Variants** — Size / Color / Material (chips on the page) + optional **Variant Combinations** with their own price & stock
- **SEO** — meta title / description / keywords (helps Google find you)
- **Warranty** note (add in description or a variant)
- Mark **Active**

## Phase 3 — Trust & conversion (free)
- **Reviews & ratings** — customers rate products (shows stars on the page)
- **Wishlist** — customers save favorites (heart icon in the nav)
- **Coupons** — Admin → Coupons: create percent or fixed discounts; customers apply them at cart
- **Secure checkout** — SSL payment gateway + order summary with subtotal, discount, shipping, tax, total
- **Stock status** — "In Stock / Out of Stock" badges; low-stock handled automatically

## Phase 4 — Go live & grow
1. Test the full flow on the live site: browse → add to cart → apply coupon → checkout → pay.
2. Submit the sitemap / meta to Google Search Console (SEO fields feed `<title>` + meta tags).
3. Share on social (links set in Store Settings).
4. Watch **Analytics** + **360° BI** in Admin for what sells.

## When to add paid upgrades (later, not now)
- AI product recommendations
- EMI / installment calculator
- Premium custom theme
- Advanced marketing automations (Mautic/ERPNext already wired, tune as needed)

> Tip: Launch with the free features, prove demand, then reinvest revenue into the paid add-ons above.

# Second Sitting — WordPress child theme

Child theme of **Twenty Twenty-Five** (fonts: Poppins, with Cormorant Garamond kept for the SS logo) with the Second Sitting homepage, branded WooCommerce pages and the 70km-from-Harrow delivery check.

## Install

1. Make sure **Twenty Twenty-Five** and **WooCommerce** are installed.
2. Appearance → Themes → Add New → Upload → `second-sitting.zip` → Activate.
3. Activating the theme creates a **Home** page with the *Second Sitting Home* template, sets it as the homepage and creates the sofa categories. The front page always renders this design.
   Any text typed into the Home page's editor replaces the About Us copy.
4. Click **Import sample sofas** in the blue admin notice (or Tools → Second Sitting) to load 8 demo sofas with photos, a bundle and a sold item. Running it again updates them rather than duplicating.
5. Appearance → Customize → **Second Sitting**: collection address, phone/WhatsApp, email, "Book a viewing" link, Facebook/Instagram.
6. Appearance → Menus: assign "Second Sitting: main menu", "footer menu" and "legal links" (a sensible default shows until you do).

## WooCommerce setup

- **Shipping:** WooCommerce → Settings → Shipping → add a UK zone with your delivery methods (e.g. *Same-day delivery*, *Next-day delivery* as Flat rate) and **Local pickup** ("Collect from Harrow"). If no pickup method exists, the theme adds a free "Collect from Harrow" option itself.
- **Payment ("we confirm before payment"):** enable **Direct bank transfer** or **Cash on delivery** and rename it e.g. "Pay once we confirm delivery". Orders arrive *On hold* so the owner calls the customer first.
- **Categories** used by the homepage tiles (slugs): `corner-sofas`, `cuddle-chairs`, `modular-sofas`, `sofa-sets`. Add more (e.g. `2-seater`, `3-seater`) and they appear as filter chips in the shop.
- **Attributes** for shop filters: Products → Attributes → add `Brand` and `Colour`.
- **Per product (General tab):** Brand, Seat size, Condition notes, Bundle partner product ID + Bundle price.
  Regular price = RRP, Sale price = Second Sitting price (the card shows RRP struck through and the saving).
- Stock: set stock to 1 / "In stock". When it sells it shows as **Sold** for 5 days, then drops off (change `SS_SOLD_VISIBLE_DAYS` in `wp-config.php`). Turn **off** "Hide out of stock items" in WooCommerce → Settings → Products → Inventory.

## Delivery check (inc/postcode.php)

- Postcodes are geocoded with the free **postcodes.io** API (no key), cached 30 days.
- Radius: 70km from Harrow (`SS_DELIVERY_RADIUS_KM`). For the exact base point:
  `add_filter( 'ss_base_location', fn() => array( 51.5806, -0.3420 ) );`
- Where it runs:
  - Homepage hero and product pages: AJAX check; "Reserve" stays locked until checked.
  - Checkout: message above shipping. Inside → *"Great news, we deliver to you"* with the delivery options. Outside → *"We don't deliver to your area yet, but you're welcome to collect from Harrow"* and only collection is offered.
  - Server-side validation blocks any delivery order outside the radius.
  - The order screen in admin shows the result and distance.

## Templates

| View | File |
|---|---|
| Homepage | `templates/template-home.php` |
| Shop / categories | `templates/archive-product.php` + `woocommerce/content-product.php` |
| Product | `templates/single-product.php` |
| Cart / Checkout / Thank you / Account | `templates/cart.php`, `checkout.php`, `my-account.php` (shared `woo-page.php`) |

Cart and Checkout render the classic WooCommerce shortcodes so the postcode rules apply even if those pages contain the Cart/Checkout blocks.

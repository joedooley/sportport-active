=== Conversio for WooCommerce ===
Contributors: conversio
Tags: conversio, conversio woocommerce, receipt, receipts, invoice, email, order confirmation, order mail, ecommerce mail, receiptful, receiptful woocommerce
Requires at least: 3.7.0
Tested up to: 4.7.2
Stable tag: 1.3.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Conversio is the all-in-one marketing dashboard for your WooCommerce store.

== Description ==

= Why use Conversio? =

Three quick reasons to start using Conversio today:

1. It's Your All-in-One Marketing Dashboard.
2. All Conversio tools are intelligent, integrated and automated. *Sell More. Do Less.*
3. Start using our famous supercharged email receipts for free today.

Overview of all available tools:

**Receipts (included in the Free plan)**
The unsung Ecommerce hero. Drive more revenue. Increase sales by 5% in just a couple of minutes for FREE.

**Abandoned Cart Emails**
Don’t leave money on the table. Remind your customers of all the things they wanted, but didn’t end up buying. On average, 70% of your customers will abandon their carts. We can help you recover that $$.

**Followup Emails (limited functionality included in the Free plan)**
Followup for repeat revenue. Optimize your customer interactions and boost revenue by reminding your customers of how awesome you are.The probability of selling to an existing customer is 60% to 70% and we want to help you take advantage of that.

**Newsletters**
Create newsletters and create a lasting relationship. Easily design and send beautiful newsletters that your customers want to read. And don’t forget to include some Conversio Magic marketing components to increase sales too.

**Product Reviews**
Trust is built on product reviews. Activate this feature to increase your conversions and revenue. More than 60% of consumers say they research and read product reviews before buying.

**Recommendations**
Like our other revenue driving features? You’ll like this too...Use recommendations to inform your customers about other products in your store that they’d probably like to purchase.

**Search**
Make customer experiences a breeze and get more cash in your pocket. Help your customers find EXACTLY what they are looking for.

**Feedback (included in the Free plan)**
Help potential customers know exactly how great your store is, good feedback builds trust = additional revenue ($$).

**Quick & Eeasy Setup**
Installing Conversio is simple.

1. Download & Activate this plugin
2. Sign up for a free Conversio account
3. Paste your API key in your site
4. Tweak your receipt design and start sending supercharged receipts
5. Explore all of the other Premium tools

Simple huh?!

**Need help?**
[Open a support ticket](https://wordpress.org/support/plugin/receiptful-for-woocommerce), We're here to make your life easier!

**Please Note:** You require a Conversio account ([sign up here for a 100% **FREE** account](http://conversio.com))

== Installation ==

1. Go to the **Plugins > Add New page** in your WordPress admin.
2. Search for "Conversio" and install the "Conversio for WooCommerce" plugin.
3. Click **Activate Plugin**.
4. Go to **WooCommerce > Settings > Conversio** and enter your Receiptful API key. (If you haven't signed up for Conversio yet, now's the time!)

That's it! You can now manage everything about your Conversio setup right from your Conversio dashboard. :)

== Frequently Asked Questions ==

= Do I need to modify any code? =

Nope - we take care of everything for you. Just install the plugin, add your API key and you’ll be good to go!

= Does Conversio work with my theme(s)? =

Yes, Conversio works with any theme - whether free, commercial or custom. You do however need WooCommerce activated for Conversio to work.

== Screenshots ==

1. Use a simple drag-and-drop template builder to create your receipt.
2. Use the set up wizard to easily create new Followup & Abandoned Cart emails.
3. Create high performing email newsletters with upsell modules, using a simple drag-and-drop template editor.
4. Use the Reviews module to collect product reviews directly inside your emails, then display them on your product pages using the Reviews widget.
5. Enable our Recommendations widget to make intelligent & personalized product recommendations on your product pages.


== Changelog ==

= 1.3.4 - 15/02/2017 =

* [Improvement] - Load tracking script in the footer instead of header

= 1.3.3 - 01/11/2016 =

* [Improvement] - Rename Receiptful to Conversio
* [Add] - Product Reviews widget shortcode
* [Add] - Email Newsletters widget shortcode

= 1.3.2 - 18/10/2016 =

* [Fix] - Remove cookie on thank you page. Ensures proper tracking

= 1.3.1 - 21/09/2016 =

* [Add] - Support for 'product' attribute in [rf_recommendations product='123'] shortcode
* [Improvement] - Remove the DELETE abandoned cart requests, now handled through Receiptful
* [Improvement] - Sanitize product image URL so special URLs are handled better
* [Improvement] - Include discount tax in the total discount amount
* [Fix] - Warning in WP 4.6+ when recovering abandoned cart

= 1.3.0 - 10/06/2016 =

* [Add] - Feedback widget shortcode
* [Improvement] - Keep all relevant URL parameters from the when redirecting from the abandoned cart (e.g. utm parameters are no longer removed)

= 1.2.5 - 06/05/2016 =

* [Add] - Re-sync products for data accuracy with new features.
* [Fix] - Fix error when sending a receipt including a product that doesn't exist.
* [Add] - Allow check to see if Receiptful is activated.

= 1.2.4 - 24/03/2016 =

* [Fix] - Rare receipt resend loop when the API responds with 50x

= 1.2.3 - 04/03/2016 =

* [Add] - Support for Receiptful search
* [Add] - Support for WooThemes Sensei

= 1.2.2 - 11/01/2016 = Happy new year!

* [Add] - 'Clear unused coupons' feature in WooCommerce -> System -> Tools area

= 1.2.1 - 02/12/2015 =

* [Add] - Make sure abandoned cart is removed after purchase
* [Fix] - Redirect to cart with proper parameters

= 1.2.0 - 25/11/2015 =

* [Add] - Abandoned cart functionality

= 1.1.13 - 06/10/2015 =

* [Add] - 'Synchronize orders' feature in the WooCommerce -> System -> Tools area
* [Improvement] - Use order currency instead of shop currency (supports multi-currency shops)

= 1.1.12 - 01/09/2015 =

* [Improvement] - Update products on sale price start/expiry (accuracy)
* [Add] - 'Synchronize products' feature in the WooCommerce -> System -> Tools area

= 1.1.11 - 06/08/2015 =

* [Improvement] - Coupon expiry is now always will end of day that is promoted on the receipt
* [Improvement] - Update product when scheduled sale price starts/ends
* [Improvement] - Allow some HTML in product note field
* [Fix] - No longer initiate order sync on every update

= 1.1.10 - 21/07/2015 =

* [Improvement] - Improved product thumbnails, less blurry images on edge cases.
* [Fix] - Recommendations weren't showing headers/titles (overridden) fixed now.

= 1.1.9 - 15/07/2015 =

* [Add] - Product image to the API call, allow to show the product image on the receipt.
* [Improvement] - Update products to not be recommended when going out of stock.
* [Improvement] - Allow custom shortcode attributes. read more; https://app.receiptful.com/recommendations/instructions.
* [Add] - Re-sync all orders to improve our data.

= 1.1.8 - 28/05/2015 =

* [Fix] - Javascript error when recommendations are not enabled.
* [Improvement] - Add used order coupons to the API call.
* [Deprecated] - Receiptful()->print_scripts() will be automatically from now on in receiptful.init().

= 1.1.7 - 22/05/2015 =

* [Add] - Cart product IDs to recommendation init. Ensures you can use recommendations in the cart.

= 1.1.6 - 19/05/2015 =

* [Add] - Add recommendation options
* [Add] - Page tracking
* [Improvement] - Set out of stock products to hidden within Receiptful

= 1.1.5 - 27/04/2015 =

* [Fix] - WooCommerce 2.2.x compatibility notice with wc_tax_enabled()
* [Improvement] - WPML won't break checkout
* [Improvement] - Strip shortcodes from product descriptions
* [Improvement] - Pass protected, draft, hidden, private products are now synced as hidden=true

= 1.1.4 - 09/04/2015 =

* [Add] - Product pageview tracking for personalised product recommendations
* [Improvement] - Add Javascript defined checks
* [Improvement] - Cleanup unused receipt api args
* [Improvement] - Small refactor coupon creation

= 1.1.3 - 01/04/2015 =

* [Fix] - Typo in filter name 'receiptful_api_args_related_products'
* [Improvement] - Prevent shipping coupons from having discount amounts
* [Improvement] - Prevent getting related products in the initial product sync
* [Improvement] - Automatically picking up Tax/totals translation from WooCommerce
* [Improvement] - Prevent notice when API doesn't return the 'products' parameter

= 1.1.2 - 12/03/2015 =

* [Add] - Receipt sync for better recommendations
* [Add] - Order note support
* [Add] - Product note support
* [Improvement] - Changed 'Shipping' to the actual shipping title
* [Improvement] - Changed textdomain to 'receiptful' for consistency
* [Prevent] - Prevent notice in upcoming Receiptful update

= 1.1.1 - 05/03/2015 =

* [Add] - Product sync for better recommendations
* [Fix] - load translation files
* [Improvement] - Subtotals refactor
* [Improvement] - CDN for JavaScript - Improving loading time
* [Improvement] - Small queue improvements (don't add 400 response to queue)
* [Improvement] - Subscriptions email notifications

= 1.1.0 - 28/01/2015 =

* [Add] - Unit tests!
* [Add] - WooCommerce 2.3 support
* [Add] - Filters & hooks for extending/modifying
* [Add] - Receipt comparison screenshot, you should see it ;-)
* [Improvement] - Payment method to the receipt
* [Improvement] - Date parameter to the API call to keep order date/time equal
* [Improvement] - Support for multiple download URLs
* [Improvement] - Split up compatibility files in separate file
* [Improvement] - Email class refactor

= 1.0.5 - 14/01/2015 =

* [Happy New Year!]
* [Improvement] - Refactored email WC overrides
* [Fix] - Warning when descriptions < 100 char

= 1.0.4 - 18/12/2014 =

* [Add] - helper function to not copy meta data for subscription renewals
* [Add] - Send random products as related when none are found by core function
* [Improvement] - Sending discounts as negative number to API
* [Improvement] - Refactored helper functions
* [Fix] - Error when updating WooCommerce version while Receiptful is active
* [Fix] - for WC Subscriptions emails

= 1.0.3 - 12/12/2014 =

* [Add] - Support for product meta
* [Add] - Support for downloadable products (download links in Receipt)
* [Improvement] - Change the coupon tracking to JS at checkout
* [Fix] - Bug that caused coupon product restrictions
* [Fix] - Javascript error on thank you page

= 1.0.2 - 03/12/2014 =

* [Add] - Receiptful is now FREE
* [Add] - Added reporting for email conversions
* [Improvement] - Refactor the API class
* [Improvement] - Refactor related products code
* [Improvement] - Add more code commenting
* [Improvement] - Remove custom API endpoint for coupons
* [Fix] - WC Pending email sending
* [Fix] - Email being sent for digital downloads

= 1.0.1 - 19/11/2014 =

* [Add] - Plugin screen shots + banner + icon
* [Add] - Coupon usage tracking
* [Add] - Option to restrict coupon usage to customer email
* [Add] - WooCommerce 2.1.X support
* [Improvement] - Change CRON from 60 to 15 minutes
* [Improvement] - WooCommerce activated check for both network activated and single site
* [Fix] - Notice when using Free shipping upsell
* [Fix] - Incorrect coupon expiry date

= 1.0.0 - 22/10/2014 =

* Initial Release


== Upgrade Notice ==


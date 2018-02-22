=== WooCommerce - Store Exporter Deluxe ===

Contributors: visser
Tags: e-commerce, woocommerce, shop, cart, ecommerce, export, csv, xml, xls, xlsx, excel, customers, products, sales, orders, coupons, users, attributes, subscriptions
Requires at least: 2.9.2
Tested up to: 4.9.2
Stable tag: 2.5.4

== Description ==

Export store details out of WooCommerce into simple formatted files (e.g. CSV, XML, Excel 2007 XLS, etc.).

For more information visit: http://www.visser.com.au/woocommerce/

== Installation ==

1. Upload the folder 'woocommerce-exporter-deluxe' to the '/wp-content/plugins/' directory
2. Activate 'WooCommerce - Store Exporter Deluxe' through the 'Plugins' menu in WordPress

If you currently have our basic Store Exporter Plugin activated within your WordPress site we will do our best to automatically de-activate it to avoid conflicts with Store Exporter Deluxe.

See Usage section before for instructions on how to generate export files.

== Usage ==

1. Open WooCommerce > Store Export from the WordPress Administration
2. Select the Quick Export tab on the Store Exporter screen
3. Select which export type and WooCommerce details you would like to export
4. Click Export
5. Download archived copies of previous exports from the Archives tab

Done!

== FOSS Disclaimer ==

One open source library is included with this Plugin (without changes)
> PHPExcel v1.8.0 (2014-03-02) - http://phpexcel.codeplex.com

== Support ==

If you have any problems, questions or suggestions please join the members discussion on our WooCommerce dedicated forum.

http://www.visser.com.au/woocommerce/forums/

== Changelog ==

= 2.5.4 =
* Added: Export support for WC Fields Factory (thanks Tim)
* Added: WP-CLI support for running single Scheduled Export events (thanks Alan)
* Added: Turn off Debugging and Logging mode from WooCommerce > System Status screen
* Fixed: Detection of WooCommerce Multilingual
* Added: WP-CLI filtering support for listing Scheduled Exports
* Added: WP-CLI support for running Quick Exports
* Added: Support for execute now on WordPress sites with DISABLE_WP_CRON (thanks Nathan)
* Changed: Moved Dashboard widgets to /includes/admin/widgets.php
* Added: Export support for Attibute Terms

= 2.5.3 =
* Added: WP-CLI support for listing Scheduled Exports (thanks Alan)
* Fixed: Order date filtering failing validation (thanks Kendall)

= 2.5.2 =
* Added: Filter Products by Product Gallery
* Added: Product export support for custom Term Taxonomies
* Fixed: Order export compatibility with Sequential Order Numbers Pro (thanks Matt)
* Changed: Exclude Draft Products from Product filter dropdowns (thanks Elizabeth)
* Fixed: Scheduled Subscription exports not filtering by Products (thanks Beni)
* Fixed: Total Quantity Order field not counting correctly with individual Order Items Formatting (thanks Marvin)
* Added: Export support for WooCommerce Gift Certificates Pro (thanks Jan)
* Added: Filter to keep order_items Object when exporting Orders (thanks Orjan)
* Added: Filter to override Transient timeout for TM EPO field scanning (thanks Colm)
* Added: Advanced Setting option to manually refresh TM EPO fields
* Fixed: Order date filtering where the date does not match the date format (thanks Colm)

= 2.5.1 =
* Fixed: PHP 7.1 compatibility issue with TM EPO exporting (thanks Geoff)
* Added: Export suppor for Checkout Field Editor Pro (thanks Davide)
* Fixed: Total Quantity field showing 0 value (thanks Ricardo)
* Added: Order export support for WooCommerce Wholesale Pricing (thanks William)
* Added: Featured Image formatting support to Scheduled Export (thanks Daniel)
* Added: Product Gallery formatting support to Scheduled Export
* Added: Order export support for YITH WooCommerce PDF Invoice and Shipping List (thanks Daniel)
* Fixed: Product Featured status (thanks Daniel)
* Fixed: Filtering Products by Featured status
* Changed: Use of "Un-featured" to "Not featured"
* Fixed: CRON exports failing due to init Action delay (thanks Richard)
* Added: Filter Products by User Role (thanks Tanguy)
* Added: Author ID to Product export type
* Added: Order export support for Custom Order Numbers for WooCommerce (thanks Rushad)
* Added: User Role to Products export (thanks Tanguy)

= 2.5 =
* Added: Filter to individual Order Items Formatting to support blank rows between Orders (thanks Adad)
* Added: Export support for Tickera (thanks Fabian)
* Added: Sorting to Customers export type (thanks Fernando)
* Added: Additional debug logging to CRON export engine (thanks Pablo)
* Added: Filter Subscriptions by Product to Scheduled Exports (thanks Beni)
* Changed: Moved Scheduled Export filters logic to separate export type files
* Changed: WOO_CD_DEBUG and WOO_CD_LOGGING Constants can be triggered via WooCommerce > System Status
* Added: Filter Orders by Product Tag support to Scheduled Exports
* Added: Post Parent ID support to Scheduled Exports using Archive to WordPress Media
* Added: Export support for WooCommerce Measurement Price Calculator (thanks David)
* Added: Transaction ID to Orders export (thanks William)
* Added: Created Via to Orders export
* Added: Cart Hash to Orders export
* Added: Export support for PayPal details in Order exports
* Added: Export support for WooCommerce Stripe Payment Gateway
* Added: Filter Reviews export since last export (thanks Pablo)
* Fixed: PHP 7.1 compatibility issue when totalling quantity (thanks Jeroen)
* Added: Export Template field selection to the Quick Export screen
* Added: Manual scheduling option for Scheduled Exports
* Fixed: Order date to filtering missing 36 minutes up to midnight (thanks Ørjan)
* Fixed: Subscription export missing Order ID (thanks Geoff)

= 2.4.3 =
* Added: Export support for WooCommerce Appointments (thanks Raymond)
* Added: Export support for SEO Squirrly (thanks Joe)
* Added: Group Name support to N-Media WooCommerce Personalized Product Meta Manager

= 2.4.2 =
* Added: Fix for Last Month date filter (thanks Lawrence)
* Fixed: Exporting arrays from Custom Order Item meta (thanks Philip)
* Added: Export support for WooCommerce PDF Invoices & Packing Slips Professional (thanks Laura)
* Added: Export support for AG WooCommerce Barcode / ISBN & Amazon ASIN - PRO (thanks Juliet)
* Added: Order Items: Shipping Class to Orders export
* Added: Delimiter field to Edit Scheduled Export screen (thanks Natalia)
* Added: Filter to turn on/off Attribute id in XML exports (thanks Vincent)
* Added: Transaction ID to Subscriptions export (thanks Geoff)
* Added: Export support for N-Media WooCommerce Personalized Product Meta Manager (thanks Chris)

= 2.4.1 =
* Added: Add custom User meta to exports from the Profile/Edit User screen
* Added: Export support for WooCommerce Chained Products (thanks Simon)
* Added: Export support for WooCommerce Sample (thanks David)
* Added: Integration with Product Importer Deluxe for importing linked Product Attributes
* Added: Filter to override node labels in XML exports (thanks Vincent)
* Fixed: Chained Product IDs returning Array (thanks Simon)
* Fixed: Empty Multisite Product exports (thanks Elias)
* Added: FTP/SFTP support to Order export triggers (thanks Hendrik)
* Added: Debug logging support for bulk actions
* Fixed: Booking start and end time for Bookings export type

= 2.4 =
* Fixed: Empty WooCommerce User fields response breaking User exports (thanks Elias)
* Fixed: Export support for WooCommerce PDF Invoices and Packing Slips (thanks Oliver)
* Added: Export support for WooCommerce Advanced Product Quantities (thanks Elias)
* Changed: Product Visibility field matches WooCommerce 3.1 labels (thanks spbuckle)
* Added: Filter for legacy Product Visibility labels
* Added: SSL Filter override for Post to URL export method
* Fixed: Conditional support for detecting y/n values in cell values

= 2.3.8 =
* Fixed: Large number of Coupons crashing the Edit Scheduled Export screen (thanks Profound)
* Added: Coupon Published and Coupon Modified columns to Coupon export (thanks Matt)
* Added: Used In column to Coupon export
* Fixed: Remember Filter Order by Product Type filter on Quick Export screen
* Changed: Function name woo_ce_scheduled_export_orders to singular
* Added: Coupon Type filter to Coupons export within Scheduled Exports
* Fixed: Remember Filter Coupons by Discount Type on Quick Export screen
* Fixed: Remember Filter Order by Product Tag on Quick Export screen
* Fixed: Bulk exporting Products from the Trash (thanks Gio)
* Added: Export method labels to Scheduled Export screen
* Changed: Renamed FTP Transfer mode to Connection mode on Edit Scheduled Export screen
* Added: FTP Transfer mode to Edit Scheduled Export screen
* Fixed: Hide one time Scheduled Exports from Scheduled Export Dashboard widget
* Added: Sequential Order ID support to Used In Coupon export column (thanks Matt)
* Added: Export support for WooCommerce Easy Booking (thanks Raymond)
* Added: Filter Products by Featured Image
* Added: Attributes export type

= 2.3.7 =
* Fixed: Exporting Order Shipping Weight in WooCommerce 3.1+ (thanks Sarun)
* Added: Additional logging Filters
* Fixed: Override default Product Types when bulk exporting from Products screen
* Added: Filter woo_ce_enable_advanced_product_attributes to hide advanced Product Attribute fields (thanks Corin)
* Fixed: Logging to woo_ce... files in WooCommerce 3.1+
* Fixed: Filtering Orders by Coupon case-sensitivity mis-match (thanks Scott)
* Added: Export support for WooCommerce Unit of Measure (thanks David)
* Fixed: Custom Product Attributes not showing as export columns without a single Global Attribute (thanks David)

= 2.3.6 =
* Added: Pagination of Recent Scheduled Exports on the Scheduled Export screen (thanks Joe)
* Fixed: Check for WooCommerce pre-2.1 when exporting Products (thanks Yogesh)
* Fixed: Check for corrupt Products export query (thanks Alex)
* Added: Hide any error notices when running a Quick Export after a fatal PHP error (thanks Yogesh)
* Fixed: Defaulting empty e-mail recipient for Scheduled Exports to WordPress Administration e-mail (thanks Bruno)
* Added: Notice for Extra Product Options users of memory warning with 1000+ Orders (thanks Colm)
* Added: Export support for WooCommerce Deposits (thanks Niels)
* Fixed: Purchase Date offset applying to some GMT dates (thanks Joe)
* Fixed: Slashes appearing in custom labels (thanks Craig)
* Fixed: CSS styling on tabs in Edit Export Template screen (thanks Phil)
* Fixed: CSS styling on tabs in Edit Scheduled Export screen
* Added: Add custom Order meta to exports from Edit Order screen
* Added: Add custom Coupon meta to exports from Edit Coupon screen
* Fixed: Quick Export slowdown for stores with thousands of Coupons (thanks Simon and Rees)
* Changed: WordPress Option woo_ce_order_coupons to woo_ce_order_coupon
* Added: WOO_CD_DEBUG to WooCommerce > System Status screen
* Added: WOO_CD_LOGGING to WooCommerce > System Status screen
* Added: Export to FTP notice shows missing PHP functions on WooCommerce > System Status screen
* Added: Export to SFTP notice shows missing PHP functions on WooCommerce > System Status screen
* Added: Count column to Categories export type
* Added: Count column to Tags export type
* Fixed: Exporting multiple values (separate fee) for Extra Product Options Order Items (thanks Dave)
* Added: Scheduled Export History meta box to Edit Scheduled Export screen (thanks Bill)
* Changed: Export Details meta box title to Scheduled Export Details
* Fixed: Filtering Orders to the same date returned Orders from the next day (thanks Taavi)
* Changed: Renamed order-combined.php to order-combined-extend.php
* Added: Order attribute logging to woo_ce_debug_product_attributes
* Added: Order speed optimisations by loading filter resources once (thanks Sarun)
* Fixed: Lowest Product Variation Price snippet causing fatal PHP error (thanks Ingmar)

= 2.3.5.1 =
* Fixed: Default logging level set incorrectly

= 2.3.5 =
* Fixed: Variations being trimmed from Product export when filtering by Type (thanks Roy)
* Changed: Moved alot of debugging notices to be WOO_CD_LOGGING dependent
* Added: WordPress Filters to restrict categories of debugging notices
* Added: woo_ce_debug_subscriptions Filter for debugging Subscriptions
* Added: woo_ce_debug_product_attributes Filter for debugging Product Attributes
* Fixed: Order Items: Product Variations missing custom per-Product Attributes
* Added: String cell detection and type support for PHPExcel cells (thanks Niels)
* Added: Force text wrapping for Excel format cells that contains strings
* Changed: PHP function rename woo_ce_sanitize_key to woo_ce_filter_sanitize_key
* Added: woo_ce_debug_cron_export_email Filter for debugging Scheduled Export e-mails
* Added: woo_ce_debug_cron_export_ftp Filter for debugging Scheduled Export FTP/SFTP/FTPS transfers
* Fixed: Removed setting of $order->id related to forced WooCommerce 3.0 deprecation
* Fixed: Order Time not reflecting local timezone (thanks Joe)
* Fixed: wc_format_datetime not available in WC 2.7 (thanks Guillaume)
* Added: Custom label support for Export templates
* Added: Hide excess Export Types from the Quick Export screen
* Fixed: Empty Shipping Method ID in Orders (thanks Jeremy and Rod)

= 2.3.4.1 =
* Fixed: PHP error for legacy PHP users (thanks Chris)
* Added: Hover field labels to Quick Export screen (thanks Niels)
* Fixed: Missing ID for labels from Field Editor

= 2.3.4 =
* Added: Stopwatch to footer of Quick Export screen
* Added: Filter to turn off Filtering Orders by Coupon (thanks Chris)
* Added: Extend Variation Formatting to support Custom Product meta (thanks Stephen)
* Added: One more check for PHPExcel Class before failing export (thanks Stephen)
* Fixed: Compatibility with WooCommerce Subscriptions (thanks Anshul)
* Added: Hover label to Archives screen
* Added: Idle memory usage column to Archives screen
* Fixed: Custom Order Items not appearing in exports (thanks Thomas)
* Added: sanitize_key() to Product Add-ons export support (thanks Burt)
* Added: Order Items: Product Add-ons to Orders export type (thanks Matt)
* Added: Filter to override Export Filters (thanks Matt)
* Fixed: Product Attributes missing for Variations (thanks Stephen)
* Added: Export support for WooCommerce Show Single Variations (thanks Roy)
* Added: Attribute - Position to Product export type
* Added: Attribute - Visible on the product page to Product export type
* Added: Attribute - Used for variations to Product export type
* Added: Attribute - Is Taxonomy to Product export type
* Added: Filters to override Product Type counts (thanks William)
* Added: Export support for Booster for WooCommerce: Product Cost Price (thanks Colin)
* Added: Variation Formatting support for Category and Tag column (thanks Denise)
* Added: Filter Product Variations by Category
* Added: Remember Filter Products by Category
* Added: Filter Product Variations by Product Tag
* Added: Remember Filter Products by Product Tag

= 2.3.3 =
* Fixed: User Membership column for User export (thanks Thiago)
* Added: User Membership Status column for User export (thanks Thiago)
* Added: Export support for Booster for WooCommerce: EU VAT Number (thanks Jochen)
* Added: Export support for Booster for WooCommerce: Order Numbers
* Added: Export support for WooCommerce Bookings (thanks Guillaume)
* Changed: WordPress Filter from woo_ce_ticket_term_taxonomy to woo_ce_ticket_post_type
* Fixed: One time Frequency state not being remembered on refresh (thanks Dima)
* Fixed: PHP notice from Custom Order Item Product fields (thanks Hugo)
* Fixed: Custom Attribute exports for Simple and Variable Product Types (thanks David and Lee)
* Fixed: Filter Order by Coupons only showing 10 results (thanks Giovanni)

= 2.3.2 =
* Fixed: Filter Orders by Shipping Method (thanks Naomi)
* Fixed: Shipping Methods with no Title
* Added: Export support for WooCommerce Wholesale Prices (thanks Eric)
* Added: Product export support for Aelia Currency Switcher (thanks Stuart)
* Fixed: FTP upload not working with default timeout set (thanks Patrick)
* Added: Memory sync notification where PHP vs WordPress are different (thanks Paul)
* Added: Notification of export in progress on Quick Export screen
* Fixed: Fallback for empty Export Template excerpt (thanks Dima)
* Added: Filter Products by custom Product meta (thanks Matthieu)

= 2.3.1 =
* Added: FTPS support for Scheduled Exports (thanks Patrick)
* Fixed: Sections on Quick Export screen stretch on large-res screens
* Fixed: WooCommerce 3.0 compatibility for Order exports
* Fixed: PHP notice on Order Items export with custom Product meta
* Changed: Multiselect filters use Select2 Version 4
* Added: Backwards compatibility with pre-WooCommerce 3.0

= 2.3 =
* Added: XML order_items node for unique Order Items Formatting rule
* Added: XML order_item node for unique Order Items Formatting rule
* Added: Export support for WooCommerce Ultimate Multi Currency Suite (thanks Manoj)
* Added: Filter support for Order Delivery Date for WooCommerce (thanks Jeffrey)
* Added: Dismiss option on export currently running notice (thanks Linda)
* Added: Filter to override case formatting (thanks Chrystel)
* Fixed: Total Quantity field not populating in XML export (thanks Ludvig)
* Added: Export support for WooCommerce Delivery Slots (thanks Florian)
* Fixed: Filter Customers by Status value using name instead of slug (thanks Philipp)
* Added: Export support for Products Purchase Price for WooCommerce (thanks Daniela)
* Added: Export support for WooCommerce Product Custom Options Lite (thanks Paul)
* Fixed: Remember Filter Orders by Product Category (thanks Raffaele)
* Added: Product Category filter to the Orders export type within Scheduled Exports
* Added: Draft/Publish actions to the Scheduled Export screen
* Changed: Moved Admin UI export filters to includes/admin/...
* Fixed: Not checking state of BOM option (thanks Dima)
* Fixed: User Membership filter returning 5 results (thanks Audrey)
* Added: Add custom Product meta directly from the Edit Product screen when Store Toolkit is activated
* Fixed: Order sorting field not saving on Edit Scheduled Export screen
* Added: Product Sorting field to Filters on Edit Scheduled Export screen (thanks Dima)
* Added: WordPress Filter to increment row number during exports (thanks Daniel)
* Added: Category Sorting field to Filters on Edit Scheduled Export screen
* Added: Tag Sorting field to Filters on Edit Scheduled Export screen
* Added: Brand Sorting field to Filters on Edit Scheduled Export screen
* Added: Order Sorting field to Filters on Edit Scheduled Export screen
* Added: User Sorting field to Filters on Edit Scheduled Export screen
* Added: Order Item Type filtering on Edit Scheduled Export screen
* Fixed: PHP notice when Filtering Products by Product Type with keyed array (thanks Dima)

= 2.2.2 =
* Fixed: Image embed not working for Products export (thanks Danny)
* Fixed: Detection of broken image embeds and fallback
* Added: WordPress Filter to toggle relative filepath for image embed
* Fixed: Date formatting error on Filter Orders by Date (thanks Stephen)
* Added: Export support for WooCommerce Germanized
* Added: Export support for WooCommerce Germanized Pro (thanks Behrang)
* Added: Invoice Status field to Orders export type
* Fixed: Detection of discarded invoices in WooCommerce Germanized

= 2.2.1 =
* Added: Store Exports diagnostics to WooCommerce > System Status screen
* Added: Notice on Quick Export screen indicating background exports
* Added: Indicator on Scheduled Export screen for background exports
* Added: Language export column to Product export type
* Added: Language export column to Category export type
* Added: Language export column to Tag export type
* Added: WPML support for exporting Orders from all languages when filtering by Product
* Added: Filter Orders by Order meta to Quick Export screen
* Added: Support for Yoast SEO: WooCommerce
* Added: Featured Image Title field to Products export type
* Added: Featured Image Caption field to Products export type
* Added: Featured Image Alternative Text field to Products export type
* Added: Featured Image Description field to Products export type
* Added: Language filter to Products export type on Edit Scheduled Exports (thanks Andrejs)
* Added: Abort button to cancel current Scheduled Export
* Fixed: PHP warning on Orders export (thanks Lydie)
* Fixed: Force Passive FTP transfer mode when selected
* Changed: Created order-individual-extend.php
* Added: Support for WooCommerce EAN Payment Gateway (thanks Yan)

= 2.2 =
* Fixed: Performance issue affecting EPO support (thanks Steve)
* Added: Support for Builder fields within WooCommerce Extra Product Options (thanks Steve)
* Added: Support for WooCommerce Tiered Pricing (thanks Rob)
* Changed: Product URL column to Product URI
* Added: Support for filtering Orders by Booking Start Date
* Added: Support for WooCommerce Bookstore (thanks Linda)
* Added: Support for WooCommerce Point of Sale (thanks John)
* Added: Shipping Incl. Tax Order export field (thanks Charlotte)
* Added: Order Items Formatting option to Orders Screen
* Added: Order Items Formatting option to Export Triggers
* Added: WordPress Filter to disable wp_mail() failure notice (thanks David)
* Added: Notice to dismiss and override wp_mail() failure notice
* Added: All dates option to Filter Products by Date Modified (thanks Linda)
* Added: Date modified to Edit Scheduled Export screen
* Added: Support for negative number values (thanks Philipp)
* Fixed: Fatal error on CSV export without mb_convert_encoding() (thanks Milan)
* Changed: Moved Custom Product Add-ons to Orders export type
* Added: Support for WooCommerce PDF Product Vouchers (thanks Jeremy)
* Fixed: Compatibility with WooCommerce Custom Fields (thanks Catherine)
* Added: User Field support for WooCommerce Custom Fields
* Added: Order Field support for WooCommerce Custom Fields
* Added: Checkout Field support for WooCommerce Custom Fields
* Fixed: PHP warning on saving Scheduled Export with 100+ Customers (thanks Daniel)
* Added: Remember Filter Orders by Order ID
* Added: Support for Order ID ranges in Filter Orders by Order ID
* Added: Other to list of payment gateways (thanks Vu)
* Added: Subscription Renewal export field to Orders export type (thanks Casey)
* Added: Custom Subscription meta to Subscriptions export type
* Added: Subscription Resubscribe export field to Orders export type
* Added: Subscription Switch export field to Orders export type
* Added: Subscription Relationship to Orders export type
* Added: Filter Orders by Order Type to Orders export type
* Added: Clone Scheduled Export action to Scheduled Exports tab
* Fixed: Filter Orders by Date > Last month ignoring new year difference
* Fixed: Empty Max unique Order Items on Order screen (thanks Axel)
* Added: WordPress Filter to toggle Product ID attribute in XML exports (thanks Andrejs)
* Fixed: SFTP uploads not working (thanks Valentin & Sebastian)
* Added: Export support for WooTabs (thanks Rahul)

= 2.1.10 =
* Fixed: Order exports not working
* Fixed: Product Variations not being included in exports
* Fixed: Featured Image and Product Gallery missing
* Changed: Clearer wording of max_execution_time notice
* Changed: Display open_basedir with notice styling

= 2.1.9 =
* Changed: Conditionally load Filters and Actions on export running (thanks Tim)
* Fixed: Re-introduced Subscriptions Filter (thanks Matt)
* Added: PHPExcel temp directory override Filter (thanks Dmitry)
* Fixed: Activation issue when refreshing Transients (thanks Yoshi)
* Fixed: Default Scheduled Export schedule to daily

= 2.1.8 =
* Fixed: PHP warning in WooCommerce Bookings integration (thanks George)
* Changed: Removed experimental Google Sheets support
* Fixed: Scheduling tab on Edit Scheduled Export screen disappearing
* Fixed: Fatal error increasing memory limit for some users
* Added: Exported Order notes option to Edit Scheduled Export screen
* Added: Export support for AweBooking (thanks Juan)
* Added: Arrival Date to Orders export type
* Added: Departure Date to Orders export type
* Added: Adults to Orders export type
* Added: Children to Orders export type
* Added: Room Type ID to Orders export type
* Added: Room Type Name to Orders export type
* Added: Check for DISABLE_WP_CRON Constant on Scheduled Export screen (thanks Matt)
* Changed: Moved WordPress notice dismissable to functions.php
* Changed: Moved export data preparation to individual export type files
* Added: WordPress Filter to override Product Attribute formatting in Product Name column (thanks Peter)
* Fixed: Filter Orders by Date > Last month not including last day (thanks Lawrence)
* Fixed: Duplicate tax rates appearing in Order Items (thanks Faha)
* Fixed: Duplicate Variation details appearing in Order Items (thanks Thomas)
* Fixed: PHP warning in Gravity Forms integration for a Order Items Formatting rule (thanks Rhys)
* Added: Attribute Quantity field to Products export (thanks Anestis)
* Fixed: Vendor Commission empty on Products export (thanks Aaron)
* Fixed: Product Vendors type with new Product Vendors
* Fixed: Scheduled Exports Days frequency using GMT time (thanks Dipesh)
* Added: Export support for YITH Delivery Date Premium (thanks Ruben)
* Fixed: Mobile support for Quick Export screen
* Fixed: Default export timeout of 600 seconds (thanks Graham)
* Fixed: Allow Backorders not exporting "Allow, but notify" option
* Fixed: Export filename date not using WordPress current_time() (thanks Pam)
* Added: Featured Image Formatting options to Quick Export screen
* Fixed: Scheduled Export frequency Once not working
* Added: Override for exporting Commissions from custom tables (thanks Nancy)
* Added: Export support for YITH WooCommerce Brands Add-On (thanks Corin)
* Added: Disable XLSX export and display notice where ZipArchive Class is missing (thanks Pam)
* Fixed: Export slowdown when no active modules are detected (thanks Oriano)

= 2.1.7 =
* Changed: Orders Screen > Actions display affects the bulk actions dropdown on the Orders screen (thanks Olof)
* Fixed: Bulk actions exports not respecting Orders Screen > Export fields selection
* Added: Default to select all export fields on Quick Export screen when no fields are selected (thanks Patricia)
* Added: Process time column to Archives screen
* Added: Columns column to Archives screen
* Changed: Removed loading of unnecessary Actions and Filters at export time

= 2.1.6 =
* Fixed: Filter Orders by Digital Products not working in Scheduled Exports
* Fixed: PHP warning on Filter Orders by Customer for large WooCommerce stores (thanks Thomas)
* Added: WordPress Filter on failed Scheduled Exports (thanks Marcus)
* Changed: Default Attributes now exports Attribute Name instead of Slug (thanks Carl)
* Fixed: Next Payment and Last Payment showing as the same in Subscriptions (thanks Lyndel)
* Added: Product Addons support to Products export type (thanks Philip)
* Fixed: Compatibility with Gravity Forms Product Add-Ons (thanks David)
* Fixed: Active modules count for modules with multiple Class detection
* Added: WordPress Filters to control Order Items sorting (thanks Andrew)
* Added: Bulk export actions to Products screen
* Fixed: Product count being overridden by Reviews count (thanks Kenneth)

= 2.1.5 =
* Added: Export support for WooCommerce Product Bundles
* Added: Remember Filter Products by Brand
* Added: Export support for WooCommerce Min/Max Quantities
* Fixed: Filter Products by Brand in Scheduled Exports
* Added: Override for excluding child Order Items from Product Bundles in the Order exports (thanks Michael)
* Added: Re-order export fields from the Edit Export Template screen (thanks Thomas)
* Added: Override for Refund Date excluding Fully Refunded Order
* Fixed: Duplicate Attributes appearing in Default Attributes
* Added: Template Tag woo_ce_is_scheduled_export (thanks Valentin)
* Added: Template Tag woo_ce_is_export_template
* Changed: Delete Scheduled Export is now Permanent Delete (thanks Julia)
* Added: Order Items: Publish Date to Orders export (thanks Nitai)
* Added: Order Items: Modified Date to Orders export
* Added: Filter Orders by Digital Products (thanks Valentin and Michael)
* Added: Support for no cell escaping in CSV/TSV exports (thanks Juan)
* Added: Clear Recent Scheduled Exports associated with deleted Scheduled Exports
* Added: Days filter to Scheduled Exports Frequency tab (thanks Henrik)

= 2.1.4 =
* Added: WordPress Filter to disable Product Attributes support
* Added: Default notice when Fields list on Edit Export Template screen is empty
* Added: Export Template support to Order screen Actions
* Added: Export Template support to CRON export engine
* Changed: Order Items: %Variation% label to Order Items: %Variation% Variation
* Changed: Subscription Items: %Variation% to Subscription Items: %Variation% Variation
* Added: Order Items: %Variation% Attribute to Orders export
* Added: Filter Orders by User Role to Scheduled Exports (thanks Mark)
* Added: Filter Orders by Coupon Code to Scheduled Exports
* Added: Remember Filter Orders by Coupon Code
* Added: Remember Filter Orders by Payment Method
* Added: Remember Filter Orders by Shipping Method
* Fixed: Order screen Actions not remembering field preference (thanks Jinesh)
* Added: Guest option to Filter Orders by User Role (thanks Mark)
* Added: Deutsch (German) translation (thanks Thomas)
* Added: Filter Products by Product Vendor (thanks Mark)
* Added: Filter Orders by Product Vendor
* Added: Remember Filter Product by Brand
* Fixed: CRON export where order_date_from and order_date_to are the same (thanks Giorgio)
* Added: Export support for Order Delivery Date for WooCommerce (thanks Robin)
* Added: Heading Formatting option to Scheduled Exports (thanks Lucas)

= 2.1.3 =
* Added: WordPress Filter for controlling thumbnail size on Featured Image (Embed)
* Added: Check for PHPExcel_Worksheet_Drawing for Image embed in XLSX file type exports
* Added: Product Gallery (Embed) to Products export
* Added: Image (Embed) to Categories export type
* Added: Image (Embed) to Brands export type
* Added: Order Items: Featured Image (Embed) to Orders export type
* Added: Category: Level 1 to Products export type
* Added: Category: Level 2 to Products export type
* Added: Category: Level 3 to Products export type
* Added: Gravity Forms field support to Subscriptions export type (thanks Morten)
* Fixed: Compatibility with WooCommerce Checkout Manager (thanks Grégoire)
* Changed: Moved export type extensions to separate files
* Fixed: PHP notice in Orders export (thanks Andreas)
* Added: WordPress MultiSite support for Categories
* Added: WordPress MultiSite support for Tags
* Added: WordPress MultiSite support for Brands
* Added: WordPress MultiSite support for Orders
* Added: WordPress MultiSite support for Users
* Added: WordPress MultiSite support for Reviews
* Added: WordPress MultiSite support for Coupons
* Added: WordPress MultiSite support for Shipping Classes
* Added: WordPress MultiSite support for Subscriptions
* Added: WordPress MultiSite support for Product Vendors
* Added: WordPress MultiSite support for Tickets
* Added: WordPress MultiSite support for Customer
* Added: Quick Export button below Export Types on Quick Export screen
* Added: Export support for WooCommerce EU VAT Compliance free and Premium (thanks Andy)
* Added: VAT ID to Orders export
* Added: Valid VAT ID to Orders export
* Added: VAT ID Validated to Orders export
* Added: VAT Country ID to Orders export
* Added: VAT Country Source to Orders export
* Added: VAT B2B Transaction to Orders export
* Changed: Show Scheduled exports are disabled in Recent Scheduled Exports Dashboard widget
* Changed: Order Tax Percentage defaults to 0 (thanks Andy)
* Fixed: Order Items: RRP ignoring Variation data (thanks Kevin)
* Added: Export support for YITH WooCommerce Checkout Manager
* Added: YITH WooCommerce Checkout Manager Billing fields support to Orders export
* Added: YITH WooCommerce Checkout Manager Shipping fields support to Orders export
* Added: YITH WooCommerce Checkout Manager Additional fields support to Orders export
* Fixed: Order Tax Percentage fetches the Tax Rate from WooCommerce (thanks Andy)
* Added: Filter Orders by Date: Tomorrow to Orders export (thanks Ruben)
* Added: DONOTCACHEPAGE Constant to export process
* Added: Export support for Discontinued Product for WooCommerce
* Added: Export support for YITH WooCommerce Multi Vendor Premium
* Fixed: Product Vendor compatibility with YITH WooCommerce Multi Vendor Premium
* Added: Product compatibility with YITH WooCommerce Multi Vendor Premium
* Added: Export support for WooCommerce Memberships
* Added: Active Memberships to Orders export
* Added: User Membership to Users export
* Added: Filter Users by User Membership to Users export
* Added: Export Templates tab to Store Export screen
* Added: Export Template meta box to Edit Export Template screen
* Added: WordPress Filter for overriding %date% and %time% filename Tags
* Added: Export Template option within Edit Scheduled Export screen

= 2.1.2 =
* Added: Coupon Expiry Date to Orders export (thanks Mihai)
* Added: Remember Filter Order by Dates filter on Quick Export screen
* Fixed: Filter Order by Dates: Last week not working
* Changed: Order Date filter query (thanks Joe)
* Changed: Export Modules includes links to individual Plugin pages
* Fixed: Export Modules list empty when filtering by an empty filter
* Changed: Hover text on disabled Execute button on Scheduled Exports screen
* Added: Export support for WooCommerce Profit of Sales Report (thanks Matias)
* Added: Cost of Good field to Products export
* Added: Order Items: Cost of Good field to Orders export
* Added: Limit Volume to Scheduled Export screen
* Added: Volume Offset to Scheduled Export screen
* Added: All Day Booking to Orders export (thanks Johnny)
* Added: Booking Resource ID to Orders export
* Added: Booking Resource Name to Orders export
* Added: Booking # of Persons to Orders export
* Added: Recent Scheduled Exports to Scheduled Export screen
* Added: Delete All button to Recent Scheduled Exports list
* Changed: Remove table footer section from Scheduled Exports
* Added: Styling to Scheduled Exports with Draft Status
* Added: Fatal error detection during Scheduled Exports
* Added: Automatic 30 second screen refresh after pressing Execute on Scheduled Exports screen
* Added: Fatal error details to Recent Scheduled Exports (thanks William)
* Added: Legacy support for PCLZip where ZipArchive is unavailable (thanks William)
* Added: Order Items Formatting to Edit Scheduled Export screen (thanks Emily)
* Changed: Detection PHP function for Per Product Shipping (thanks Max)
* Added: Per Product Shipping - Country to Products export
* Added: Per Product Shipping - State to Products export
* Added: Per Product Shipping - Postcode to Products export
* Added: Per Product Shipping - Cost to Products export
* Added: Per Product Shipping - Item Cost to Products export
* Added: Per Product Shipping - Order to Products export
* Fixed: Limit on number of Variations or WPML Products (thanks Warren)
* Fixed: Variation only Products exports not working (thanks Ilan)
* Fixed: Export of WooCommerce Product Vendors (thanks Joe)
* Added: User Capability checks on Dashboard widgets (thanks Valentin)
* Added: WordPress MultiSite support for Products (thanks Rob)
* Added: Notice when logged in as Network Admin on WordPress MultiSite
* Fixed: Automatic Plugin updater conflict
* Changed: Deactivate Visser Labs Updater on upgrade
* Fixed: Filter Orders by Shipping Country not working (thanks Julien)
* Added: Export support for Order Delivery Date Pro for WooCommerce (thanks Robin)
* Fixed: Export custom Product meta in Orders export (thanks Patrick)

= 2.1.1 =
* Added: Export support for WooCommerce Uploads
* Fixed: Non-breaking space skipping UTF-8 check in XML and RSS exports
* Added: Additional WordPress SEO fields for Category exports
* Added: Additional WordPress SEO fields for Tag exports
* Added: Additional WordPress SEO fields for Product exports
* Fixed: PHP compatibility issue on Archives screen (thanks Andrey)
* Fixed: Conflict with WordPress Plugin updater in WordPress 4.5

= 2.1 =
* Fixed: Total rows count for CSV, TSV, XLS and XLSX
* Changed: Export tab label to Quick Export
* Added: Scheduled Exports tab
* Changed: Moved Scheduled Exports table to Scheduled Exports tab
* Changed: Disable Execute button if Scheduled Exports is disabled
* Changed: Renamed Return to Settings to Return to Scheduled Exports
* Added: Notice for open_basedir without correct tmp path
* Fixed: Total Weight includes Variation weights
* Fixed: Variation Description being overriden for default Variation Formatting in Product exports (thanks Flurin)
* Added: Export support for WooCommerce EU VAT Assistant (thanks Bjorn)
* Changed: Volume offset and Volume limit are on separate rows within Export Options (thanks Mark)
* Changed: Description for Volume offset and Volume limit (thanks Mark)
* Changed: Order field slug order_excl_tax to order_subtotal_excl_tax
* Added: Order Shipping excl. Tax to Orders export (thanks Rikardo)
* Added: Order Items: Tax Rate amount to Orders export (thanks Rikardo)
* Added: Order Items: Height to Orders export (thanks Doug)
* Added: Order Items: Width to Orders export
* Added: Order Items: Length to Orders export
* Added: Order Total Tax: Tax Rates to Orders export
* Added: Product export support for WooCommerce Custom Fields
* Added: Description of Field escape formatting fields (thanks Valentin)
* Fixed: PHP warning on Gravity Forms integration (thanks Caitlin)
* Fixed: Order shipping fields defaulting to billing fields when using WooCommerce Checkout Manager (thanks Fabio)
* Changed: Show Recent Scheduled Exports and Scheduled Exports Dashboard widgets regardless of Enable Scheduled Exports state
* Fixed: Could not Filter Products by Simple and Variations without including Variables (thanks Andrey)
* Added: Default Filter Products by Product Type to include Simple, Variable and Variation Product Types

= 2.0.9 =
* Fixed: PHP warning exporting from WooCommerce Checkout Manager
* Added: Tickets export type
* Fixed: &nbsp; appearing in some price values
* Added: Subscription Sorting
* Fixed: Empty Cost of Good for Variations in Product export
* Added: Nuke Scheduled Export to Advanced options
* Added: Nuke WP-CRON Option to Advanced options
* Added: New Subscriptions export engine
* Added: Notice regarding increased memory demands with Query Monitor
* Added: Filter Subscriptions by Customer
* Added: Filter Subscriptions by Product
* Added: Filter Subscriptions by Source
* Changed: Removed WooCommerce User fields from Subscriptions export type
* Added: Subscription Billing and Shipping fields
* Added: Active Subscriber field to Users export

= 2.0.8 =
* Fixed: Exporting Custom Attribute with accents in Products export
* Added: Export support for Woocommerce Easy Checkout Fields Editor
* Added: Export support for WooCommerce Product Fees
* Added: Export support for WooCommerce Events
* Added: Export support for WooCommerce Product Tabs
* Added: Modules filter support on Tools screen
* Fixed: Order Subtotal not excluding shipping cost
* Fixed: Cost of Goods support in Products export
* Fixed: Export of custom meta with an apostrophe in the meta name
* Added: Custom Attributes support in Orders export
* Added: Export support for WooCommerce Custom Fields
* Added: Product Reviews export type
* Added: Review count field to Products export type
* Added: Rating count field to Products export type
* Added: Average rating field to Products export type
* Added: Support for IP whitelisting within the CRON export engine
* Added: Support for limiting allowed export types within the CRON export engine
* Added: Support for triggering Scheduled Exports via the CRON export engine
* Change: Product gallery formatting to URL by default
* Added: WordPress Filters during the XML/RSS export process
* Added: %random% Tag to export filename for random number generation
* Fixed: Field type detection giving false positive for integers
* Changed: Button styling of Save Custom Fields
* Fixed: Update all export Attachments to Post Status private
* Added: Notice prompt when non-private export Attachments are detected
* Added: Dismiss option to override detection of non-private export Attachments
* Added: Return to Settings button on Add Scheduled Export screen
* Added: Return to Settings button on Edit Scheduled Export screen
* Changed: E-mail export method uses temporary files instead of WordPress Media
* Changed: Max unique Order Items only shown if related Order Items Formatting rule is selected
* Changed: Max unique Product Gallery images only shown if related Product Gallery formatting rule is selected
* Changed: Reduction in memory requirements for $export Global
* Added: Filter Products by Date Modified
* Added: Quantity populates total stock quantity for Variables
* Added: Min/max Price and Sale Price for Variables (thanks terravity and Lena)
* Fixed: Export of Product Stock Status in Scheduled Exports
* Changed: Translation set to woocommerce-exporter

= 2.0.7 =
* Fixed: Description/Excerpt formatting not saving on refresh
* Fixed: Default timezone for scheduled export where wc_timezone_string() is unavailable
* Added: Local time display to Scheduling tab on Edit Scheduled Export 
* Fixed: Privilege escalation vulnerability (thanks jamesgol)
* Added: Product Description supports Variation Description
* Fixed: Description/Excerpt formatting strips carriage return from XML export type
* Added: Post Title to Products export type
* Changed: Product Name is populated with friendly Variation data
* Added: WooCommerce Gravity Forms Product Add-Ons to Export Modules list
* Fixed: Gravity Forms export support in Orders
* Fixed: Filter Order by ID and Extra Product Options support
* Fixed: Duplicate column data for Extra Product Options
* Added: Export support for WooCommerce Quick Donation

= 2.0.6 =
* Added: Price option for Product Addons
* Added: Option to remove exported flag from Orders
* Added: New export method for Scheduled Exports; Save to this server
* Added: Override scheduled_export.php template via WordPress Theme
* Added: E-mail contents option to Edit Scheduled Export screen
* Fixed: Customer Notes not exporting
* Changed: ftp_fput method uses PHP resource instead of WordPress Media
* Fixed: Disable Execute button for Draft Scheduled Exports
* Changed: Show Every x minutes instead of Custom under Frequency listing
* Added: Remember Order Status Filter on Export screen
* Added: Remember Order Billing Country on Export screen
* Added: Remember Order Shipping Country on Export screen
* Added: Remember Order User Role on Export screen
* Added: Filter Products by SKU
* Added: Export support for WooCommerce Extra Checkout Fields for Brazil
* Added: Reset counts link to Export Types dialog on Export screen
* Added: Loading dialog to Export screen
* Added: Filter Users by Date Registered
* Fixed: Order Total Tax not calculating correctly (thanks Warren Moore)

= 2.0.5 =
* Added: RSS export type to Scheduled Export screen
* Fixed: WordPress Filter affecting other Plugins 

= 2.0.4 =
* Added: type_id column for Orders export
* Added: Store export type counts as hourly WordPress Transients
* Added: Memory usage to Admin footer on Export screen
* Added: Order Items: ID to Orders export to export order_item_id
* Added: Switch between ftp_put and ftp_fput
* Added: Switch for changing the Order Items Formatting option for triggered Order exports

= 2.0.3 =
* Added: Support for WooCommerce Pre-Orders
* Changed: Moved Export Modules to Tools screen
* Added: WordPress Filter to disable Gravity Forms integration
* Added: Display failed scheduled exports in Recent Scheduled Exports Dashboard widget
* Fixed: Orders view conflict with PDF Invoices & Packing Slips
* Fixed: Check that get_total_refunded() is available in WooCommerce 4.4
* Added: Refund Date to Orders export
* Added: Subscription Quantity to Subscriptions export
* Added: Subscription Interval to Subscriptions export
* Added: Maximum Amount to Coupons export
* Added: Aelia Currency Switcher support to Coupons export
* Added: WooCommerce Checkout Add-ons as separate Order columns
* Fixed: Upload to FTP with 0 byte issue
* Added: Export Product Featured Image as filepath
* Added: Export Product Gallery images as filepath
* Fixed: Compatibility with WooCommerce Subscriptions 2.0+
* Added: Support for WC Vendors Plugin
* Added: Vendor to Products export
* Added: Commission (%) to Products export
* Fixed: Line ending formatting is passed onto CSV export
* Added: Shop name to Users export
* Added: Shop slug to Users export
* Added: PayPal e-mail to Users export
* Added: Commission rate (%) to Users export
* Added: Seller info to Users export
* Added: Shop description to Users export
* Added: Sign-up fee to Products export
* Added: Trial length to Products export
* Added: Trial period to Products export
* Fixed: Excel vulnerability reported by Hely H. Shah
* Added: Support for WooCommerce Basic Ordernumbers
* Added: Order ID override for WooCommerce Basic Ordernumbers
* Added: Support for WooCommerce Custom Admin Order Fields
* Added: Support for WooCommerce Table Rate Shipping Plus

= 2.0.2 =
* Fixed: Site hash detection false positives
* Added: Notice on empty exports with volume offset set
* Added: WordPress Filter for Order ID filtering
* Added: Prompt for WooCommerce Checkout Add-ons users
* Added: Default Order Item Type Fee for WooCommerce Checkout Add-ons users
* Fixed: Empty scheduled export titles
* Changed: Uninstall script removes scheduled exports
* Added: Advanced Settings dialog on Settings screen
* Added: Reset dismissed Store Export Deluxe notices to Advanced Settings
* Added: Delete Scheduled Exports to Advanced Settings
* Added: Delete WordPress Options to Advanced Settings

= 2.0.1 =
* Fixed: Line Ending Formatting not saving
* Fixed: New scheduled Product exports not running
* Added: Export support for Smart Coupons
* Changed: Fetch Coupon Types from WooCommerce
* Added: Support for Valid for to Coupons export
* Added: Support for Pick Products Price to Coupons export
* Added: Support for Auto Generate Coupon to Coupons export
* Added: Support for Coupon Title Prefix to Coupons export
* Added: Support for Coupon Title Suffix to Coupons export
* Added: Support for Visible Storewide to Coupons export
* Added: Support for Disable E-mail Restriction to Coupons export
* Added: Send to e-mail for new Order trigger export
* Added: Refund Total to Orders export
* Fixed: Order Total is reduced by Refund Total in Orders export
* Added: Order Items: Refund Subtotal to Orders export
* Added: Order Items: Refund Quantity to Orders export

= 2.0 =
* Added: Support for product_tag filter in Products export for CRON export engine
* Added: Support for product_cat filter in Products export for CRON export engine
* Added: Support for product_brand filter in Products export for CRON export engine
* Added: Support for product_vendor filter in Products export for CRON export engine
* Added: Support for product_type filter in Products export for CRON export engine
* Added: Multiple scheduled export support
* Added: Migrate default scheduled export to scheduled_export CPT
* Added: Filter Products by Featured
* Added: Filter Products by SKU
* Added: Filter Orders by Product Brand
* Added: Filter Users by User Role
* Added: Option to hide Archives tab if Enabled Archives is disabled
* Added: Option to restore Archives tab from Settings tab
* Added: WordPress SEO support for Categories
* Added: ID attribute to export elements in XML/RSS formats
* Added: Fixed date select reflects date formatting option
* Added: Limit Extra Products Option scan to filtered Order IDs if provided
* Added: Filter Products by Shipping Classes
* Added: Support for product_shipping_class filter in Products export for CRON export engine
* Added: Manage Custom Product Fields to Products export type
* Added: Manage Custom User Fields to Users export type
* Added: Manage Custom Customer Fields to Customers export type
* Added: Execute button to Scheduled Export to trigger immediate scheduled exports
* Added: Support for the TSV file type
* Added: Export fields support to Orders screen export actions
* Fixed: Custom Variations not exporting in some situations for Products export
* Added: Populate default Attributes for Product exports with custom Attributes
* Fixed: Custom user meta not being included in Order exports
* Fixed: Order export support for Checkout Manager Pro
* Added: Support for export of empty field labels in Checkout Manager Pro
* Fixed: DateTimePicker displaying erroneous options
* Fixed: Filter Variations by Product Status in Products export
* Fixed: HTML quotes included in CSV, XLS and XLSX column headers
* Changed: Increased key limit to 48 characters
* Fixed: Fixed filename not display correctly
* Changed: Using WC_Logger for saving error logs to wc-logs
* Changed: Filter Orders by Product is now pre-WP_Query
* Fixed: Detection of CRON export with no export fields
* Fixed: Detection of trashed scheduled exports
* Fixed: Date filtering error on Orders fixed date
* Fixed: Limit Screen Options to Archives tab
* Fixed: Total Weight not being filled for Orders export
* Fixed: Checkout Field Editor support for Additional fields
* Fixed: Fatal PHP error when activating multiple instances of SED
* Added: Notice to Edit scheduled export screen if scheduled exports is disabled globally
* Changed: Display Export File and Export Details for TSV file type
* Added: Save number of each scheduled exports ran
* Added: Save timestamp of each last scheduled export ran
* Changed: Styled the Export Details meta boxes
* Added: Remember Product Type filter on Export screen
* Fixed: Styling change in WooCommerce affecting Plugin screen

= 1.9.7 =
* Changed: Using WP_Query by default for Subscriptions export
* Fixed: Filter by Order Status not working in Orders export
* Fixed: PHP warning notices for json_ids on Filter Orders by Product
* Fixed: Failed export notice showing for non-last_export Orders
* Added: Hide option within Field Editor for excess export fields
* Added: Since last export to scheduled export engine
* Added: Has Downloads to Orders export
* Added: Has Downloaded to Orders export
* Added: Disable SFTP scheduled export option if required PHP module is missing

= 1.9.6 =
* Added: Barcode to Order Items within Orders export
* Added: Barcode Type to Order Items within Orders export
* Fixed: Empty Order exports since introducing Select2 Enhanced
* Fixed: Export buttons on Edit Orders screen not working

= 1.9.5 =
* Added: Filter Coupons by Discount Type
* Added: Usage Count to Coupons export
* Added: Used By to Coupons export
* Added: Usage Cost to Coupons export
* Added: Export Orders since last export under Filter Orders by Date
* Added: Export Status column to Orders screen
* Added: Detection of failed export and reset of export flags
* Added: Export support for Barcodes for WooCommerce
* Fixed: Formatting of prefix/suffix within PDF Invoice Number export field
* Changed: Filter Order by Product using Select2 and AJAX
* Changed: Filter Order by Product in scheduled export using Select2 and AJAX
* Added: Biographical Info to Users export
* Added: AIM to Users export
* Added: Yahoo IM to Users export
* Added: Jabberr / Google Talk to Users export
* Changed: Do not include Variations by default

= 1.9.4 =
* Added: Option to split Product Gallery over multiple rows
* Added: Support for Field Editor within unique Order Items Formatting
* Fixed: Serialised arrays now export array values
* Added: WordPress Filters to override XML nodes
* Added: WordPress Filters to override associated Categories to Products
* Added: Coupon Description to Orders export
* Added: WPML integration for Post and Term counts
* Added: Filter Products by Language
* Added: Filter Categories by Language
* Added: Filter Tag by Language
* Added: Filter Orders by Sequential Order Number via CRON
* Added: Filter Orders by Sequential Order Number Pro via CRON
* Added: Support for WooCommerce EU VAT Number in Orders
* Added: Support for WooCommerce Hear About Us in Order, Customer and User exports
* Fixed: Detection of duplicate store to hide prompts after dismiss
* Added: Support for WooCommerce Wholesale Pricing in Products export
* Added: Filter Products by Status via scheduled export engine
* Added: Filter Products by Status via CRON engine
* Added: Warning notice to Enable Archives
* Changed: Enable Archives is disabled by default
* Added: Trigger export on new Order to Settings pane
* Added: Option to enable/disable trigger on new Order
* Added: Option to control export format of trigger export on new Order
* Added: WordPress Filters to override WP_Query, WP_User_Query and get_terms
* Added: Show on screen tab options for columns within the Archives tab
* Added: Filesize column to Archives tab
* Added: Rows column to Archives tab
* Fixed: Compatibilty sharing PHPExcel class with other WordPress Plugins
* Changed: Filter Orders by multiple Customers
* Fixed: Field escaping formatting in CSV export files
* Added: Custom User field support to Customer exports
* Fixed: Column mismatch for unique Order Items Formatting rule in Order exports
* Changed: Filter Orders by Product includes Variations
* Fixed: Variations within Filter Orders by Product include Attribute values
* Added: Filter Orders by Product to scheduled exports

= 1.9.3 =
* Fixed: Saving Default e-mail subject within Settings
* Changed: Increased maxlength on Once every x minutes interval
* Added: SFTP protocol for scheduled exports
* Added: Filter to override path to sys_get_temp_dir()
* Fixed: Currency symbol beside price fields in latest WooCommerce
* Fixed: Strip HTML from price fields in latest WooCommerce
* Added: Filter Orders by Payment Gateway
* Added: Payment Gateway count to Filter Orders by Payment Gateway
* Added: Configure panel to Recent Scheduled Exports widget on WordPress Dashboard
* Added: Number of recent scheduled exports form field to Dashboard widget
* Added: Disable scheduled exports on duplicate site or staging site detection
* Changed: Filter Orders by Billing Country supports multple options
* Changed: Filter Orders by Order Status supports multiple options
* Changed: Filter Products by Product Type supports multiple exports
* Added: Orders Screen section to Settings screen
* Added: Actions display fields to show/hide export actions on Orders screen
* Changed: Hide Filter Products by Brand if Brands are unavailable
* Changed: Filter Orders by Order Status supports multple options
* Changed: Filter Orders by User Role supports multiple options
* Changed: Filter Products by Product Type supports multiple options
* Changed: Filter Products by Product Status supports multiple options
* Added: Filter Orders by Shipping Method
* Fixed: Encoding issue affecting UTF-8 in PHPExcel formats

= 1.9.2 =
* Fixed: Variable date ranges in Order exports
* Added: Order Items Booking ID to Orders export
* Added: Order Items Booking Start Date to Orders export
* Added: Order Items Booking End Date to Orders export
* Added: Scheduling section for Scheduled Exports
* Changed: Moved "Once every (x) minutes) to Scheduling section
* Added: Export daily/weekly/monthly to Scheduling section
* Added: Commence exports from now option to Scheduling section
* Added: Commence exports from date option to Scheduling section
* Added: Override XML nodes via Field Editor for exports
* Fixed: Leading 0's being stripped from numbers in CSV, XLS, XLSX
* Fixed: Subscriptions exports for stores with the is_large_store flag set
* Added: Notice to indicate where is_large_store flag is set within Subscriptions 
* Added: WordPress filter to override Order Shipping ID
* Fixed: Links to WordPress Plugins Search using Term filter instead of Tag
* Added: Time support to scheduling exports
* Added: Support for custom Product Add-ons in Orders
* Changed: Hide Add New button on Export screen
* Added: Fixed filename support for Export to FTP scheduled exports
* Changed: Error styling within Recent Scheduled Exports widget on WordPress Dashboard

= 1.9.1 =
* Fixed: Subscriptions export not working
* Changed: Filter Subscriptions by Subscripion Product uses jQuery Chosen
* Added: Filter Orders by Billing Country to scheduled export
* Added: Filter Orders by Shipping Country to scheduled export
* Added: Filter Orders by Product to scheduled export
* Fixed: Filter Orders by Coupon not working
* Changed: Exclude Variations from Filter Orders by Product dropdown
* Fixed: Default empty Order Items Type to Line Item for CRON Order exports
* Added: order_items_types support to CRON attributes
* Added: Notice when fatal error is encountered from memory/timeout
* Fixed: Conflict of XML class name
* Fixed: Order Items: Stock missing in individual Order Items Formatting of Orders export
* Fixed: Scenario where open_basedir is enabled and ./tmp is off limits
* Fixed: Delimiter override not working in CRON exports
* Added: WooCommerce Bookings integration for Booking Date in Orders export
* Added: Booking to Filter Products by Product Type in Products export
* Added: Booking Has Persons to Products export
* Added: Booking Has Resources to Products export
* Added: Booking Base Price to Products export
* Added: Booking Block Price to Products export
* Added: Booking Display Price to Products export
* Added: Booking Requires Confirmation to Products export
* Added: Booking Can Be Cancelled to Products export
* Added: Export to XLSX to Orders screen
* Fixed: Order Discount not being filled in WooCommerce
* Changed: Renamed Order Excl. Tax to Order Subtotal Excl. Tax
* Added: Order Total Tax to Orders export
* Added: Order Tax Percentage to Orders export

= 1.9 =
* Fixed: Default to Attribute Name if Label is empty
* Fixed: Product Gallery exporting only Image ID
* Changed: Reduced memory requirements for Products export
* Changed: Reduced memory requirements for Orders export
* Fixed: Filter Products by multiple tax_query arguments
* Fixed: Variant Products with empty Price
* Fixed: Advanced Google Product Feed: Product Type not exporting
* Added: Support for XLSX Excel 2013 export format
* Changed: Using PHPExcel library for CSV, XLS and XLSX export file generation
* Fixed: Delete multiple archives via bulk actions
* Added: RSS export format
* Added: RSS Settings section to Settings tab
* Added: RSS Title option to Settings tab
* Added: RSS Link option to Settings tab
* Added: RSS Description option to Settings tab
* Fixed: FTP Host now strips out excess prefixes
* Fixed: WooCommerce Checkout Manager Pro integration
* Added: Support for multiple function/class detection on Export Modules
* Fixed: Empty SKU on Order Items in Orders export
* Added: Total Order Items to Orders export
* Added: Strip tags from Description/Excerpt to Export Options
* Fixed: Filter Orders by Product missing loads of Products
* Changed: Filter Orders by Product uses jQuery Chosen
* Changed: Filter Orders by Product Category uses jQuery Chosen
* Changed: Filter Orders by Product Tag uses jQuery Chosen
* Changed: Filter Orders by Product Brand uses jQuery Chosen
* Changed: Filter Orders by Coupon Code uses jQuery Chosen
* Changed: Filter Products by Product Category uses jQuery Chosen
* Changed: Filter Products by Product Tag uses jQuery Chosen
* Changed: Filter Products by Product Brand uses jQuery Chosen
* Changed: Filter Products by Product Vendor uses jQuery Chosen
* Fixed: Default Order Line Types for Orders export to Line Item
* Fixed: Order Items: SKU empty for Product Variations in Orders export
* Fixed: Exclude CRON exports from Recent Scheduled Exports Dashboard widget
* Changed: Filter Products by Stock Status now takes Stock Qty into consideration

= 1.8.9 =
* Fixed: Export Product Attributes in Product export
* Added: Support for custom Attributes in Product export
* Added: Default Attributes to Product export
* Fixed: Attribute taxonomy missing from Order Items: Product Variation in WC 2.2+
* Added: Support for Ship to Multiple Address for Order export
* Changed: Export to FTP now deletes the archived export
* Fixed: Variables not being included in Product export when filtering by Categories/Tags/Brands/Vendors
* Added: Support for Sequential Order ID within WooCommerce Jetpack
* Added: Support for Sequential Order ID formatting within WooCommerce Jetpack Plus
* Fixed: Return default Post ID where Sequential Order ID is empty
* Fixed: Delete export file after e-mailing via Scheduled export
* Changed: Moved default e-mail receipient and e-mail subject to Export method options
* Changed: Moved Default remote POST to Export method options
* Added: Delete All archives button to Archives screen
* Fixed: Incorrect mime type for some XML exports
* Changed: Archives table uses WP_List_Table class
* Added: Format colunn to Archives list
* Added: Pagination to Archives list
* Added: Number of Archives to Screen Options on Archives list
* Changed: Removed media icon from Archives list
* Fixed: Date filtering of Orders is now WP_Query-based
* Added: Support for exporting WooCommerce Brands
* Added: Support for Featured Image Thumbnail in Product export
* Added: Support for Product Gallery Thumbnail in Product export
* Fixed: Filter Orders by Order Status via CRON
* Fixed: Ordering of Product ID's when exporting Product Variations

= 1.8.8 =
* Fixed: Product Price broken for non-decimal currencies
* Fixed: Total Sales not included in Orders export
* Added: Notice for empty exports
* Changed: Remove Trashed Products from exports

= 1.8.7 =
* Added: Total Quantity export field for Orders
* Added: Filter Orders by Billing Country for Orders
* Added: Filter Orders by Shipping Country for Orders
* Fixed: Filter Orders by Date radio options are selected via jQuery calendar or variable date
* Added: MSRP Pricing to Orders export
* Added: Order Items: RRP to Orders export
* Added: Product Subscription details to Products export
* Fixed: CRON exports and scheduled exports not working with WOO_CD_DEBUG activated
* Added: Filter Orders by Product for Orders
* Added: Download link to attachments on Archives screen
* Added: Filter Products by Product Category to Scheduled Exports
* Added: Filter Products by Product Tag to Scheduled Exports
* Added: Reset Sorting link to Export Fields box
* Added: Custom Order meta support to Subscriptions exports
* Added: Order Item Attribute fields to Orders export

= 1.8.6 =
* Fixed: Prices not formatted to local currency
* Added: Plugin update notification where Visser Labs Updater is not installed
* Added: Filter Orders by Product Brand
* Added: Filter Products by Product Brand
* Added: Notice when WordPress transient fails to store debug log
* Added: Bulk export Orders from the Orders screen
* Added: Filter Products by Product Vendor
* Added: Support for line break as Category Separator in CSV and XLS
* Fixed: Commission Status count not working
* Added: Filter Products by Stock Status in Scheduled Exports
* Fixed: stock_status filter not working in CRON
* Fixed: Extra Product Options support in Orders
* Fixed: Ignore page breaks, section breaks and hidden fields in Gravity Forms integration
* Fixed: Ignore duplicate fields in Gravity Forms integration
* Changed: Order Notes and Customer Notes uses line break instead of category separator
* Changed: Order Items: Product Variation uses line break instead of category separator
* Added: Comment Date to Order Notes and Customer Notes
* Added: Filters to more export fields for Theme/Plugin overrides
* Fixed: Fill Billing: E-mail with User e-mail address if empty in Order
* Fixed: WooCommerce Sequential Order Numbers Pro checking for wrong class on Export Modules

= 1.8.5 =
* Fixed: Include all Order Status for WooCommerce 2.2+ in Orders export
* Fixed: Integration with Custom Billing fields in WooCommerce Checkout Fields Editor
* Fixed: Support for Custom Shipping fields in WooCommerce Checkout Fields Editor
* Fixed: Support for Custom Fields in WooCommerce Checkout Fields Editor
* Changed: Purchase Date to Order Date
* Changed: Purchase Time to Order Time
* Added: Support for Today in Filter Orders by Order Date
* Added: Support for Yesterday in Filter Orders by Order Date
* Added: Support for Current Week in Filter Orders by Order Date
* Added: Support for Last Week in Filter Orders by Order Date
* Fixed: Filter Orders by Order Date for Current Month
* Added: Support for variable filtering of Order Date in scheduled exports
* Added: Heading to Order Checkout field labels for WooCommerce Checkout Manager Pro
* Fixed: Multiple e-mail addresses within Default e-mail recipient
* Fixed: Variation Product Type filter for Products breaking export
* Added: Support for WooCommerce Follow-Up Emails Opt-outs for Customer exports
* Fixed: Filter Order by Order Status in Scheduled Exports not saving
* Added: All default option to Filter Order by Order Date in Scheduled Exports
* Added: Filter Subscriptions by Subscription Product
* Added: Filter Orders by Order Status to CRON engine
* Added: Filter Orders by mulitple Order ID to CRON engine
* Added: Filter Orders by Order Date to CRON engine
* Fixed: Filter Orders by Order Date in scheduled export
* Fixed: Customer Meta fields not being filled in Customer export
* Added: Filter Commissions by Commission Date
* Added: Filter Orders by Order ID
* Fixed: Formatting of Post Status in Commissions export
* Fixed: Default value for Paid Status in Commissions export
* Added: Product SKU to Commissions export
* Added: New tab to Help pulldown on Store Export screen
* Added: Filter Customers by User Role filter
* Added: Line ending formatting option to Settings screen
* Changed: Moved add_action for export options to admin.php
* Fixed: Remove scheduled export from WP CRON if de-activated
* Added: Download as CSV to Actions list on Orders screen
* Added: Download as XML to Actions list on Orders screen
* Added: Download as Excel 2007 (XLS) to Actions list on Orders screen
* Added: Download as CSV to Action list on Edit Order screen
* Added: Download as XML to Action list on Edit Order screen
* Added: Download as Excel 2007 (XLS) to Action list on Edit Order screen
* Added: Compatibility for WC 2.1 for Action list on Orders screen
* Added: Filter Products by Product Status to CRON engine

= 1.8.4 =
* Fixed: Saving Default e-mail recipient on Settings screen
* Fixed: Changing the Scheduled Export interval forces WP_CRON to reload the export
* Fixed: Scheduled Export of Orders filtered by Order Status not working
* Changed: File Download is now Download File URL
* Added: Download File Name support to Products export
* Added: Variation Formatting option to Products export
* Added: Product URL supports Variation URL with attributes
* Fixed: Filter Products by Product Type for Downloadable and Virtual
* Fixed: Count of Downloadable and Virtual within Filter Products by Product Type
* Fixed: Order Status displaying 'publish' in WooCommerce pre-2.2
* Fixed: Formatting of Post Status in Orders export
* Changed: Moved woo_ce_format_product_status to formatting.php
* Changed: Renamed woo_ce_format_product_status to woo_ce_format_post_status
* Added: Disregard column headers in CSV/XLS export option to Settings screen
* Changed: Hide Post Status on Subscriptions export for pre-WooCommerce 2.2 stores
* Fixed: Formatting of Order Status on Subscriptions exports
* Added: Filter Subscriptions by Subscription Status
* Fixed: Multi-line fields breaking CSVTable in Debug Mode
* Fixed: Support for exporting Orders in WooCommerce 2.2.5-2.2.6

= 1.8.3 =
* Fixed: Next Scheduled export in... not accounting for GMT offset
* Added: Order Subtotal field to Orders export
* Added: Shipping Classes to Archives filter list
* Added: In-line links of Settings page to Overview screen
* Added: Check that get_customer_meta_fields method exists within Users export
* Added: Check that get_customer_meta_fields method exists within Subscription export
* Added: Support for WooCommerce Checkout fields in Subscription export
* Added: Support for custom User meta in Subscription export
* Added: Vendor ID to Product Vendors export
* Added: Product Vendor URL to Product Vendors export
* Added: Support for exporting Commissions generated by Product Vendors
* Added: Commission ID to Commissions export
* Added: Commission Date to Commissions export
* Added: Commission Title to Commissions export
* Added: Product ID to Commissions export
* Added: Product Name to Commissions export
* Added: Vendor User ID to Commissions export
* Added: Vendor Username to Commissions export
* Added: Vendor Name to Commissions export
* Added: Commission Amount to Commissions export
* Added: Commission Status to Commissions export
* Added: Post Status to Commissions export
* Added: Support for sorting commissions
* Added: Filter Commissions by Product Vendor
* Added: Filter Commissions by Commission Status
* Fixed: PHP warnings showing for PHP 5.2 installs

= 1.8.2 =
* Added: Order support for Extra Product Options
* Fixed: Custom Product meta not showing up for Order Items
* Fixed: Custom Order meta not showing up for Orders
* Fixed: Detect corrupted Date Format
* Added: Detection of corrupted WordPress options at export time
* Fixed: Gravity Forms error affecting Orders
* Added: Gravity Form label to Order Items export fields
* Added: Export Fields to CRON Settings to control fields
* Added: Export Fields to Scheduled Exports Settings to control fields
* Added: Product Excerpt to Order Items for Orders export
* Added: Product Description to Order Items for Orders export
* Added: Total Sales to Products export
* Fixed: Advanced Google Product Feed not being included in Products export
* Added: Custom User meta to Customers export
* Added: Support for exporting Shipping Classes

= 1.8.1 =
* Changed: Product URL is now External URL
* Added: Product URL is the absolute URL to the Product
* Added: Support for custom User fields
* Fixed: Admin notice not showing for saving custom fields

= 1.8 =
* Added: Export Modules to the Export list

= 1.7.9 =
* Added: Notice for unsupported PHP 5.2
* Fixed: Fatal error due to anonymous functions under PHP 5.2

= 1.7.8 =
* Fixed: Subscription export not working via CRON
* Added: Support for exporting Product Vendors in Products export
* Added: Support for exporting Vendor Commission in Products export
* Added: Support for exporting Product Vendors in Orders export
* Added: Product Vendors export type
* Added: Support for Term ID in Product Vendors export
* Added: Support for Name in Product Vendors export
* Added: Support for Slug in Product Vendors export
* Added: Support for Description in Product Vendors export
* Added: Support for Commission in Product Vendors export
* Added: Support for Vendor Username in Product Vendors export
* Added: Support for Vendor User ID in Product Vendors export
* Added: Support for PayPal E-mail Address in Product Vendors export
* Added: Support for WooCommerce Branding
* Added: E-mail Subject field to Settings screen for Scheduled Exports
* Added: Default notices on Settings screen for export types with no filters
* Added: Default notices on Settings screen for export methods with no filters
* Added: Export to FTP for Scheduled Exports and CRON engine
* Fixed: Fatal error affecting CRON engine (introduced in 1.7.7)
* Added: Dashicons to the Export and Settings screen
* Added: Dashboard widget showing next scheduled export and controls
* Fixed: Warning notice on export of Products
* Added: Order By as XML attribute
* Added: Order as XML attribute
* Added: Limit Volume as XML attribute
* Added: Volume Offset as XML attribute

= 1.7.7 =
* Added: E-mail Address to Subscriptions export
* Changed: Moved User related functions to users.php
* Fixed: Sorting error affecting Products export
* Fixed: Compatibility with PHP 5.3 and above
* Fixed: Compatibility with WooCommerce 2.2+
* Added: Backwards compatibility for Order Status with pre-2.2
* Changed: Moved Brands sorting to brands.php

= 1.7.6 =
* Fixed: Category Image generating PHP warning notices
* Fixed: Default Export Type to Product if not set
* Fixed: Default Date Format for exports if not set
* Changed: Renamed Order Items Types to Order Item Types under Export Options
* Fixed: Ordering of export fields not saving
* Added: Support for filtering Products by Product Type in scheduled exports
* Added: Support for custom date formatting under Settings screen

= 1.7.5 =
* Added: Gravity Form ID to Orders export
* Added: Gravity Form Name to Orders export
* Added: Support for changing the export format of scheduled exports
* Fixed: Display of multiple queued Admin notices
* Fixed: PHP warning on Subscriptions export
* Fixed: Attributes showing Term Slug in Products export
* Fixed: Attributes not including Taxonomy based Terms in Products export
* Fixed: Empty export rows under certain environments in Products export
* Added: Support for filtering Orders by Order Dates for scheduled exports
* Added: Field Editor for all export types
* Added: Sortable export fields

= 1.7.4 =
* Fixed: Limit volume for Users export
* Fixed: Offset for Users export
* Fixed: Pickup Location Plus not working with unique Order Items formatting
* Added: Billing: Street Address 1 to Orders export
* Added: Billing: Street Address 2 to Orders export
* Added: Shipping: Street Address 1 to Orders export
* Added: Shipping: Street Address 2 to Orders export
* Fixed: Validation of $export arguments on CRON export
* Added: Filter Orders by Product Category to Orders export
* Added: Filter Orders by Product Tags to Orders export
* Fixed: XML file export generating surplus HTML
* Added: Basic support for WooCommerce Checkout Add-ons in Orders export
* Added: Support for filtering Orders by Order Status for scheduled exports

= 1.7.3 =
* Added: Support for custom Shipping and Billing fields (Poor Guys Swiss Knife) in Orders export
* Added: Support for exporting as XLS file
* Changed: Moved Default e-mail recipient to General Settings
* Changed: Moved Default remote URL POST to General Settings
* Fixed: Product Addons not showing when using unique export formatting for Orders
* Added: Support for checkbox/multiple answers in Product Addons for Orders export
* Fixed: Empty Settings options on Plugin activation in some stores
* Fixed: Skip generator Customer count for large User stores
* Fixed: Alternative Filter Orders by Customer widget for large User stores
* Fixed: Reduced memory footprint for generating User counts
* Fixed: Reduced memory footprint for generating Order counts
* Fixed: Added detection and correction of incorrect file extensions when exporting
* Fixed: Export support for Currency Switcher in Orders

= 1.7.2 =
* Added: Support for Invoice Date (WooCommerce Print Invoice & Delivery Note) in Orders export
* Added: Support for custom Product Attributes using Custom Product meta dialog
* Fixed: Saving XML files to WordPress Media and Archives screen
* Fixed: Debug mode with XML files
* Fixed: Exporting custom Product Attributes in Product export
* Added: Support for Order Item: Stock in Orders export

= 1.7.1 =
* Added: Support for Invoice Number (WooCommerce PDF Invoices & Packing Slips) in Orders export
* Added: Support for Invoice Date (WooCommerce PDF Invoices & Packing Slips) in Orders export

= 1.7 =
* Added: Subscriptions export type
* Added: Support for Subscription Key in Subscriptions export
* Added: Support for Subscription Status in Subscriptions export
* Added: Support for Subscription Name in Subscriptions export
* Added: Support for User in Subscriptions export
* Added: Support for User ID in Subscriptions export
* Added: Support for Order ID in Subscriptions export
* Added: Support for Order Status in Subscriptions export
* Added: Support for Post Status in Subscriptions export
* Added: Support for Start Date in Subscriptions export
* Added: Support for Expiration in Subscriptions export
* Added: Support for End Date in Subscriptions export
* Added: Support for Trial End Date in Subscriptions export
* Added: Support for Last Payment in Subscriptions export
* Added: Support for Next Payment in Subscriptions export
* Added: Support for Renewals in Subscriptions export
* Added: Support for Product ID in Subscriptions export
* Added: Support for Product SKU in Subscriptions export
* Added: Support for Variation ID in Subscriptions export
* Added: Support for Coupon Code in Subscription export
* Added: Support for Limit Volume in Subscription export

= 1.6.6 =
* Fixed: Admin notices not being displayed
* Fixed: CRON export not e-mailing to Default Recipient by default
* Added: Export filters support for Scheduled Exports
* Added: Filter Orders by Order Status within Scheduled Exports

= 1.6.5 =
* Fixed: Only export published Orders, no longer include trashed Orders
* Added: WordPress filter to override published only Orders export rule
* Changed: Filter Orders by Customer matches export screen UI
* Added: Post Status export field for Orders
* Added: Order count indicators for Filter Orders by Coupon Code
* Added: Order count indiciator for Filter Orders by Order Status
* Added: Export type is remembered between screen refreshes
* Changed: Moved Product Sorting widget to products.php
* Changed: Moved Filter Products by Product Category widget to products.php
* Changed: Moved Filter Products by Product Tag widget to products.php
* Changed: Moved Filter Products by Product Status widget to products.php
* Added: Order Item Variation support for non-taxonomy variants
* Fixed: Order Item Variation empty for some Order exports
* Fixed: Scheduled export email template filename outdated
* Added: Check that scheduled export email template exists
* Added: Customer Message export field for Orders
* Fixed: Customer Notes working for WooCommerce 2.0.20+
* Fixed: Order Notes working for WooCommerce 2.0.20+
* Fixed: Empty Shipping Method and Shipping Method ID in WooCommerce 2.1+

= 1.6.4 =
* Fixed: Check for wc_format_localized_price() in older releases of WooCommerce
* Added: Brands export type
* Added: Support for Brand Name in Brands export
* Added: Support for Brand Description in Brands export
* Added: Support for Brand Slug in Brands export
* Added: Support for Parent ID in Brands export
* Added: Support for Brand Image in Brands export
* Added: Support for sorting options in Brands export
* Fixed: Added checks for 3rd party classes and legacy WooCommerce functions for 2.0.20
* Added: Support for Category Description in Categories export
* Added: Support for Category Image in Categories export
* Added: Support for Display Type in Categories export

= 1.6.3 =
* Added: Brands support to Orders export
* Added: Brands support for Order Items in Orders export
* Fixed: PHP warning notice in Orders export
* Added: Option to filter different Order Items types from Orders export
* Changed: Payment Status to Order Status to reduce confusion
* Fixed: Gravity Forms Order Items support
* Added: Export support for weight of Order Items
* Added: Export support for total weight of Order Items
* Added: Export support for total weight of Order

= 1.6.2 =
* Fixed: Fatal PHP error on first time activation

= 1.6.1 =
* Changed: Removed requirement for basic Store Exporter Plugin
* Added: Coupon Code to Orders export
* Added: Export Users
* Added: Support for User ID in Users export
* Added: Support for Username in Users export
* Added: Support for User Role in Users export
* Added: Support for First Name in Users export
* Added: Support for Last Name in Users export
* Added: Support for Full Name in Users export
* Added: Support for Nickname in Users export
* Added: Support for E-mail in Users export
* Added: Support for Website in Users export
* Added: Support for Date Registered in Users export
* Added: Support for WooCommerce User Profile fields in Users export
* Added: Product Gallery formatting support includes Media URL
* Added: Sorting support for Users export
* Added: Sorting options for Coupons
* Added: Filter Orders by Coupon Codes

= 1.6 =
* Fixed: Empty category separator on Order Items
* Added: Support for exporting Checkout Field Manager
* Added: Support for exporting WooCommerce Sequential Order Numbers (free!)
* Added: Support for exporting WooCommerce Print Invoice & Delivery Note
* Fixed: Support for WooCommerce Checkout Manager (Free!)
* Added: Support for WooCommerce Checkout Manager Pro
* Added: Support for Currency Switcher in Orders export
* Added: Support for Checkout Field Editor

= 1.5.8 =
* Changed: Removed User ID binding for Customers export
* Fixed: Empty exports
* Changed: Better detection of empty exports
* Changed: Better detection of empty data types
* Added: Customer Filter to Export screen
* Added: Filter Customers by Order Status option 
* Added: Using is_wp_error() throughout CPT and Term requests

= 1.5.7 =
* Added: XML Settings section to Settings screen
* Added: Presentation options for attributes within XML export

= 1.5.6 =
* Fixed: Force file extension if removed from the Filename option on Settings screen
* Changed: Reduced memory load by storing $args in $export global

= 1.5.5 =
* Fixed: Fatal error if Store Exporter is not activated

= 1.5.4 =
* Fixed: Fatal error on individual cart item export in XML

= 1.5.3 =
* Fixed: Coupon export to XML issue

= 1.5.2 =
* Added: Support for exporting as XML file
* Added: XML export support for Products
* Added: XML export support for Categories
* Added: XML export support for Tags
* Added: XML export support for Orders
* Added: XML export support for Customers
* Added: XML export support for Coupons
* Changed: Created new functions-xml.php file
* Added: wpsc_cd_generate_xml_filename() to functions-xml.php
* Added: wpsc_cd_generate_xml_header() to functions-xml.php

= 1.5.1 =
* Added: Scheduled export type option
* Fixed: Scheduled export not triggering
* Changed: Remove last scheduled export immediately instead of waiting to run
* Added: Support for overriding field delimiter in CRON exports
* Added: Support for overriding category separator in CRON exports
* Added: Support for overriding BOM support in CRON exports
* Added: Support for overriding encoding in CRON exports
* Added: Support for overriding limit in CRON exports
* Added: Support for overriding offset in CRON exports
* Added: Support for overriding escape formatting in CRON exports

= 1.5 =
* Added: Support for scheduled exports
* Changed: Using WP_Query instead of get_posts for bulk export
* Changed: Moved export function into common space for CRON and scheduled exports
* Added: Support for exporting Local Pickup Plus fields in Orders
* Changed: Removed duplicate Order Items: Type field
* Fixed: Faster processing of Shipping Method and Payment Methods labels

= 1.4.9 =
* Added: Support for exporting Local Pickup Plus fields in Orders
* Added: Support for e-mailing exported CSV via CRON
* Added: Export e-mail template to available WooCommerce e-mails

= 1.4.8 =
* Changed: Moved Max unique Order items option to Settings tab
* Added: Support for CRON triggered exports
* Added: Support for exporting CSV URL via CRON
* Added: Support for exporting file system path of CSV via CRON
* Added: Support for setting ordering of export types via CRON
* Added: Support for setting ASC/DESC sorting of export types via CRON
* Added: Support for saving CSV to WordPress Media via CRON
* Added: Support for authenticating CRON secret key
* Added: uninstall.php
* Changed: Added Plugin activation functions for generating default CRON secret key

= 1.4.7 =
* Fixed: Ordering of Order Items: Product Variations for multiple variations

= 1.4.6 =
* Added: Support for multiple variation within Order Items: Product Variation
* Added: Order Items: Category and Tags to Orders export
* Fixed: Empty Quantity in Order Items: Quantity for unique order items formatting

= 1.4.5 =
* Added: Advanced Custom Fields support for Products export
* Changed: Dropped $wpsc_ce global
* Added: Using Plugin constants
* Added: Cost of Goods integration for Orders export

= 1.4.4 =
* Changed: Removed functions-alternatives.php
* Fixed: Compatibility with legacy WooCommerce 1.6

= 1.4.3 =
* Fixed: Formatting of Order Items: Type for tax
* Added: Memory optimisations for get_posts()
* Changed: Removed functions-alternatives.php
* Added: Custom Product Fields support
* Fixed: Filter Orders by Date option

= 1.4.2 =
* Fixed: PHP error affecting Coupons export
* Fixed: Date Format support for Purchase Date and Expiry Date

= 1.4.1 =
* Added: Cost of Goods integration for Products export
* Added: Per-Product Shipping integration for Products export
* Fixed: Export Orders by User Roles
* Added: Formatting of User Role

= 1.4 =
* Fixed: User Role not displaying within Customers export in WordPress 3.8
* Added: New automatic Plugin updater

= 1.3.9 =
* Added: Payment Gateway ID to Orders export
* Added: Shipping Method ID to Orders export
* Added: Shipping Cost to Orders export
* Added: Checkout IP Address to Orders export
* Added: Checkout Browser Agent to Orders export
* Added: Filter Orders by User Role for Orders export
* Added: User Role to Orders export
* Added: User Role to Customers export

= 1.3.8 =
* Added: Support for Sequential Order Numbers Pro
* Fixed: Fatal error affecting Order exports

= 1.3.7 =
* Changed: Added Docs, Premium Support, Export link to Plugins screen

= 1.3.6 =
* Fixed: Fatal error affecting Order exports

= 1.3.5 =
* Changed: Display detection notices only on Plugins screen
* Added: Display notice when WooCommerce isn't detected
* Fixed: Admin icon on Store Exporter screen
* Added: Export Details widget to Media Library for debug
* Fixed: Fatal error affecting Custom Order Items

= 1.3.4 =
* Fixed: Order Notes on Orders export
* Added: Notice when Store Exporter Plugin is not installed
* Changed: Purchase Date to exclude time
* Added: Total excl. GST
* Added: Purchase Time
* Added: Commenting to each function

= 1.3.3 =
* Changed: Ammended Custom Order Fields note
* Changed: Store Export menu to Export
* Added: Custom Order Items meta support
* Changed: Extended Custom Order meta support
* Added: Help suggestions for Custom Order and Custom Order Item meta
* Added: Product Add Ons integration

= 1.3.2 =
* Added: jQuery Chosen support to Orders Customer dropdown

= 1.3.1 =
* Fixed: Column issue in unique Order Items formatting

= 1.3 =
* Added: New Order date filtering methods
* Added: Order Items formatting
* Added: Order Item Tax Class option
* Added: Order Item Type option
* Added: Formatting of Order Item Tax Class labels
* Added: Formatting of Order Item Type labels
* Fixed: Notices under WP_DEBUG
* Added: N/A value for manual Order creation

= 1.2.8 =
* Fixed: Error notice under WP_DEBUG

= 1.2.7 =
* Added: Escape field formatting option
* Added: Payment Status (number) option
* Added: Filter Orders by Customer option
* Added: Filter Orders by Order Status option

= 1.2.6 =
* Fixed: Order Date to include todays Orders
* Fixed: Removed excess separator at end of each line
* Moved: Order Dates to Order Options
* Added: Order Options section

= 1.2.5 =
* Fixed: Coupons export

= 1.2.4 =
* Changed: Added formatting to Purchase Date
* Fixed: Limit Volume and Offset affecting Orders

= 1.2.3 =
* Fixed: Error on landing page for non-base Plugin users
* Fixed: Link on landing page to Install Plugins

= 1.2.2 =
* Fixed: Customers report
* Added: Total Spent to Customers report
* Added: Completed Orders to Customers report
* Added: Total Orders to Customers report
* Fixed: Customers counter
* Added: Prefix and full Country and State name support

= 1.2.1 =
* Added: Custom Sale meta support

= 1.2 =
* Fixed: Sale export

= 1.1 =
* Added: Admin notice if Store Exporter is not activated
* Added: WordPress Plugin search link to Store Exporter
* Added: Export link to Plugins screen
* Fixed: Duplicate Store Export menu links

= 1.0 =
* Added: First working release of the Plugin

== Upgrade Notice ==

= 2.3 =
2.3 is a major update introducing compatibility with WooCommerce 3.0's new CRUD data structure, efforts to provide backwards compatibility will continue but you are recommended to upgrade to WooCommerce 3.0 when possible.

= 2.0 =
2.0 is a major update introducing our new Scheduled Export engine, so it is important that you review your Scheduled Export settings after updating from WooCommerce > Store Export > Settings > Scheduled Exports.

== Disclaimer ==

It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.
=== Affiliate Super Assistent ===
Tags: amazon, affiliate, monetize, commission, marketing
Contributors: worschtebrot
Requires at least: 2.1
Requires PHP: 7.2
Tested up to: 6.6.1
Stable tag: 1.5.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The flexible plugin for WordPress affiliates working with Amazon. Create your own templates, embed products by use of [asa]ASIN[/asa] shortcodes

== Description ==

Affiliate Super Assistent (ASA1) lets you easily integrate Amazon products into your WordPress pages.
By using the template feature, you can present the products in different styles on different pages. All by using simple [asa] shortcodes.

[Usage guide](https://www.wp-amazon-plugin.com/usage/)

For the **complete range of functions**, [get ASA2](https://getasa2.com/).

Find the latest news on the [blog](https://www.wp-amazon-plugin.com/blog/) and check out the [Step-by-step guide](https://www.wp-amazon-plugin.com/guide/).

Features:

* Make money with WordPress and Amazon's affiliate program
* Supports Amazon PA API 5.0
* Utilizes the Amazon Product Advertising API to receive product data
* Ease of use with asa-**Shortcode** tags
* Lets you design your own product **templates**
* Supports all Amazon stores activated for the API / webservice: Australia (AU), Brazil (BR), Canada (CA), China (CN), Germany (DE), Spain (ES), France (FR), India (IN), Italy (IT), Japan (JP), Mexico (MX), United Kingdom (UK), USA (US)
* Use your Amazon **Associate ID** for making money
* Features **collections**. You can define and mangage sets of products as a collection an show them on a page with only one BBCode tag or just the latest added product in your sidebar
* Backend translation: supported so far are English, German, Spanish and Serbian. Contact me to help translate.
* Supports product **preview layers** (for US, UK and DE so far)
* New with version 0.9.5: **Caching** Speeds up your blog when adding many products to your posts!
* New with version 0.9.6: Parsing [asa] tags in user comments
* Since version 0.9.6 compatible with Amazons Product Advertising API changes by August 15, 2009 which require all requests to be authenticated using request signatures
* Version 0.9.7 supports customer reviews again!
* Version 0.9.11 brings AJAX mode (optional) for faster page load
* Error handling options
* Test section
* Support WP capabilities to manage the permissions to subpages
* Version 1.3 intoduces ASIN prefetching which significantly increases the speed of the side-loading process.

Features of the Pro Version:

* [Supports other Shops besides Amazon](https://docs.getasa2.com/shops.html)
* [Create Amazon prodcuts without PA API](https://docs.getasa2.com/create_amazon_product_without_api.html)
* [Managed Templates](https://docs.getasa2.commanaged_templates.html)
* [Customizable Templates](http://www.asa2-demo.de/templates/) (without programming skills)
* [Product Picker](https://www.youtube.com/watch?v=Bi_KAqCqgks) (Editor button)
* [Single Image Feature](https://getasa2.com/single-image/) Select an image from all available Amazon product images
* [Product Repository](https://docs.getasa2.com/repo.html) (speed up your site!)
* Parallel use of all Amazon stores
* [Manage multiple Associate IDs in sets](https://docs.getasa2.comsetup.html#associate-id-sets)
* [Powerful template syntax](https://docs.getasa2.comtemplate_syntax.html) (Conditions...)
* [Internationalization](https://docs.getasa2.cominternationalization.html) (Geolocation)
* [Bestseller lists](https://docs.getasa2.comfeeds.html)
* [Email notifications](https://docs.getasa2.comnotifications.html)
* [Translated Templates](https://docs.getasa2.comtemplates_translation.html)
* [Multiple caches](https://docs.getasa2.comcaching.html)
* [Cronjobs](https://docs.getasa2.comcronjobs.html)
* HTTPS ready
* SEO ready

ASA2 Pro is **backwards compatible** and comes with a [Migration Wizard](https://docs.getasa2.commigration_wizard.html).

Credits:

Thanks for translations

Serbian: Ogi Djuraskovic (http://firstsiteguide.com/)
Spanish: Andrew Kurtis (http://www.webhostinghub.com/)
Russian: Ivanka
French: Marie-Aude (http://www.lumieredelune.com/)

Help me translate ASA in your language. [Get in contact](https://www.wp-amazon-plugin.com/contact/)

== Installation ==

Just unpack the `amazonsimpleadmin` folder into your plugins directory and activate it on your wordpress plugins page.
Then you will have the option `Affiliate Super Assistent (ASA1)` on your wordpress options page.

== Configuration ==

Go to the new option page `Affiliate Super Assistent (ASA1)`. On the `Setup` panel you can set your Amazon Access Key ID and Secret Access Key.

Here you can find a detailed documentation:

[www.wp-amazon-plugin.com/guide/](https://www.wp-amazon-plugin.com/guide/)

== Change Log ==

= 1.5.4 =
* Fixed: Security issue with option "Parse comments". Now only [asa] shortcodes will be parsed in comments

= 1.5.3 =
* Fixed: PHP issue that could occur when collecting ASINs for cache
* Fixed: Issue on WP widgets admin page when collection feature was not enabled

= 1.5.2 =
* Fixed: CSRF issues. Added nonces to forms on options, cache and collections page where they were missing

= 1.5.1 =
* Fixed: Removed product preview links option as it was no longer supported

= 1.5.0 =
* Added: Support for Amazon PA API Belgium, Egypt and Saudi Arabia
* Added: WordPress 6.1 compatibility

= 1.4.20 =
* Fixed: Another display problem of the widget

= 1.4.19 =
* Fixed: Display problem of the widget

= 1.4.18 =
* Changed: Option "Activate debugging" has been removed
* Fixed: New collection items could not be saved

= 1.4.17 =
* Fixed: Code improvements

= 1.4.16 =
* Changed: Plugin name to "Affiliate Simple Assistent"

= 1.4.15 =
* Fixed: PHP 8 issues

= 1.4.14 =
* Added: ASA2 feature list items

= 1.4.13 =
* Added: Support for Amazon Sweden
* Added: Support for Amazon Poland
* Added: Compatibility with WordPress 5.7

= 1.4.12 =
* Added: Amazon PA API support for The Netherlands, Singapore, Turkey and United Arab Emirates

= 1.4.11 =
* Changed: Default version of PA API set to 5

= 1.4.10 =
* Fixed: Duplicate library files were removed and a namespace problem was fixed

= 1.4.9 =
* Added: New templates with German translation: "default_de", flat_box_horizontal_de", "flat_boxvertical_de", "book_de"

= 1.4.8 =
* Added: Support for shortcodes inside templates
* Added: Shortcode options "text1" and "text2" which can be used as custom template placeholders {$text1} and {$text2}
* Fixed PHP Warning: Invalid argument supplied for foreach() in lib/Asa/Prefetcher.php on line 200
* Fixed: Uncaught AsaZend_Cache_Exception: Invalid id or tag in lib/AsaZend/Cache.php:209

= 1.4.7 =
* Fixed: Placeholder {$Author} did not work correctly with PA API 5.0
* Fixed: Placeholder {$Language} did not work correctly with PA API 5.0
* Fixed: Empty shortcodes produced fatal errors when cache was used

= 1.4.6 =
* Fixed: Minor issue regarding the last update

= 1.4.5 =
* Added: PA API 5.0 support for book placeholders {$Author}, {$NumberOfPages}, {$ISBN}, {$Edition}
* Added: Button in the setup form to reset API credentials
* Fixed: PHP issues with PaApi5 class that could lead to errors on some installations

= 1.4.4 =
Fixed: Placeholder {$AmazonPrice} didn't contain decimal separator

= 1.4.3 =
Changed: Debug mode currently not supported with PA API 5.0
Fixed: Improvements to avoid class conflicts with other plugins that also use PA API 5.0

= 1.4.2 =
Added: Minor improvements for Amazon PA API 5.0 support

= 1.4.1 =
Added: Minor improvements for Amazon PA API 5.0 support

= 1.4.0 =
Added: Support for Amazon PA API 5.0

= 1.3.4 =
Fixed: An error in the processing of the XML response from the API in case of an error

= 1.3.3 =
* Changed: PHP 7.2 optimizations

= 1.3.2 =
* Added: Support for Amazon API Australia
* Fixed: Issue with connecting to the CN store

= 1.3.1 =
* Changed: Added CSS max width property to button of template "flat_box_horizontal"

= 1.3.0 =
* Added: ASIN Prefetcher: Tries to collect all ASINs on one page and thereby send as few requests as possible to the Amazon API by using batch lookups. This significantly increases the speed of the side-loading process.
* Added: Capabilities: asa1_edit_setup, asa1_edit_options, asa1_edit_collections, asa1_delete_collections, asa1_edit_cache
* Added: An option to enable CSS3 loading animations for AJAX mode
* Changed: The debug output is now displayed in a div container instead of a textarea, which could cause problems when saving the options.
* Changed: Cache: By default, a random value of seconds gets added to the cache lifetime (between 0 and 10% of the lifetime value) so that cached products never lose their validity at the same time.
* Fixed: The rare PHP error: "Call to a member function itemLookup() on null in AsaCore.php:2700"
* Fixed: PHP notices "Trying to get property of non-object" in AsaCore.php on line 2452 and 2453
* Fixed: AJAX mode in multisite installations did not use the options of the subsite

= 1.2.0 =
* Added: New template "flat_box_horizontal". It can be set in the options as a new default template. Usage: [asa tpl="flat_box_horizontal"]ASIN[/asa]
* Added: New template "flat_box_vertical". Usage: [asa tpl="flat_box_vertical"]ASIN[/asa]
* Added: HTTPS support
* Changed: Improved collection rendering ([Read more](https://www.wp-amazon-plugin.com/asa1-shortcode-asa_collection/))
* Fixed: Tested with PHP 7.2, 7.1, 7.0
* Fixed: Minor fixes

= 1.1.1 =
* Fixed: A bug which caused an issue with the setup form

= 1.1.0 =
* Added: Option to connect Amazon API via HTTPS
* Added: Collection shortcode generator in collection admin section
* Changed: Minor improvements
* Fixed: PHP7 compatibility fixes

= 1.0.2 =
* Changed: Added "nofollow" attribute to all links in the included default templates

= 1.0.1 =
* Changed: Removed dependence on PHP function set_include_path
* Fixed: Multiple implementations of the same product on one page did only show the first one in AJAX mode
* Fixed: Improved compatibility with WooZone (sections Setup and Options were hidden)

= 1.0.0 =
* Added: Announcement of ASA2 ([Read more](https://getasa2.com/))
* Improved user interface (mainly collections admin)
* Fixed: PHP deprecated message about iconv_set_encoding()

= 0.9.19 =
* New: Placeholder {$Features}

= 0.9.18 =
* Added support for: Brazil, Mexico
* Placeholder {$AmazonPrice} now respects the sale price correctly
* Minor fixes and improvements

= 0.9.17 =
* Tweak: Main price handling improved
* Improvement: Security updates
* Improvement: Prepared for WP 4.3

= 0.9.16 =
* Bugfix: Due to changes in the structure of the API's XML result, the second product image was used.
* Added: French translation

= 0.9.15 =
* New feature: Export / import collections
* Added language file: Serbian. Thanks to Ogi Djuraskovic (http://firstsiteguide.com/)
* Added update notice for template backup (Please also read: https://www.wp-amazon-plugin.com/2015/13280/keeping-your-custom-templates-update-safe/)
* New placeholder: {$OffersMainPriceAmount} Always keeps the main Amazon price. You can set the text it should show if the price is empty.
* New placeholder: {$OffersMainPriceCurrencyCode} The main price currency code.
* New placeholder: {$OffersMainPriceFormattedPrice} Always keeps the main Amazon formatted price (with currency code). You can set the text it should show if the price is empty.

= 0.9.14 =
* New feature: Test section. Test a ASIN with your template or check if the ratings can be retrieved.
* Improved error handling options
* New placeholder: {$Class}. You can use it like {$Comment} e.g. to put a custom CSS class in it. [asa demo, class="my_css_class"]B00AKHKTVI[/asa]
* Improvement: On some servers the rating images could not be loaded. This has been improved.

= 0.9.13 =
* New feature: Added support for indian country store. You can now advertise with Amazon India.
* New feature: ASA is ready for translation. It supports english and german so far.
* Layout adjustments for the new WordPress 3.8 backend.
* Minor bug fixes

= 0.9.12 =
* New feature: New cache option: Do not use cache when logged in as admin
* New feature: Now you can place your template files in your theme directory in a subdirectory called "wp-content/themes/[your-theme]/asa". Supported file extensions are ".htm" and ".html". This is optional, the subdirectory "tpl" of the plugin directory will work as usual.
* New placeholder: {$TrackingId}
* New placeholder: {$AmazonShopURL}
* New placeholder: {$ProductReviewsURL}
* Improvement: Added collection name filtering
* Improvement: Collections are tested successfully in WordPress Multisite environment
* Bugfix: asa / asa_collection shortcode regex fix
* Bugfix: Collection browse select will keep the selected item
* Fix: Removed PHP notices
* Fix: PHP Notice: has_cap was called with an argument that is deprecated since version 2.0! on menu creation

= 0.9.11.2 =
* Bugfix: Ajax mode did not work for not logged-in users
* Bugfix: Removed double usage of Amazon price formatting function
* Added: Placeholders {$Prime}, {$PrimePic}

= 0.9.11.1 =
* Bugfix: Functions asa_item und asa_get_item resulted in an error when used with custom templates (file_get_contents warning)
* Bugfix: Placeholder {$PercentageSaved} should show 0 if nothing is saved instead of it being blank
* Added: Custom CSS class for Ajax container called "asa_async_container_" + template name. So you can add a custom Ajax loading image per template.

= 0.9.11 =
* Added: AJAX mode for asynchronous loading to improve page load speed
* Added: HTML tag support for $Comment placeholder
* Added: Shortcode handler for better integration with other plugins / widgets
* Added: Options panel
* Added: FAQ panel
* Fixed: Behavious of {$AmazonPrice}
* Fixed: On adding items to collections, the collection name stays selected

= 0.9.10.2 =
* Added: Option for using shorter Amazon links (as they were in previous versions)
* Added: Debugging mode (you can activate it on the setup panel)
* Added: Better error handling/reporting on connection/setup errors
* Fix: Removing whitespace on updating Amazon credentials (trim)
* Fix: Minor bugfixes

= 0.9.10.1 =
* Fix: Fixed error: Class 'Asa' not found in .../AsaCore.php on line 384 

= 0.9.10 =
* Fix: Update to API version 2011-08-01. The associate tag is now mandatory!
* New: Support of Amazon Italy, Spain and China
* New: With this version comes a backup mechanism for your custom templates. Further updates should keep your custom files.
* Fix: Fixed a bug in rendering placeholder $AmountSaved->FormattedPrice

= 0.9.9.3 =
* hotfix: Solves a problem when accessing the Amazon customer rating page for parsing the results
          I will check this in detail and provide some more options on this in the admin panel later on

= 0.9.9.2 =
* Fixed: issue with currency and price not displaying properly
* Added: Widget for displaying collections in sidebar
* New placeholder: {$LowestNewOfferFormattedPrice}
* New placeholder: {$LowestUsedOfferFormattedPrice}
* New placeholder: {$AmazonPriceFormatted}
* New placeholder: {$ListPriceFormatted}
* Keeps user templates after update and does not overwrite custom templates. 
  The built-in templates are now place in directory "tpl/built-in". 
  Do not change these build-in template as I could change them for future updates.
  For your own template copy a build-in into the directory "tpl" and change it there.
  If templates with same names exist, always the one in "tpl" will be used.
* New feature: Personal comment [asa comment="Hello world"]ASIN[/asa] The comment can be displayed with placeholder {$Comment}
* Added: New build in template: mp3
* Improved default / built-in templates: default, book, dvd, mp3
* Added: When browsing a collection in the backend, the price of an item is listed
* New plugin homepage: https://www.wp-amazon-plugin.com/

= 0.9.9.1 =
* Added: Support for the excerpt
* Added: 2 new parameters for [asa_collection]: "items" and "type". 
         "items=2" for example lets you limit the collection items to be displayed.
         type supports "random", so "type=random" shows randomly selected collection items. 
         By default the latest added item will be displayed first.
         You may combine the two parameters, separated by comma: [asa_collection items=2, type=random]Products01[/asa_collection]
         If you want to use a template: [asa_collection book, items=2, type=random]Products01[/asa_collection]
* Added: Support for placeholders: Languages, Subtitles (for DVDs), Model, 
         ListPrice->FormattedPrice, ListPrice->CurrencyCode, AmountSaved->FormattedPrice, AmountSaved->CurrencyCode
         
= 0.9.8 =
* Bugfixes
          
= 0.9.7 =
* Fixed: Customer reviews. Due to amazon api changes in November 2010 the customer reviews did not work any more.
         The api now provides a url to an iframe where the reviews are generated for 24 hours. I implemented a way
         to grab the 5-stars image and the total reviews so the old placeholders are still supported. I hope this 
         way works. If you have any problems please let me know.
* Added: News placeholders: LowestOfferFormattedPrice, RatingStarsSrc (the url to the image file) 

= 0.9.6 =
* Added: Compatible with request authentification
* Added: Parsing [asa] tags in user comments (optional) (feature request from Sebastian, thanks)
* Bugfix: Browsing a collection list which contained an item without an image caused an error (bug report by Lingus, thanks)
* Bugfix: Product with multiple artists caused an error (bug report by Aaron, thanks)

= 0.9.5 =
* Added: Caching
* Added new placeholders: ProductDescription, AmazonDescription, Artist
* Bugfix: Template parsing bug fixed

= 0.9.4.1 = 
* Fixed: Installation of collections (form for installation was not displayed)
* Added function asa_item for use in PHP code everywhere eg sidebar: <?php asa_item('B000TKHBDK'); ?>
* (User request) Added placeholder "CustomRating" which can be used like this [asa custom_rating=3.5]0316015849[/asa] 

= 0.9.4 = 
* Adapated to wordpress version 2.5
* Added some new DVD related placehoders (Director, Actors, RunningTime, Format, Studio)
* Added an exemplary dvd template

= 0.9.3.1 = 
* Bugfix: Fixed error in deleting collection items

= 0.9.3 = 
* Bugfixes
* Added new placeholders: AverageRating, TotalReviews, RatingStars

= 0.9.2 = 
* Added new placeholders: ASIN, ISBN, EAN, NumberOfPages, ReleaseDate, Binding, Author, Creator, Edition
* Added template book.htm
* Fixed: Delete collection items with qutations in title

= 0.9.1 = 
* Added a PHP Version checker. If plugin gets activated under PHP 4 it will be reversed.

== Info ==

If you find any bugs please send me a mail (support@NOSPAM_wp-amazon-plugin.com, remove the NOSPAM_) or use the comments on the [plugin's homepage](https://www.wp-amazon-plugin.com/ ). Please also contact me for feature requests and ideas how to improve this plugin. Any other reactions are welcome too of course.

== Frequently Asked Questions ==

= For category music CDs what is the placeholders for artist? = 
{$Artist}

= Are there any special placeholders for MP3 downloads? = 
There is a special built-in template for mp3 files. Use [asa mp3]ASIN[/asa]

= The plugin does not show the price for Kindle ebooks correctly. = 
Unfortunately the Amazon webservice does not support Kindle prices at the moment. See https://forums.aws.amazon.com/thread.jspa?messageID=208072

= I get the PHP-Error: Warning: domdocument::domdocument() expects at least 1 parameter, 0 given in \wp-path\plugins\amazonsimpleadmin\lib\Zend\Service\Amazon.php on line 129 = 
There are two very similarly named PHP extensions, dom and dom_xml - the dom extension is built into php5 and loading the dom_xml extension (designed for php4) will override the default extension.
I suspect that loading php_domxml.dll (on Windows) in your php.ini is the cause of this problem. Try to disable it and see if that helps.

== Screenshots ==

1. Easy to use [asa] shortcode, in this example with template flat_box_vertical
2. Easy to use [asa] shortcode, in this example with template flat_box_horizontal
3. Sidebar Widget
4. The Sidebar Widget is very easy to setup
5. Setup screen. Here you can setup your connection with the Amazon Product Advertising API
6. ASA comes with many options
7. With ASA you can manage Amazon products in collections ...
8. ... and display the whole collection with just one shortcode [asa_collection]
9. You can view the API error messages in the log section (if activated in the options)
10. Get error reports via email
11. Go Pro with ASA2 (https://getasa2.com/)
12. Delegable capabilities

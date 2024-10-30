=== EU Cookie Law Compliance ===
Contributors: zafrira
Donate link: https://zafrira.net/
Tags: cookie, cookie compliance, google analytics, EU regulations, cookie law, cookies, eu cookie law, eu privacy directive, privacy directive, privacy, eu cookie directive
Requires at least: 3.0.0
Tested up to: 3.4.2
Stable tag: 1.1.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugin to help you to make your wordpress installation to comply to the new cookie regulations in the EU.

== Description ==

The Cookie Compliance plugin to help you to make your wordpress installation to comply to the new cookie regulations in the EU.

You will be able to set a welcome message that will appear in a popup for your visitors. In this popup your visitors will be asked for the approval or denial to store cookies on the computer.
The choice of the visitor will be stored. You will be able to set the scripts that should be executed based on the approval or denial.

= Integrated Google Analytics =
In normal cases when the visitor chooses to not allow cookies, you cannot run the Google Analytics scripts.
Google Analytics can be enabled in the plugin settings. When enabled the analytics tracking will work in all cases. When the visitor does not allow you to store cookies the plugin will make sure the hits of the visitor session will still be stored in Google Analytics.

Since version 1.1.2 the cookie-less Google Analytics version also supports event and campaign tracking.
See the FAQ for the commands to use.

= Works with translation plugins =
This plugin does work in combination with translation plugins. When the translation plugin is enabled, the cookie compliance plugin will let you set the pop-up message in the languages that are enabled in your translation plugin envoirement.
The below translation plugins are currently supported:

*	[Q-Translate](http://wordpress.org/extend/plugins/qtranslate/) `version 2.5.32`
*	[Polylang](http://wordpress.org/extend/plugins/polylang/) `version 0.9.4`
*	[WPML](http://wpml.org/) `version 2.6.0`

Suggetions for other language plugins are welcome, please let us know through the [cookie compliance support forum](http://wordpress.org/support/topic/plugin-cookie-compliance-polylang-compatible).

= Works with wordpress caching =
The plugin is build like that so it does function with server side caching enabled, no matter if your visitor chooses to accept or deny the cookies. 
We tested it with [WP Super Cache](http://wordpress.org/extend/plugins/wp-super-cache/) and Varnish. Having troubles with plugin in combination with a cacher? Please let us know!

= About the plugin =
This plugin is developed by Zafrira. Soon a matching plugin for Magento will be released that will make use of the same cookies.
More information as it becomes available will be posted on the [cookie compliance](https://zafrira.net/en/tools/wordpress-plugins/cookie-compliance/) page on our website.

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the folder `cookie-compliance` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the options in the `Cookie` menu that appears in your admin backend

== Frequently Asked Questions ==

= What does it do? =

It will help you make your website to comply to the new EU regulations that make you to ask approval for storing cookies.

= Does it automatically disable cookie usage in Wordpress? =

No, it is just a tool to help you out. You should use the settings pages in the backend to set up javascript commands and functions to act when cookies are enabled or disabled.
The [Zafrira](https://zafrira.net/en/) support team can help you develop custom made scripts. Please contact us for the appropiate rates.

= Can i see what the visitors click? =

Yes, a table is generated that will keep track of the clicks.
This is also meant as a prove of what your visitor clicked in case of a discussion.

= Google Analytics campaign tracking, how to? =

When tracking campains using querystring values in your website URL's (such as utma, utmb and utmc) the plugin will automatically parse through those values.
If you are missing functionallity that you would normally use, please open an issue on the [cookie compliance support forum](http://wordpress.org/support/topic/plugin-cookie-compliance-polylang-compatible).

= Google Analytics event tracking, how to? =

To track an event, you would normally use the google analytics push function.
If you replace this with the compliance.gapush function it will automatically use the activate Google Analytics communication channel.
For example: 
`compliance.gapush(['_trackEvent', 'MyEvent', 'What Happened', 'What was chosen', 1]);`

= What is the user set that cookies are not allowed in the browser? =

In this case the question will not be asked. Based on the possibillity to store cookies the message will be shown.
When not able to store cookies, there is no use in asking it.

= What is the EU Cookie Law? =

Essentially the EU Cookie Law is the EU e-Privacy Directive that is set to come into action in 2012 and what it means is that you have to get your visitors informed consent before placing a cookie on their machine. 
Here is a link to the [ICO website](http://www.ico.gov.uk/for_organisations/privacy_and_electronic_communications/the_guide/cookies.aspx) detailing the law.
More information can also be found on the [Wikipedia](http://en.wikipedia.org/wiki/Directive_on_Privacy_and_Electronic_Communications#Cookies).

The original text of the law can be found on the [EUR-Lex](http://eur-lex.europa.eu/LexUriServ/LexUriServ.do?uri=CELEX:32002L0058:EN:NOT) (see article 5).

= Is it just cookies? =
No, also `Flash cookies` and `HTML5 local storage`.

= Do i need to have this for all cookies that I store? =
No, essential cookies that are used to for example store a login session are essential. Those cookies are not used for tracking your customer but just to keep the login session alive. In this case the cookie is allowed without asking permission first.
When you store cookies to track your visitor, your visitor should allow you this first. Basicly you are able to determine if your visitor is returning from a previous session with such cookie.

= Are cookies from advertisements on my website also part of the new law =
Yes, everything you provide from your website is part of the EU law.

= My server is outside the EU, for example in the U.S., do i still need to ask permission? =
Yes, the law basicly aimes on the location of the visitor. So the location of the hosting does not matter.

= When i activate the plugin and the user makes a choice, it will set a cookie. Why? =
To not annoy your visitor with constant pop-ups 2 types of cookies can be made by the plugin:

*	cookie_compliance: this cookie contains a 0 (zero) or 1 (one). Zero stands for denied and One for accepted.
*	session cookie (used for google analytics when deny option is chosen)

This type of cookie is needed to let your site function, a website where on every page you will get the same popup asking the same question is not functioning well.
That's why we believe this is a ok cookie by law.

= Known bugs in this plugin, upcoming updates: =

*	When plugin is enabled in combination with Polylang pages will return with a 404 not found `fixed in version 1.1.0`
*	Plugin does not function in combination with the WPML plugin `fixed in version 1.1.1`
*	Event and campaign tracking is not possible when cookies are enabled `fixed in version 1.1.2`
*	Sometimes every pageview counts as a new visitor `fixed in version 1.1.2`
*	Visitor locations in cookie-less Google Analytics show the location of the server `fixed in version 1.1.3`

== Changelog ==

= 1.1.4 =
* removed the usage of a session cookie to track with Google Analytics, replaced it with a session key

= 1.1.3 = 
* localised Google Analytics scripts
* updated Google Analytics communications to version 5.3.8
* added the option to choose if the Google Analytics script is executed from the header or the footer (by default the footer)
* fixed cookie-less Google Analytics to show a different location then where the user is (like with the normal GA)
* gave Google Analytics a seperate settings page
* added on the Pop-up Settings page an option to set your own CSS rules for the bottom bar, when nothing is set yet it will load the default CSS rules
* minified cookie-complience.js file

= 1.1.2 = 
* improved communications with Google Analytics when cookie usage is disabled
* added Google Analytics support for event tracking
* added Google Analytics support for tracking campaigns 

= 1.1.1 = 
* improved code for checking on the Polylang language settings, thank you [Chouby](http://wordpress.org/support/profile/chouby) for the input on this!
* added support for the language plugin WPML, thanks to the [WPML.org](http://wpml.org) team for the input
* changed the cookie image in the backend to a smaller and more clear one
* replaced the Google Analytics scripts that are meant for cookieless usage, this should fix the referrer problem
* fixed bug in the display of the bottom bar in Internet Explorer
* fixed bug in loading config in Internet Explorer
* re-aranged settings in the backend for a more clear overview
* gave the bottom bar it's own message box in the backend

= 1.1.0 =
* new release version 1.1
* automatically clearing cache of WP Super Cache after saving options (1.0.5)
* better handling of google analytics (1.0.6)
* new display method, bar on bottom in stead of pop-up
* new display method, able to display continues question bar when visitor did not agree with the cookies
* added polylang compatibillity (1.0.8)
* no more window.reload after accepting or denial on question to visitor (1.0.10)
* added direct settings link in plugin overview (1.0.8)
* options to tweak the cookie handling a bit, some EU countries apply the regulations a bit different

= 1.0.11 =
* fixed bug in version 1.0.8, pages return with an error 404 when Polylang is enabled.

= 1.0.10 =
* fixed bug caused by window.reload after visitor chooses deny or accept, was causing page to reload on wrong url in some cases

= 1.0.9 =
* added option to also not save a cookie at all, only when accepted. The deny choice is not saved.
* fixed display of the bottom bar so it will be added rather then putted over the content on the bottom of the page

= 1.0.8 =
* made it possible to set 'deny' by default, but still leave option open (through bottom bar) to ask for approval
* added support for 'Polylang' language plugin
* added settings link in the plugin admin area

= 1.0.7 =
* added option to switch from a pop-up to a bar on the bottom
* added option to keep bugging your visitor when he choose deny
* fixed bug in the javascript that came with version 1.0.6

= 1.0.6 =
* added function to choose the deny option by default for all visitors (will act like the users pressed deny, but does not ask the user)
* fixed handling of footer scripts when Google Analytics is disabled

= 1.0.5 =
* clear cache contents (wp-super-cache) on saving plugin options
* bugfix: improved stripslashes on button display of the pop-up

= 1.0.4 =
* added http referrer to no-cookie google analytics
* added accepted language to no-cookie google analytics

= 1.0.3 =
* fixed mistake in plugin settings version 1.0.2

= 1.0.2 =
* fixed printed google analytics code layout
* expanded explanation of plugin settings in wp-admin
* renamed menu items to more clear names
* use of more clear titles in plugin options in wp-admin

= 1.0.1 =
* did some corrections in the descriptions and tagging

= 1.0.0 =
* this is the first public release

== Upgrade Notice == 

no upgrade notice to show

== Screenshots ==

1. An example of the pop-up the visitor will get to see
4. As of release 1.0.7 it is possible to choose to display a bar at the bottom of the page in stead of a pop-up
2. A screenshot of the script settings page
3. A screenshot of the pop-up settings page, where you can also name the buttons

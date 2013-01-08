=== wpLike2Get ===
Contributors: drumba
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=BCVM7FZ6ZCM2A
Tags: Pay with tweet, Pay per tweet, like 2 get, Facebook, Twitter, Google+, Google Plus, Markus Drubba, drumba, social network, social media
Requires at least: 3.1
Tested up to: 3.5
Stable tag: "1.2.3"
License: GPLv3

Improve your social media spreading by letting your users pay with a like, a tweet or +1. wpLike2Get allows you to do this easily.

== Description ==

WordPress out of the box is smart enough to give you the ability to host downloads on your personal blog/website. If you deliver premium content for free to your users maybe you want to get a little bit of love from your consumers. With wpLike2Get you can protect your download or content until the user like, tweet or +1 your content.

= Decide which type of social network =
You decide between Facebooks like and send button, Twitters tweet or follow button and Google +1 button. You can decide for only one button, two, or all together.

= Track with Google Analytics =
You have the ability to track the button-clicks with Google Analytics.

= Usage =
The social media buttons are loaded when the shortcode was detected. [l2g id="999"]. You can optional decide for a name of the downloadlink and you can select different social networks when you activated all on the wpLike2Get settings page. A complete shortcode for downloads looks like that: [l2g name="Download this awesome file" id="999" facebook="true" twitter="true" gplusone="false"]
 a complete shortcode for hiding content looks like that: [l2g facebook="true" twitter="true" gplusone="false"]here is your content to hide until user likes the content[/l2g]

 = Shortcode options =
* id => id of the attachment
* name => some text
* twitter => true|false
* facebook => true|false
* gplusone => true|false
* single => true|false *if true show only on singular views (since v1.2.2)*
* home_text => some text *only displayed when single is true (since v1.2.2)*

== Installation ==

1. Upload the `wplike2get` folder to the `/wp-content/plugins/` directory
1. Activate the wpLike2Get plugin through the 'Plugins' menu in WordPress
1. Configure the plugin by going to the `wpLike2Get` menu that appears under `settings` in your admin menu

== Screenshots ==

1. wpLike2Get settings excerpt
2. The wpLike2Get option in the media-uploader
3. Example layout of all the social media buttons
4. Example layout of the download link after facebook like

== Frequently Asked Questions ==

= Can I contact you for support questions? =
Yes, but please use the wordpress.org forums. In the future I give no support via email anymore.

= Can I using it on old files? =
Yes, you can use it also on files you uploaded before installing the plugin

= Can I hide also content, and not only downloads? =
Yes, since version 1.2.0 you can also hide content before user like your post/page. Use following shortcode: [l2g]here is your content to hide until user likes the content[/l2g]

= How can I hide a social network on only one specific post/page? =
Use the following options in the shortcode: facebook="false", twitter="false" or gplusone="false"

== Changelog ==

= 1.2.3 | 08.01.2013 =
* full compatibility for wordpress 3.5
* including french language file (thanks to @oweb (http://www.office-web.net) for the translation)

= 1.2.2 | 19.10.2012 =
* feature: adding an option to show buttons only on singular views (thanks @tiagosgd for feature-request)
* feature: adding option to set facebook language
* bugfix: some default options not observed
* bugfix: show love to developer is not working
* add a amazon wishlist widget to options page sidebar

= 1.2.1 | 10.09.2012 =
* fixing an error with G+ Plugin. User can't see hidden content when using G+.
* fixing array_merge warning on activating plugin
* integrating PressTrends Plugin API

= 1.2.0 | 12.03.2012 =
* you can now also hiding content before user like the post/page (thanks to Ovidiu for the feature suggestion)
* using this feature you can use the [l2g] shortcode: [l2g]here is your content to hide until user likes the content[/l2g]
* this feature is not implemented like the download link (requesting the link via ajax), the content is only hidden in the html output

= 1.1.0 | 24.02.2012 =
* refactoring plugin code for a more wordpress standard code base
* adding standard css styles to the button, so after activating plugin, you can use it without adding any css styles

= 1.0 | 10.02.2012 =
* initial release

== Upgrade Notice ==

Just do a normal upgrade.
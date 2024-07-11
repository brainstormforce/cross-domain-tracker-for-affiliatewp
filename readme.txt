=== Cross Domain Tracker for AffiliateWP ===
Contributors: pratikchaskar
Donate link: https://www.paypal.me/BrainstormForce
Tags: AffiliateWp, Cross domain tracking
Requires at least: 4.4
Tested up to: 6.6
Stable tag: 1.0.5
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Track referrals from different domains.

== Description ==
The official <a href="https://affiliatewp.com/add-ons/official-free/external-referral-links/?ref=952">AffiliateWP – External Referral Links</a> add-on is excellent for tracking referrals from different domains. But it has some limitations.

1. It does not send visits data to AffiliateWP — which means, your affiliates do not get to see any information of the visits data and other important statistics accurately.
2. It does not have Credit Last Referrer option — which means, you can only have “First Referrer” cookie policy.
3. It does not have campaign visits tracking capability — which means, your affiliates do not get to see visits data with respect to campaigns.

So we developed this plugin which mainly fixes these three problems. Here is how it works:

1. Install this plugin on main website where you have AffiliateWP plugin installed and where conversions take place (SiteA.com)
2. Install this plugin on marketing website as well, where your affiliates send traffic (SiteB.com)
3. Link and authenticate these two instances (SiteA.com and SiteB.com) using Public Key & Token.
4. Install & activate the <a href="https://affiliatewp.com/add-ons/pro/rest-api-extended">REST API Extended</a> plugin on parent website (SiteA.com). And enable <a href="https://cl.ly/fbdd25/Image%202019-04-23%20at%204.38.16%20PM.png">Create Visit Endpoints</a> option from AffiliateWP -> Settings -> REST API.

And you are all set.

Your affiliates can now start sending traffic to your marketing website (SiteB.com) and still see all the statistics and reports in their AffiliateWP dashboard.

== Frequently Asked Questions ==

= Are there seperate plugins for AffiliateWP and marketing site? =

No, you just need to select the plugin type as parent/child inside the plugin settings.


== Installation ==

1. Upload `cross-domain-tracker-for-affiliate.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

=== Screenshots ===


== Changelog ==

= Version 1.0.5 - Thursday, 11th July 2024 =
* Improvement: Compatibility with WordPress 6.6.

= Version 1.0.4 - Wednesday, 14th July 2021 =
* Improvement: Updated the type of input field for authentication options.
* Improvement: Compatibility with WordPress 5.8

= Version 1.0.3 - Wednesday, 10th March 2021 =
* Improvement: Compatibility with WooCommerce 5.1.0
* Improvement: Compatibility with WordPress 5.7

= Version 1.0.2 - Monday, 24th Aug 2020 =
* Improvement: Compatibility with WordPress 5.5.

= Version 1.0.1 - Monday, 30th May 2019 =
* Fix: Authentication with new AffiliateWP setup where visits are Zero.

= Version 1.0.0 - Monday, 20th May 2019 =
* Initial Release

== Upgrade Notice ==

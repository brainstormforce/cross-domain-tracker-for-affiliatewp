## Cross Domain Tracker for AffiliateWP

The official AffiliateWP – External Referral Links add-on is excellent for tracking referrals from different domains. But it has some limitations.

1. It does not send visits data to AffiliateWP — which means, your affiliates do not get to see any information of the visits data and other important statistics accurately.
2. It does not have Credit Last Referrer option — which means, you can only have “First Referrer” cookie policy.
3. It does not have campaign visits tracking capability — which means, your affiliates do not get to see visits data with respect to campaigns.

So we developed this plugin which mainly fixes these three problems. Here is how it works:


1. Install this plugin on main website where you have AffiliateWP plugin installed and where conversions take place (SiteA.com)
2. Install this plugin on marketing website as well, where your affiliates send traffic (SiteB.com)
3. Link and authenticate these two instances (SiteA.com and SiteB.com) using Public Key & Token.
4. Install & activate the <a href="https://affiliatewp.com/add-ons/pro/rest-api-extended">REST API Extended<a> plugin on parent website (SiteA.com). And enable <a href="https://cl.ly/fbdd25/Image%202019-04-23%20at%204.38.16%20PM.png">Create Visit Endpoints</a> option from AffiliateWP -> Settings -> REST API.

And you are all set. 

Your affiliates can now start sending traffic to your marketing website (SiteB.com) and still see all the statistics and reports in their AffiliateWP dashboard.

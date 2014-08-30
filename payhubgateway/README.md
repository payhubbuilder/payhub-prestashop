
*** PayHub Gateway OpenCart Payment Extension ***
Provided by: PayHub, Inc.  (www.payhub.com)
PayHub Support: wecare@payhub.com or 844-205-4332 (M-F, 8-5 PST)

Created by: Lon Sun
Last Updated By: Lon Sun
Last Updated On: August 29, 2014

Description: 
This module allows you to accept credit card payments in PrestaShop using PayHub Gateway.  It currently supports English and USD currency only.  A merchant account with PayHub is required.  Contact us to setup an account at www.payhub.com or by calling 1-866-286-1300.

Supported PrestaShop Version(s): 1.6.0.x

PLEASE SEE THE LICENSE FILE INCLUDED WITH THIS EXTENSION.

*** Release Notes ***
v1.0 - August 29, 2014
  -Initial Release

*** How to Install ***

Ensure that you have a working PrestaShop installation (e.g. you can get to your site in a browser and log into the admin section).

Download the module from http://developer.payhub.com/thirdparty or from www.prestashop.com.

Unzip the module and you should now have a "payhubgateway" directory.

Manually upload the entire "payhubgateway" directory to your PrestaShop installation (using FTP, SCP, etc).  Put it into the <prestashop_base>/modules/ directory.

Now log into your PrestaShop admin section and click on Modules in the navigation pane on the left.

In the Modules List, search for "payhub" and you should see the module appear in the results.

Click on the Install option for the PayHub Gateway module.

You should automatically be taken to the configuration page for the module.

Enter your PayHub credentials and set the mode to "demo".  Also choose the card types you accept.  Save your changes.

Now test the configuration using test data found here: http://developer.payhub.com/api#api-howtotest.  Rest assured that these tests will not show up on any live accounts.

Once you are satisfied that everything is working, go back to the PayHub Gateway configuration page in PrestaShop admin.

Change the mode to "live" and save your changes.

It as advisable to test a payment one more time with a live card.  You can always log into your PayHub account and void it if you want.

*** How to Get Your PayHub Credentials ***

Log into your PayHub VirtualHub account.  Go to the Admin->3rd Party API page.  You will see your credentials there.

*** Security Considerations ***

PayHub is very serious about security.  As such, you must enable SSL in PrestaShop for the PayHub Gateway module to work.

Also note that this module requires host certficate verification.   
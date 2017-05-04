# mod-mailwizz

PAYFAST ADDON FOR MAILWIZZ 1.3.5.7

Copyright (c) 2008 PayFast (Pty) Ltd
You (being anyone who is not PayFast (Pty) Ltd) may download and use this plugin / code in your own website in conjunction with a registered and active PayFast account. If your PayFast account is terminated for any reason, you may not use this plugin / code or part thereof.
Except as expressly indicated in this licence, you may not use, copy, modify or distribute this plugin / code or part thereof in any way.

INTEGRATION:
1. Download the PayFast addon and open the zip folder to a temporary folder on your computer. Copy the payment-gateway-payfast file to the apps/common/extensions folder.
2. In the backend area of your MailWizz website, navigate to Settings>Monetization to enable Monetization.
3. Navigate to Extend>Extensions and enable Payment Gateway - Payfast.
4. Navigate to Monetization (in the main menu)>Payment gateways>PayFast.
5. For testing purposes use the following credentials:
    Merchant ID: 10000100
    Merchant Key: 46f0cd694581a
   Leave gte passphrase blank, set the mode to 'Sandbox' and status to 'Enabled'.
6. Click save changes.
7. Once you are ready to go live change the merchant ID and Key to your PayFast merchant ID and Key, if you have set a passphrase on your payfast account input the same passphrase on the PayFast configuration page, otherwise leave it blank. Set the mode to 'Live' and click save changes.
# mod-mailwizz

PAYFAST ADDON FOR MAILWIZZ 1.3.5.7

Copyright (c) 2010-2015 PayFast (Pty) Ltd

LICENSE:

This payment module is free software; you can redistribute it and/or modify it under the terms of the GNU Lesser General Public License as published by the Free Software Foundation; either version 3 of the License, or (at your option) any later version.

This payment module is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more details.

Please see http://www.opensource.org/licenses/ for a copy of the GNU Lesser General Public License.

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
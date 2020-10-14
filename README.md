### _[Signup free with 2Checkout and start selling!](https://www.2checkout.com)_

# 2Checkout and PrestaShop Module Configuration

## PrestaShop Settings:
1. Download the 2Checkout payment module from https://github.com/2Checkout/PrestaShop-2Checkout
2. After downloading the .zip archive, open it and extract the folder twocheckout, then archive it separately as a .zip file
3. Sign in to your PrestaShop admin.
4. Under Modules click Module Manager then select Upload a Module.
5. Upload the ‘twocheckout.zip’ directory and select configure
6. Enter your 2Checkout Account Number (2Checkout Seller ID).
7. Enter your Private Key (2Checkout Private Key).
8. Select one of the three Ordering Engines – Convert Plus, Inline Cart, API
9. Select No under Sandbox Mode (Unless you are testing in the 2Checkout Sandbox).
10. Click Update Settings.
_**Important note:**
a. The Seller ID is your 2Checkout Merchant Code that you can obtain by logging in to your Merchant Control Panel and navigating to Integrations → Webhooks & API.
b. To find the Buy Link Secret Word, log in to your 2Checkout Merchant Control Panel and navigate to Integrations → Webhooks & API → Secret Word . Edit your INS Secret Word to match the Buy Link Secret Word , copy the value and paste it in the PrestaShop admin.
c. The Secret key can be found in your 2Checkout Merchant Control Panel, right next to the Merchant Code. Copy and paste it in your PrestaShop admin._

## 2Checkout Settings:
1. Sign in to your 2Checkout account.
2. Navigate to Dashboard → Integrations → IPN Settings
3. Copy the IPN Url provided in your 2Checkout PrestaShop settings and set it as your IPN URL.
4. Enable 'Triggers' in the IPN section. It’s simpler to enable all the triggers. Those who are not required will simply not be used.
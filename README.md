# Plentymarkets with Nets Easy

|Module | Nets Easy Payment Module for plentymarkets
|------|----------
|Author | `Nets eCom / Web Wikinger`
|plentyshop LTS Version | `v5.0.0+`
|iO Version | `v5.0.0+`
|plugin Version | `1.0.4`
|Documentation Guide | [https://developer.nexigroup.com/nets-easy/en-EU/docs/nets-easy-for-plentymarkets/](https://developer.nexigroup.com/nexi-checkout/en-EU/docs/checkout-for-plentymarkets/)
|Github | https://github.com/Nets-eCom/Plentymarkets_easy
|License | `MIT License`

## Techsite Link
- [https://developer.nexigroup.com/nets-easy/en-EU/docs/nets-easy-for-plentymarkets/](https://developer.nexigroup.com/nexi-checkout/en-EU/docs/checkout-for-plentymarkets/)

# Nexi Checkout
## The ultimate Checkout
**Online payments. Local. Relevant.** Checkout with local support!

- **Fewer cart abandonments, optimized conversion, and increased revenue.**
- **The most essential payment methods for your webshop and your customers.**
- **No setup or monthly fees.**
- **Pay only for successful transactions.**

Our checkout and payment mix for increased revenue. Let your customers pay their way! Offer preferred payment methods in various currencies. Our checkout includes all relevant payment options:

- Secured Debit
- Visa
- Mastercard
- American Express
- Maestro
- PayPal
- Apple Pay
- Sofortüberweisung (Instant Bank Transfer)
- Direct Debit
- Invoice Payment
- Installment Payments

#### Benefit from our terms and additional advantages:
- **No setup or monthly fees.** Pay only for completed transactions.
- **Everything at your fingertips on the dashboard.** Enjoy full transparency and control. All transactions, fees, and payouts are always accessible.
- **All major payment methods and different currencies.** Your customers want to pay in your webshop the way they prefer.
- **Quick Checkout.** With the Remember-me function, the ordering process is shortened, and conversion is boosted.
- **Checkout Styler.** Customizable to match the look and feel of your webshop for an optimal user experience.
- **Quick setup.** Easy onboarding with our local support, available at all times.

#### Security is paramount to us!
We are a PCI-DSS Level 1 certified company and comply with the latest PSD2 and Strong Customer Authentication requirements. Payment data is processed only in our secure environment.

**Our checkout is conversion-optimized:** With our checkout, you improve your conversion rate, reduce cart abandonments, enhance customer loyalty, and ultimately increase revenue.

**Experience the benefits of our checkout solution and take your webshop to the next level.**

**Your Benefits:**
- Key payment methods for your webshop.
- No setup or monthly fees.
- Pay only for successful transactions.
- Optimal user experience: Checkout is customizable to match your webshop design.
- Conversion-optimized for increased revenue.
- Local support.
- Trusted by more than 140,000 online merchants.

You need an account to get the credentials to test the checkout. [Please register to use the Nexi Checkout](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

The Nexi Group stands as Europe's leading PayTech provider, processing a remarkable 6.1 trillion transactions globally each year. Our payment and checkout solutions are actively utilized by over 140,000 online merchants! We have an intimate understanding of online commerce and local markets. Our checkout system was meticulously developed by experts near you specifically for the local e-commerce. With our localized approach, we offer expertise, support, and market insights to equip merchants with payment solutions that drive successful and seamless growth within their markets. **Experience the benefits of our checkout solution and take your plentymarket webshop to the next level.**

[Please register to use the Nexi Checkout](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

## Nexi Checkout - Integration Guide
### Configuration Guide - Assistant
1. After the installation of the plug-in, the Nexi Checkout Plugin Assistant is available, which must be completed. You can find it under **"Setup » Assistants » Plugins » {Plugin-Set} » Nexi Checkout"**

![NE_Assistant](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_assistent.png)

2. In the first step of the assistant (credentials) you have to enter your Checkout Key and your Secret Key, which you can find in your Nexi Dashboard under **"Company » Integration"**
(If you have any questions about your Nexi Checkout account, please contact the [support](https://developers.nets.eu/nets-easy/en-EU/support/) directly)

![NE_Dashboard](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_dashboard.png)

3. In the 2nd step of the assistant (availability) you can select which payment methods you would like to offer in your checkout.

![NE_assistant_availability](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_assistent_verfuegbarkeit.png)

4. In the 4th of the assistant (Additional settings) you will find specific settings that affect the plugin functionalities:  
   - "**Consumer data outside checkout**" - If you enable this feature, then customer information from the billing address will be automatically transferred to Nexi Checkout. So your customer doesn't have to enter their data twice.
   - "**Backend notifications**" - If you enable this feature, then you will be informed about errors that may occur in the event actions of the plugin via notification in the backend.

5. In the 5th step of the assistant (webhook settings) you can activate and configure additional webhook functionalities:
    - "**Change order status when *X* happens**" - It is possible to change the status of an order based on a specific webhook event. (e.g. set the order status to 3.X if the charge of a payment has not been possible)
    - "**Generate credit note in plentymarkets on refund via dashboard**" - It is possible to have a credit note that has been created in the Nexi dashboard automatically created in plentymarkets. You can also configure the status in which it is to be created. 

6. In the 6th step of the assistant (Apple Pay) you can store the content of a domain verification file. This is necessary if you want to offer Apple Pay in your checkout, otherwise this step is optional. You can find more information about the domain verification file in your checkout portal.

6. Done - The configuration of the assistant is now complete

### Configuration guide - payment methods in plentymarkets: 
In the latest version of the plugin, it is possible to offer individual payment methods via the Nexi Checkout. You can do this in the 2nd step of the assistant (availability). 

If you work with customer classes in your system, the respective payment method must be activated accordingly for your customer class(es), only then will it be visible in the checkout. (Please note that [Apple Pay](https://developer.nexigroup.com/nexi-checkout/en-EU/docs/apple-pay/) is only displayed in certain browsers/operating systems)

1. You can find the configuration of your customer class(es) under: **"Setup » CRM » Customer classes "**

2. The corresponding payment method with the prefix "Easy" or "Nexi" must then be added in the "Allowed payment methods" area

![NE_customerclasses](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_kundenklassen_new.png)

### Configuration guide - Event actions
The following event actions are provided to you by the plugin:

1. **Nexi Checkout Charge payment** - This event allows you to charge an already reserved payment

2. **Nexi Checkout Cancel payment** - This event allows you to cancel a reserved payment. (Please note: you cannot cancel a payment that has already been charged)

3. **Nexi Checkout Refund payment** - This event allows you to refund a payment that has already been charged. (Please note: A refund can only be made based on an order of type Credit)

#### Example: configuration of event actions:  
Basically, it is up to you how you configure the event actions in your system. Our recommendations refer to a standardized order-management process.

1. **Charging a Payment - Nexi Checkout Charge payment:**
We recommend you debit a payment as soon as the appropriate goods have been transferred to shipping.

   **Event:**       Goods issue posted  
   **Filter:**      Payment methods, order type if applicable.
   **Action:**      Nexi Checkout Charge payment

![NE_charge_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_charge_event.png)

2. **Cancel a payment - Nexi Checkout Cancel payment**
You should map the cancellation of a payment using your cancellation status and use filters to ensure that the event is only executed for orders related to the relevant payment types.

    **Event:**      Status change
    **Filter:**     Payment methods, order type if applicable.
    **Action:**     Nexi Checkout Cancel payment

![NE_cancel_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_cancel_event.png)

3. **Refund of a payment - Nexi Checkout Refund payment:**
Refund of a payment is relevant accordingly after receiving a return. The event should be configured to be executed for corresponding credits. 
We recommend here the creation of a corresponding status in the "Credit note" area - 11.x 

    **Event:**      Status change
    **Filter:**     Payment types, if necessary order type
    **Action:**     Nexi Checkout Refund payment

![NE_refund_event](https://cdn02.plentymarkets.com/8bc77dyqm1gj/frontend/marketplace_images/NE_refund_event.png)

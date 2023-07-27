# Nexi | Nets Checkout
## Online payments. Local. Relevant.

**The ultimate plentymarket Checkout with personal and local support!** Our Checkout and Payment mix for more revenue. Avoid purchase abandonment by offering your customers their preferred payment methods. Our Checkout has everything on board for you and your customers:

- Credit card
- PayPal and Apple Pay
- Instant bank transfer
- Direct debit
- AMEX
- Purchase on invoice
- Installment payment

**Optimized for conversion.** With the following features and our services, we support you in achieving your sales goals and growth: **Benefit from unbeatable conditions and other advantages:**

- No setup and monthly fees
- Everything in view in our dashboard
- All important payment methods
- Selling abroad
- Quick Checkout
- Checkout Styler
- One contract. One contact person

**Fast setup.** Easy onboarding with our local support, who is always available to help.

You need an account to get the credentials to test the checkout. [Please register to use the Nexi | Nets payment checkout](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

**Nets is part of the Nexi Group** and with 6.1 trillion transactions annually, Europe's largest PayTech provider. Our payment and checkout solutions are used by more than 170,000 merchants worldwide! We offer you the ultimate checkout plug-in for all your e-commerce transactions, so you can better focus on your business and customers. With our solution, you can optimize your conversion rate, reduce the number of purchase abandonments, and increase customer loyalty. **Experience the benefits of our checkout solution and take your plentymarket webshop to the next level.**

[Please register to use the Nexi | Nets payment checkout](https://ecom.nets.eu/de/plentymarkets-checkout/?utm_source=plentymarketplace&utm_medium=partner-page&utm_campaign=plentymarkets#form)

## Nexi | Nets Checkout - Integration Guide
### Configuration Guide - Assistant
1. After the installation of the plug-in, the Nets Easy Plugin Assistant is available, which must be completed. You can find it under **"Setup » Assistants » Plugins » {Plugin-Set} » Nets Easy"**

![NE_Assistant](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_assistant_en.png)

2. In the first step of the assistant (credentials) you have to enter your Checkout Key and your Secret Key, which you can find in your Nets Easy Dashboard under **"Company » Integration"**
(If you have any questions about your Nets Easy account, please contact the [support](https://developers.nets.eu/nets-easy/en-EU/support/) directly)

![NE_Dashboard](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_dashboard_en.png)

3. Steps 2 (countries of delivery) and 3 (additional information) of the assistant are standard configurations of a payment method in the plentymarkets context.

4. In the 4th step of the assistant (logo) you can customize the logo of the payment method itself as well as choose from a number of additional payment method icons to display at the payment method in the checkout 
This way you can clearly communicate to your customers which payment methods are available through Nets Easy.

![NE_frontend_icons](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_icons_frontend.png)

5. In the 5th and last step of the assistant (Additional settings) you will find specific settings that affect the plugin functionalities:  
   - "**Consumer data outside checkout**" - If you enable this feature, then customer information from the billing address will be automatically transferred to Net Easy Checkout. So your customer doesn't have to enter their data twice.
   - "**Backend notifications**" - If you enable this feature, then you will be informed about errors that may occur in the event actions of the plugin via notification in the backend.

6. Done - The configuration of the assistant is now complete

### Configuration guide - payment methods in plentymarkets: 
If you work with customer classes in your system, Easy Pay must be activated accordingly for your customer class(es), only then will it be visible in the checkout.

1. You can find the configuration of your customer class(es) under: **"Setup » CRM » Customer classes "**

2. In the area "Permitted payment methods" you have to add Easy Pay accordingly

![NE_Kundenklassen](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_kundenklasse_en.png)
### Configuration guide - Event actions
The following event actions are provided to you by the plugin:

1. **NetsEasy Charge payment** - This event allows you to charge an already reserved payment

2. **NetsEasy Cancel payment** - This event allows you to cancel a reserved payment. (Please note: you cannot cancel a payment that has already been charged)

3. **NetsEasy Refund payment** - This event allows you to refund a payment that has already been charged. (Please note: A refund can only be made based on an order of type Credit)

#### Example: configuration of event actions:  
Basically, it is up to you how you configure the event actions in your system. Our recommendations refer to a standardized order-management process.

1. **Charging a Payment - NetsEasy Charge payment:**
We recommend you debit a payment as soon as the appropriate goods have been transferred to shipping.

   **Event:**       Goods issue posted  
   **Filter:**      Payment methods, order type if applicable.
   **Action:**      NetsEasy Charge payment

![NE_charge_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_charge_event_en.png)

2. **Cancel a payment - NetsEasy Cancel payment**
You should map the cancellation of a payment using your cancellation status and use filters to ensure that the event is only executed for orders related to the relevant payment types.

    **Event:**      Status change
    **Filter:**     Payment methods, order type if applicable.
    **Action:**     NetsEasy Cancel payment

![NE_cancel_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_cancel_event_en.png)

3. **Refund of a payment - NetsEasy Refund payment:**
Refund of a payment is relevant accordingly after receiving a return. The event should be configured to be executed for corresponding credits. 
We recommend here the creation of a corresponding status in the "Credit note" area - 11.x 

    **Event:**      Status change
    **Filter:**     Payment types, if necessary order type
    **Action:**     NetsEasy Refund payment

![NE_refund_event](https://cdn02.plentymarkets.com/ivnbujmb83j4/frontend/NexiNets_Checkout_Plugin_images/Userguide_images/NE_refund_event_en.png)
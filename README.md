# Plentymarkets with Nets Easy

|Module | Nets Easy Payment Module for plentymarkets
|------|----------
|Author | `Nets eCom / Web Wikinger`
|plentyshop LTS Version | `v5.0.0+`
|iO Version | `v5.0.0+`
|plugin Version | `1.0.1`
|Documentation Guide | https://developer.nexigroup.com/nets-easy/en-EU/docs/nets-easy-for-plentymarkets/
|Github | https://github.com/Nets-eCom/Plentymarkets_easy
|License | `MIT License`

## Techsite Link
- https://developer.nexigroup.com/nets-easy/en-EU/docs/nets-easy-for-plentymarkets/

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
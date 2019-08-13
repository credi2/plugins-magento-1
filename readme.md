# cashpresso 
 [Follow the link for more details about cashpresso.](https://www.cashpresso.com/de/i/business)
 
### Table of Contents
**[1. Installation Instructions](#installation-instructions)**<br>
**[2. Configuration](#configuration)**<br>
**[3. How it works](#howto)**<br>
**[4. For developers](#developers)**<br>
**[5. Integration into other checkout modules](#checkout)**<br>
**[6. Links](#links)**<br>

## 1. Installation Instructions
**Magento Classic installation**

1. Copy all files from ```src/app``` to ```app``` folder in your project.
2. ```src/skin/frontend/base/default/cashpresso/images``` to ```skin/frontend/base/default/cashpresso/images folder in your project```
3. Run your magento cron file. The module includes a cronjob, which runs every half hour. It updates the information about your cashpresso account to ```cashpresso/partnerinfo``` config which is accessible in the database only.

## 2.Configuration

1. Logout your current admin session and then login again.
2. All settings are here: 
    
    ```Magento Admin Menu / System / Configuration / Payment Methods / cashpresso```
    
   By default the cashpresso Payment Module is not activated. You need to get an API key and Secret Key, which you can find in your cashpresso account.
   Fill the fields API KEY and Secret Key in the magento settings and save your settings. (You can fill other settings also, but don't activate the payment method "Cahspresso" until you have not saved your API Key and Secret Key).
   Now you should receive the informations of the settings in your cashpresso account:
   
   ![Step 1](configuration.png)
   
   The option ```Target account``` will be available only, if target accounts exists in your cashpresso account. 
    
3. Options table 
  
   Option | Description | Dependency
   ------ | ----------- | ---
   Account | Only needed, if you want to receive payments to different bank accounts on a per purchase basis. If not specified the purchase is paid to the main bank account. Please contact your account manager for more information on using multiple target bank accounts. Notice: You cannot create, edit or remove accounts in this module. |
   Mode | You can test the payment method "cashpresso" using the test mode. Its recommended to use the test mode at the beginning. |
   Title | This is the title of the payment method on the checkout page |
   Payment from Applicable Countries | You can set the filter to restrict the availability of the payment method "cashpresso" for specific countries. |
   Payment from Specific Countries | If restricted availability is selected in the step before, select here the countries, where the payment method "cashpresso" is available | 
   Instructions | This is the description of the payment method, that appears on the checkout page |
   Product label status | Switch it to YES if you want to show information about cashpresso rates on the product page or on category pages |
   Product label integration | You can choose between "Product level integration" (recommended) and "Static Label Integration". This means you use the cashpresso Javascript or your custom text for the displayed rates. The Static Label Version has several disadvantages: <br> - No detection of returning cashpresso customers <br> - No indication for a successful risk check <br> - Server side calculation of rates is necessary. | Product label integration
   Show checkout button | Show the checkout button on the cashpresso popup, if you selected ```Show checkout button``` for the Product label integration. | Show checkout button, Product label status
   Checkout url | The URL of your checkout page |
   Place to show | The place, where to show the cashpresso rates information | Place to show, Product label status  
   Template | The template for the cashpresso rates if you selected ```Static Label Integration``` for the Product label integration | Product label status, Product label integration
   The timeout for the order | Time in hours to wait for the approvement of the payment from cashpresso, after placing the order. |
   Sign contract text | The text on the success page for the following order approvement |
   Write log | Choose YES, if the api requests should be written to the log files. |
   Sort Order | Sets the order of the payment methods in the list on the checkout page| 

3. Options table

    When you save the configuration, do not forget to clean the cache.
    
### 2.1 Option to show the checkout button

If you want to use this option, check magentos redirect option before:

    Magento Admin Menu / System / Checkout / Cart / Redirect to cart
 
If it's set to "yes", the button redirects to the cart page only. Otherwise it redirects to the checkout page.

## 3. How it works

- customers can calculate automatically their cashpresso rates on a product page. 
- customers can add one or more products to their cart 
- on the checkout, in the payment step, customers can choose "cashpresso" as their payment method and recalculate their rates for the order.
- after a successful purchase, customers receive a success page, where the cashpresso widget is triggered and if they are first time customers, they are asked to start a videocall with cashpresso to approve their account. If they are already registered, cashpresso approves the rate.
- after the approvement cashpresso sends the status of the transaction to your store (success, canceled/timeout). If the status is "canceled/timeout", the order will be canceled automatically. The status "success" will assign the status "in process" to the related order.
  

## 4. For developers
### 4.1 Blocks which are used for the transport html definition

 - Mage_Checkout_Block_Onepage_Success
 - Mage_Catalog_Block_Product_Price

### 4.2 Adding the cashpresso price information on catalog and product pages
By default, the price can only be shown for the product type of "simple products". But you can use an observer if you want to add the same functionality for configurable products or for other product types.

    cashpresso_type_handler
    
An example (you can find it here as well: ```app/code/community/LimeSoda/Cashpresso/Model/Observer/Sample.php```):

In your config.xml file:

    <cashpresso_type_handler>
        <observers>
            <your_module_cashpresso_types>
                <type>singleton</type>
                <class>your_module/observer_sample</class>
                <method>addType</method>
            </your_module_cashpresso_types>
        </observers>
    </cashpresso_type_handler>
    
Some of your observer classes:
    
    class Your_Module_Model_Observer_Sample
    {
        public function addType(Varien_Event_Observer $observer)
        {
            $result = $observer->getEvent()->getResult();
            $product = $observer->getEvent()->getProduct();
    
            array_push($result->types, 'configurable');
            
            // ...
        }
    }

### 4.3 Change the place of the cashpresso price on your product page
By default it's placed right after the standard price. 
If you want to change the place, you need to change the parameter "Place to show" to "In catalog/search".

After that, add to your layout file this section:

      <catalog_product_view>
            <reference name="product.info.extrahint">
                <block type="ls_cashpresso/button" ifconfig="payment/cashpresso/active"
                       name="product.info.extrahint.cashpresso_button" translate="label"/>
            </reference>
       </catalog_product_view>  

You can change the reference "product.info.extrahint" to any name.

### 4.4 If you use a checkout handler not like ```checkout_onepage_index```

Add to your checkout index handler this layout:

    <reference name="footer">
        <block type="ls_cashpresso/checkout" ifconfig="payment/cashpresso/active" name="footer.cashpresso.script" translate="label"/>
    </reference> 
    
### 4.5 Redirect definition

You can manage a redirection in this template:

    app/design/frontend/base/default/template/limesoda/cashpreso/page/js/head.phtml
   
You can define you redirect url using the observer 
    
    cashpresso_js_c2checkout_url
   
An example:

In your config.xml file:
     
     <cashpresso_js_c2checkout_url>
         <observers>
             <your_module_cashpresso_types>
                 <type>singleton</type>
                 <class>your_module/observer_sample</class>
                 <method>setUrl</method>
             </your_module_cashpresso_types>
         </observers>
     </cashpresso_js_c2checkout_url>
         
Some of your observer classes:
     
     class Your_Module_Model_Observer_Sample
     {
         public function setUrl(Varien_Event_Observer $observer)
         {
             $urlObject = $observer->getEvent()->getUrl();
             $urlObject->url = "your url";
         }
     }

### 4.6 API testing

You can activate the Simulation Mode in your cashpresso account to test the magento API.

On magento side it works only in the test-mode.

1. Create an order using the frontend.
2. Open the page: ```http://yourwebsite.com/cashpresso/api/index```
   You will see an url. Something like this: ```http://yourwebsite.com/cashpresso/api/test/type/success/purchaseID/SIM-....b60a/```
3. Copy the previous link and replace ```SIM-....b60a``` by your purchaseID. You can find it in the DB: sales_flat_order_payment: additional_data field.
   Type parameters could have one of three status:  
   
    - success, if you want to test the success cashpresso response
    - canceled, if you want to test the cancellation from cashpresso side
    - timeout, if you want to test the timeout response from cashpresso side.

## 5. Integration into other checkout modules

If you use third party extension for the checkout process, you have to change the layout handle name to the name your extension uses.

Place this code to local.xml file in your theme.
```
<your_handle_name_of_checkout_index>
    <reference name="before_body_end">
        <block type="ls_cashpresso/checkout" ifconfig="payment/cashpresso/active" name="footer.cashpresso.script"
               translate="label"/>
    </reference>
</your_handle_name_of_checkout_index>
``` 

## 6. Links
 - [cashpresso API](https://partner.cashpresso.com/#/api)
 - [cashpresso](https://www.cashpresso.com/de/i/business)
 - [Developer contacts](https://www.kawa-commerce.com/kontakt/)

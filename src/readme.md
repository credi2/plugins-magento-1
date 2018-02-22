CASHPRESSO

https://test.cashpresso.com/urlreferral/api/ecommerce/v2?1

Used blocks:
Mage_Checkout_Block_Onepage_Success


app/code/local/Limesoda/Cashpresso/Model/Observer/Sample.php

```
<cashpresso_type_handler>
    <observers>
        <ls_cashpresso_types>
            <type>singleton</type>
            <class>ls_cashpresso/observer_sample</class>
            <method>addType</method>
        </ls_cashpresso_types>
    </observers>
</cashpresso_type_handler>
```

```
class Limesoda_Cashpresso_Model_Observer_Sample
{
    public function addType(Varien_Event_Observer $observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        array_push($result->types, 'configurable');
    }
}
```


Show checkout button option:
in Limesoda_Cashpresso_Helper_Button its possible to define custom checkout page url using observer cashpresso_js_c2checkout_url 
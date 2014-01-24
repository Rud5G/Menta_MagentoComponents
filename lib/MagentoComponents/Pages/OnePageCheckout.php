<?php

class MagentoComponents_Pages_OnePageCheckout extends Menta_Component_AbstractTest
{

    public function goThrowCheckout($newsletter = false)
    {
        $this->open();

        $this->finishStep('billingAddress');

        $this->waitForShippingMethod();
        $this->finishStep('shippingMethod');

        $this->selectPaymentMethodCheckmo();
        $this->finishStep('paymentMethod');

        $this->submitForm();
    }

    public function signInWithExistingAccount($login, $password)
    {
        $this->getHelperCommon()->type('id=login-email', $login, true, false);
        $this->getHelperCommon()->type('id=login-password', $password, true, false);
        $this->getTest()->assertElementPresent($this->getLoginButtonPath());
        $this->getTest()->click($this->getLoginButtonPath());
    }

    public function assertUserLogged()
    {
        $this->getTest()->assertElementNotPresent('//div[@class="step-title"]//h2[contains(text(),"Checkout Method")]');
    }

    /**
     * Open (one page) checkout
     *
     * @return void
     */
    public function open()
    {
        $this->getTest()->open($this->getCheckoutUrl());
        $this->getTest()->waitForElementPresent('//*[@id="checkoutSteps"]',10);
        $this->getTest()->assertTitle('Checkout');
    }

    /**
     * Get (one step) checkout url
     *
     * @return string
     */
    public function getCheckoutUrl()
    {
        return '/checkout/onepage/';
    }

    public function setCheckoutMethod($method)
    {
        if ($method == 'register' || $method == 'guest') {
            $this->getTest()->assertElementPresent("//label[@for='login:$method']");
            $this->getTest()->click("//label[@for='login:$method']");
        }
    }

    public function finishStep($step)
    {
        if ($step == 'checkoutMethod') {
            $buttonPath = '//*[@id="onepage-guest-register-button"]';
        } else if ($step == 'billingAddress') {
            $buttonPath = '//div[@id="billing-buttons-container"]//span[contains(text(),"Continue")]';
        } else if ($step == 'shippingAddress') {
            $buttonPath = '//div[@id="shipping-buttons-container"]//span[contains(text(),"Continue")]';
        } else if ($step == 'shippingMethod') {
            $buttonPath = '//div[@id="shipping-method-buttons-container"]//span[contains(text(),"Continue")]';
        } else if ($step == 'paymentMethod') {
            $buttonPath = '//div[@id="payment-buttons-container"]//button//span[contains(text(),"Continue")]';
        }
        $this->getTest()->assertElementPresent($buttonPath);
        $this->getTest()->click($buttonPath);
    }

    /**
     * Add billing address
     *
     * @param string $country
     * @return array complete address data that was used
     */
    public function addBillingAddress($country = 'us')
    {
        $addressProvider = new MagentoComponents_Provider_Address();
        $address = $addressProvider->getAddressField('billing', $country);


        $address['email'] = Menta_ComponentManager::get('MagentoComponents_Pages_CustomerAccount')->createNewMailAddress('oscbillling');

        $this->getTest()->typeAndLeave("id=billing:firstname", $address['firstname']);
        $this->getTest()->typeAndLeave("id=billing:lastname", $address['lastname']);
        $this->getTest()->typeAndLeave("id=billing:email", $address['email']);
        $this->getTest()->typeAndLeave("id=billing:telephone", $address['phone']);
        $this->getTest()->typeAndLeave("id=billing:street1", $address['street1']);
        $this->getTest()->typeAndLeave("id=billing:street2", $address['street2']);
        $this->getTest()->typeAndLeave("id=billing:city", $address['city']);
        $this->getTest()->typeAndLeave("id=billing:postcode", $address['postcode']);
        $this->getTest()->typeAndLeave("id=billing:company", $address['company']);
        $this->getTest()->select("id=billing:country_id", "label=" . $address['country']);
        if (isset($address['region']) && $address['region']) {
            $this->getTest()->select("id=billing:region_id", "label=" . $address['region']);
        }

        return $address;
    }

    /**
     * Add shipping address
     *
     * @param string $country
     * @return array complete address data that was used
     */
    public function addShippingAddress($country = 'us')
    {

        $addressProvider = new MagentoComponents_Provider_Address();
        $address = $addressProvider->getAddressField('shipping', $country);

        $this->getTest()->typeAndLeave("id=shipping:firstname", $address['firstname']);
        $this->getTest()->typeAndLeave("id=shipping:lastname", $address['lastname']);
        $this->getTest()->typeAndLeave("id=shipping:street1", $address['street1']);
        $this->getTest()->typeAndLeave("id=shipping:street2", $address['street2']);
        $this->getTest()->typeAndLeave("id=shipping:city", $address['city']);
        $this->getTest()->typeAndLeave("id=shipping:telephone", $address['phone']);
        $this->getTest()->typeAndLeave("id=shipping:postcode", $address['postcode']);
        $this->getTest()->typeAndLeave("id=shipping:company", $address['company']);
        $this->getTest()->select("id=shipping:country_id", "label=" . $address['country']);
        if (isset($address['region']) && $address['region'] != '') {
            $this->getTest()->select("id=shipping:region_id", "label=" . $address['region']);
        }

        return $address;
    }

    public function waitForShippingMethod()
    {
        $this->getTest()->waitForVisible('//*[@id="checkout-shipping-method-load"]');
    }

    public function waitForReview()
    {
        $this->getTest()->waitForVisible('//*[@id="checkout-review-load"]');
    }

    public function enterValidCreditCardDataVisa()
    {
        $this->getTest()->waitForVisible('//input[@id="p_method_braintree"]');
        $this->getTest()->click('//input[@id="p_method_braintree"]');
        $this->getTest()->waitForVisible('//ul[@id="payment_form_braintree"]');
        $this->getTest()->select('id=braintree_cc_type', 'label=Visa');
        $this->getTest()->typeAndLeave('id=braintree_cc_number', '4111111111111111');
        $this->getTest()->fireEvent('id=braintree_cc_number', 'blur');
        $this->getTest()->select('id=braintree_expiration', 'label=03 - March');
        $this->getTest()->select('id=braintree_expiration_yr', 'label=2020');
        $this->getTest()->typeAndLeave('id=braintree_cc_cid', '123');
        $this->getTest()->fireEvent('id=braintree_cc_cid', 'blur');
    }

    public function submitForm($checkValidationPassed = TRUE)
    {
        $this->getTest()->assertElementPresent($this->getPlaceOrderButtonPath());
        $this->getTest()->click($this->getPlaceOrderButtonPath());
        sleep(1);
        if ($checkValidationPassed) {
            $this->getTest()->assertElementNotPresent("css=.validation-failed");
            $this->getHelperAssert()->assertTextNotPresent("Please check red fields below and try again");
            $this->getHelperWait()->waitForTextPresent('Sending your order', 2);
        }
    }

    public function toggleShipToDifferentAddress()
    {
        $this->getTest()->assertElementPresent('//input[@id="billing:use_for_shipping_no"]');
        $this->getTest()->click('//input[@id="billing:use_for_shipping_no"]');
    }

    /**
     * Get order id from order success page
     *
     * @author Joerg Winkler <joerg.winkler@aoemedia.de>
     * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
     * @return integer Order ID
     */
    public function getOrderIdFromSuccessPage()
    {
        $waitHelper = Menta_ComponentManager::get('Menta_Component_Helper_Wait');
        /* @var $waitHelper Menta_Component_Helper_Wait */
        $this->getTest()->assertTrue(
            $waitHelper->waitForElementPresent("//h1[contains(text(),'Thanks for shopping with Angry Birds!')]"),
            'Waiting for headline "Thanks for shopping with Angry Birds!" timed out'
        );
        $waitHelper->waitForElementPresent('//span[@class="order_number"]');
        $orderId = $this->getTest()->getElement('//span[@class="order_number"]')->getAttribute('innerHTML');
        $this->getTest()->assertNotEmpty($orderId, 'No order id found!');
        return $orderId;
    }

    public function getOrderNumberFromSuccessPage()
    {
        $this->getTest()->waitForElementPresent('//h1[contains(text(), "Your order has been received.")]');
        $orderNumber = $this->getTest()
            ->getElement('//p[contains(text(),"Your order")]//a')->getText();
        return $orderNumber;
    }

    public function assertPriceInSummary($productId, $expectedPrice)
    {
        $price = $this->getTest()->getText('//tr[@id="product_' . $productId . '"]//td//div[@class="cart-price"]');
        $price = strip_tags($price);
        $this->getTest()->assertEquals($expectedPrice, $price, 'Prices in summary is not as expected');
    }

    public function saveAccountForLaterUse()
    {
        $commonHelper = Menta_ComponentManager::get('Menta_Component_Helper_Common');
        /* @var $commonHelper Menta_Component_Helper_Common */
        $password = $this->getConfiguration()->getValue('testing.frontend.password');
        $commonHelper->type('billing:customer_password', $password, true, true);
        $commonHelper->type('billing:confirm_password', $password, true, true);
    }

    /**
     * Path for checkbox "Save account for later use" (or other thing to click on)
     * @return string
     */
    public function getSaveAccountCheckPath()
    {
        return "id=id_create_account";
    }

    public function assertShipping($expectedPrice)
    {
        $this->assertTotal($expectedPrice, 'shipping', 'Shipping price does not match');
    }

    //ONESTEP TEMP

    public function assertTotal($expectedPrice, $type, $message = '')
    {
        $price = $this->getTest()->getText('//table[@id="checkout-review-totals-table"]//tr[' . GeneralComponents_Div::contains($type) . ']//td[' . GeneralComponents_Div::contains('value') . ']');
        $price = strip_tags($price);
        var_dump($expectedPrice . ' ' . $price . ';');
        $this->getTest()->assertEquals($expectedPrice, $price, $message);
    }

    public function assertTax($expectedPrice)
    {
        $this->assertTotal($expectedPrice, 'tax', 'Tax does not match');
    }

    public function assertGrandTotal($expectedPrice)
    {
        $this->assertTotal($expectedPrice, 'grand-total', 'Grand total does not match');
    }

    public function assertSubtotal($expectedPrice)
    {
        $this->assertTotal($expectedPrice, 'subtotal', 'Subtotal does not match');
    }

    public function assertBillingCountry($countryCode)
    {
        $commonHelper = Menta_ComponentManager::get('Menta_Component_Helper_Common');
        $selected = $commonHelper->getSelectedValue('//select[@id="billing:country_id"]');
        $this->getTest()->assertEquals($countryCode, $selected);
    }

    /**
     * Shipping price form middle column
     * @param $name e.g. standard, priority...
     * @param $price
     */
    public function assertShippingPrice($name, $price)
    {
        $commonHelper = Menta_ComponentManager::get('Menta_Component_Helper_Common');
        /* @var Menta_Component_Helper_Common */
        $actualPrice = $commonHelper->getText("//tr[@class='type_" . $name . "']/td[@class='shipping-price']");
        $this->getTest()->assertEquals($price, $actualPrice);
    }

    public function prepareShippingAddressFieldsForLoggedInUsers($conditionForOptionToSelect = "label=New Address")
    {
        $this->getTest()->waitForElementPresent("id=shipping-address-select");
        $this->getTest()->select("id=shipping-address-select", $conditionForOptionToSelect);
    }

    public function toogleShipToTheSameAddress()
    {
            $this->getTest()->click("id=billing:use_for_shipping_yes");
    }

    public function prepareShippingAddressFieldsForNewUsers()
    {
        //$this->getTest()->click("id=billing:use_for_shipping_yes"); //was not working with pretty checkboxes
        $this->toogleShipToTheSameAddress();
    }

    public function selectSavedBillingAddress($conditionForOptionToSelect = "value=")
    {
        $this->getTest()->waitForElementPresent("id=billing-address-select");
        $this->getTest()->select("id=billing-address-select", $conditionForOptionToSelect);
    }

    public function acceptTermsAndConditions()
    {
        $this->getTest()->assertElementPresent("//label[@for='id_accept_terms']", 'Could not find terms and conditions checkbox');
        // $this->getTest()->click("id=id_accept_terms");
        if (!$this->getHelperCommon()->isSelected("//input[@id='id_accept_terms']")) {
            $this->getTest()->click("//input[@id='id_accept_terms']");
        }
    }

    public function selectShippingFlatRate()
    {
        $this->selectShipping('Flat Rate');
    }

    public function selectShipping($name)
    {
        $this->getTest()->click("//td[@class='shipping-name']/label[text()='$name']");
    }

    public function selectShippingMethodPriority()
    {
        $this->selectShipping('Priority');
    }

    public function selectPaymentMethodCheckmo()
    {
        $this->selectPaymentMethod('checkmo');
    }

    public function selectPaymentMethod($code)
    {
        $this->getTest()->click("//dl[@id='checkout-payment-method-load']//input[@id='p_method_$code']");
    }

    public function selectNewsletterCheckbox()
    {
        $this->getTest()->click("//input[@id='id_subscribe_newsletter']");
    }


    /*
     * get path for login button in checkout
     */
    public function getLoginButtonPath()
    {
        return '//div[@id="checkout-step-login"]//span[contains(text(),"Login")]';
    }

    /*
     * get Path for place order button
     * */
    public function getPlaceOrderButtonPath()
    {
        return '//div[@id="checkout-review-submit"]//span[contains(text(),"Place Order")]';
    }

}
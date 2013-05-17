<?php

class AoeComponents_Magento_Pages_CustomerAccount extends Menta_Component_AbstractTest {

	/**
	 * Path for element which is present only on dashboard page
	 * @return string
	 */
	public function getDashboardIndicatorPath() {
		return 'id=dash';
	}


	public function getSplitPageRegistrationButtonPath() {
		return "//div[contains(@class,'account-login')]//button//*[contains(text(),'Register')]";
	}

	/**
	 * Open login/register page
	 *
	 * @return void
	 */
	public function openSplitLoginOrRegister() {
		$this->getTest()->open('/customer/account/login/');
		$this->getHelperAssert()->assertBodyClass('customer-account-login');
		$this->getTest()->assertTextPresent($this->__('Login or Create an Account'));
		$this->getTest()->assertTextPresent($this->__('New Customers'));
		$this->getTest()->assertTextPresent($this->__('Registered Customers'));
	}

	/**
	 * Got to dashboard
	 *
	 * @return void
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2011-11-04
	 */
	public function openDashboard() {
		$this->getTest()->open('/customer/account/');
		$this->assertIsOnDashboard();
	}

	public function assertIsOnDashboard() {
		$this->getTest()->assertTitle($this->__('My Account'));
		$this->getTest()->assertTextPresent($this->__('My Dashboard'));
		$this->getTest()->assertElementPresent($this->getDashboardIndicatorPath());
	}

	/**
	 * Got to history
	 *
	 * @return void
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2011-11-04
	 */
	public function openOrderHistory() {
		$this->getTest()->open('/sales/order/history/');
		$this->getTest()->assertTitle('My Orders');
	}

	/**
	 * Open an order from the order history
	 *
	 * @param string $orderId
	 * @return void
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 * @since 2011-11-04
	 */
	public function openOrder($orderId) {
		$this->getTest()->open('/order/view/order_id/'.$orderId.'/');
	}

	/**
	 * Open address info
	 *
	 * @return void
	 * @author Fabrizio Branca
	 * @since 2013-04-23
	 */
	public function openAddressInfo() {
		$this->getTest()->open('/customer/address/');
	}

	/**
	 * Login
	 *
	 * @param string $username
	 * @param string $password
	 * @author Joerg Winkler <joerg.winkler@aoemedia.de>
	 * @author Fabrizio Branca <fabrizio.branca@aoemedia.de>
	 */
	public function login($username=NULL, $password=NULL) {
		if (is_null($username) || is_null($password)) {
			$username = $this->getConfiguration()->getValue('testing.frontend.user');
			$password = $this->getConfiguration()->getValue('testing.frontend.password');
		}
		$this->openSplitLoginOrRegister();
		//$this->getHelperCommon()->click("//ul[@class='links personal-items']/li[@class='first']/a");
		$this->getHelperCommon()->type("//input[@id='email']", $username, true, true);
		$this->getHelperCommon()->type("//input[@id='pass']", $password, true, true);

		Menta_Events::dispatchEvent('AoeComponents_Magento_Pages_CustomerAccount->login:beforeSubmit', array(
			'component' => $this
		));

		$this->getHelperCommon()->click("//button[@id='send2']");

		$this->getHelperAssert()->assertBodyClass('customer-account-index');
	}

	/**
	 * Got to forgot password page
	 *
	 * @return void
	 */
	public function openForgotPassword() {
		$this->getTest()->open('/customer/account/forgotpassword/');
	}

	/**
	 * Logout
	 *
	 * @author Joerg Winkler <joerg.winkler@aoemedia.de>
	 */
	public function logout() {
		$this->getTest()->clickAndWait("//ul[@class='links personal-items']/li[@class='first']/a");
		$this->getTest()->click("//a[@id='logout']");
		$this->getTest()->waitForElementPresent("//h1[contains(text(),'You are now logged out')]");
		$this->getTest()->assertElementPresent("//h1[contains(text(),'You are now logged out')]");
	}

	public function logoutViaOpen() {
		$this->getTest()->open('/customer/account/logout/');
	}

	public function createNewMailAddress($type='') {
		if (!$this->getConfiguration()->issetKey('testing.newmailaddresspattern')) {
			throw new Exception('No configuration for testing.newmailaddresspattern found');
		}
		$template = $this->getConfiguration()->getValue('testing.newmailaddresspattern');
		$replace = array(
			'###TYPE###' => $type,
			'###RANDOM###' => $this->createRandomString(4),
			'###TIME###' => time(),
			'###TESTID###' => $this->getTest()->getTestId()
		);
		return str_replace(array_keys($replace), array_values($replace), $template);
	}

    public function createRandomPassword($length=8) {
		return Menta_Util_Div::createRandomString($length);
	}

	public function createRandomName($length = 8) {
		$name = Menta_Util_Div::createRandomString($length, 'abcdefghijklmnopqrstuvwxyz');
		return ucfirst($name);
	}

	public function createRandomString($length = 8, $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
        return substr(str_shuffle($chars),0, $length);
    }

	/**
	 * Open registration page
	 *
	 * @author Fabrizio Branca
	 * @since 2012-11-19
	 */
	public function openRegistrationPage() {
		$this->getTest()->open('/customer/account/create/');
		$this->getHelperAssert()->assertBodyClass('customer-account-create');
	}

}
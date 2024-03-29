<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * One page checkout cart link
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Block_Onepage_Link extends Mage_Checkout_Block_Onepage_Abstract
{
    public function getCheckoutUrl()
    {
        return $this->getUrl('checkout/onepage', array('_secure'=>true));
    }

    public function isDisabled()
    {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    public function isPossibleOnepageCheckout()
    {
        return $this->helper('checkout')->canOnepageCheckout();
    }
    
	public function isLogin()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
    
	public function isAllowedGuestCheckout()
    {
        return $this->getQuote()->isAllowedGuestCheckout();
    }
    
	public function getReturnUrl()
    {
        return Mage::getUrl('checkout/onepage/guestCheckout');
    }
    
    public function getPostUrl()
    {
        return Mage::getUrl('customer/account/loginPostCheckout', array('_secure' => true));
    }
    
	public function getPostAction()
    {
        return Mage::getUrl('customer/account/loginPost', array('_secure' => true));
    }
    
    public function getRegisterAction()
    {
        return Mage::getUrl('customer/account/create', array('_secure' => true));
    }
    
    public function getCheckoutAction()
    {
        return Mage::getUrl('checkout/onepage/index/allowGuest/1', array('_secure' => true));
    }
}

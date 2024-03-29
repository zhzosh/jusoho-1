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


class Mage_Checkout_Model_Session extends Mage_Core_Model_Session_Abstract
{
    const CHECKOUT_STATE_BEGIN = 'begin';
    protected $_quote = null;

    /**
     * Class constructor. Initialize checkout session namespace
     */
    public function __construct()
    {
        $this->init('checkout');
    }

    /**
     * Unset all data associated with object
     */
    public function unsetAll()
    {
        parent::unsetAll();
        $this->_quote = null;
    }

    /**
     * Get checkout quote instance by current session
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if ($this->_quote === null) {
            $quote = Mage::getModel('sales/quote')
                ->setStoreId(Mage::app()->getStore()->getId());

            /* @var $quote Mage_Sales_Model_Quote */
            if ($this->getQuoteId()) {
                $quote->loadActive($this->getQuoteId());
                if ($quote->getId()) {
                    /**
                     * If current currency code of quote is not equal current currency code of store,
                     * need recalculate totals of quote. It is possible if customer use currency switcher or
                     * store switcher.
                     */
                    if ($quote->getQuoteCurrencyCode() != Mage::app()->getStore()->getCurrentCurrencyCode()) {
                        $quote->setStore(Mage::app()->getStore());
                        $quote->collectTotals()->save();
                        /*
                         * We mast to create new quote object, because collectTotals()
                         * can to create links with other objects.
                         */
                        $quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
                        $quote->load($this->getQuoteId());
                    }
                } else {
                    $this->setQuoteId(null);
                }
            }

            $customerSession = Mage::getSingleton('customer/session');

            if (!$this->getQuoteId()) {
                if ($customerSession->isLoggedIn()) {
                    $quote->loadByCustomer($customerSession->getCustomer());
                    $this->setQuoteId($quote->getId());
                } else {
                    $quote->setIsCheckoutCart(true);
                    Mage::dispatchEvent('checkout_quote_init', array('quote'=>$quote));
                }
            }

            if ($this->getQuoteId()) {
                if ($customerSession->isLoggedIn()) {
                    $quote->setCustomer($customerSession->getCustomer());
                }
            }

            $quote->setStore(Mage::app()->getStore());
            $this->_quote = $quote;
        }

        if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr()) {
            $this->_quote->setRemoteIp($remoteAddr);
            $xForwardIp = Mage::app()->getRequest()->getServer('HTTP_X_FORWARDED_FOR');
            $this->_quote->setXForwardedFor($xForwardIp);
        }
        return $this->_quote;
    }

    protected function _getQuoteIdKey()
    {
        return 'quote_id_' . Mage::app()->getStore()->getWebsiteId();
    }

    public function setQuoteId($quoteId)
    {
        $this->setData($this->_getQuoteIdKey(), $quoteId);
    }

    public function getQuoteId()
    {
        return $this->getData($this->_getQuoteIdKey());
    }

    /**
     * Load data for customer quote and merge with current quote
     *
     * @return Mage_Checkout_Model_Session
     */
    public function loadCustomerQuote()
    {
        if (!Mage::getSingleton('customer/session')->getCustomerId()) {
            return $this;
        }
        $customerQuote = Mage::getModel('sales/quote')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->loadByCustomer(Mage::getSingleton('customer/session')->getCustomerId());

        if ($customerQuote->getId() && $this->getQuoteId() != $customerQuote->getId()) {
            if ($this->getQuoteId()) {
                $customerQuote->merge($this->getQuote())
                    ->collectTotals()
                    ->save();
            }

            $this->setQuoteId($customerQuote->getId());

            if ($this->_quote) {
                $this->_quote->delete();
            }
            $this->_quote = $customerQuote;
        } else {
            $this->getQuote()->setCustomer(Mage::getSingleton('customer/session')->getCustomer())
                ->save();
        }
        return $this;
    }

    public function setStepData($step, $data, $value=null)
    {
        $steps = $this->getSteps();
        if (is_null($value)) {
            if (is_array($data)) {
                $steps[$step] = $data;
            }
        } else {
            if (!isset($steps[$step])) {
                $steps[$step] = array();
            }
            if (is_string($data)) {
                $steps[$step][$data] = $value;
            }
        }
        $this->setSteps($steps);

        return $this;
    }

    public function getStepData($step=null, $data=null)
    {
        $steps = $this->getSteps();
        if (is_null($step)) {
            return $steps;
        }
        if (!isset($steps[$step])) {
            return false;
        }
        if (is_null($data)) {
            return $steps[$step];
        }
        if (!is_string($data) || !isset($steps[$step][$data])) {
            return false;
        }
        return $steps[$step][$data];
    }

    /**
     * Retrieves list of all saved additional messages for different instances (e.g. quote items) in checkout session
     * Returned: array(itemKey => messageCollection, ...)
     * where itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param bool $clear
     *
     * @return array
     */
    public function getAdditionalMessages($clear = false)
    {
        $additionalMessages = $this->getData('additional_messages');
        if (!$additionalMessages) {
            return array();
        }
        if ($clear) {
            $this->setData('additional_messages', null);
        }
        return $additionalMessages;
    }

    /**
     * Retrieves list of item additional messages
     * itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param string $itemKey
     * @param bool $clear
     *
     * @return null|Mage_Core_Model_Message_Collection
     */
    public function getItemAdditionalMessages($itemKey, $clear = false)
    {
        $allMessages = $this->getAdditionalMessages();
        if (!isset($allMessages[$itemKey])) {
            return null;
        }

        $messages = $allMessages[$itemKey];
        if ($clear) {
            unset($allMessages[$itemKey]);
            $this->setAdditionalMessages($allMessages);
        }
        return $messages;
    }

    /**
     * Adds new message in this session to a list of additional messages for some item
     * itemKey is a unique hash (e.g 'quote_item17') to distinguish item messages among message collections
     *
     * @param string $itemKey
     * @param Mage_Core_Model_Message $message
     *
     * @return Mage_Checkout_Model_Session
     */
    public function addItemAdditionalMessage($itemKey, $message)
    {
        $allMessages = $this->getAdditionalMessages();
        if (!isset($allMessages[$itemKey])) {
            $allMessages[$itemKey] = Mage::getModel('core/message_collection');
        }
        $allMessages[$itemKey]->add($message);
        $this->setAdditionalMessages($allMessages);

        return $this;
    }

    /**
     * Retrieves list of quote item messages
     * @param int $itemId
     * @param bool $clear
     *
     * @return null|Mage_Core_Model_Message_Collection
     */
    public function getQuoteItemMessages($itemId, $clear = false)
    {
        return $this->getItemAdditionalMessages('quote_item' . $itemId, $clear);
    }

    /**
     * Adds new message to a list of quote item messages, saved in this session
     *
     * @param int $itemId
     * @param Mage_Core_Model_Message $message
     *
     * @return Mage_Checkout_Model_Session
     */
    function addQuoteItemMessage($itemId, $message)
    {
        return $this->addItemAdditionalMessage('quote_item' . $itemId, $message);
    }

    public function clear()
    {
        Mage::dispatchEvent('checkout_quote_destroy', array('quote'=>$this->getQuote()));
        $this->_quote = null;
        $this->setQuoteId(null);
        $this->setLastSuccessQuoteId(null);
    }

    /**
     * Clear misc checkout parameters
     */
    public function clearHelperData()
    {
        $this->setLastShippingAgreementId(null)
            ->setRedirectUrl(null)
            ->setLastOrderId(null)
            ->setLastRealOrderId(null)
            ->setLastRecurringProfileIds(null)
            ->setAdditionalMessages(null)
        ;
    }

    public function resetCheckout()
    {
        $this->setCheckoutState(self::CHECKOUT_STATE_BEGIN);
        return $this;
    }

    public function replaceQuote($quote)
    {
        $this->_quote = $quote;
        $this->setQuoteId($quote->getId());
        return $this;
    }
}

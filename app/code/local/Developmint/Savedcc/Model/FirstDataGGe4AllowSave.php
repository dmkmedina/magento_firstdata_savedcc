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
     * @package     Mage_Paypal
     * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
     * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */

    /**
     * Payflow Pro payment gateway model
     *
     * @category    Mage
     * @package     Mage_Paypal
     * @author      Magento Core Team <core@magentocommerce.com>
     */

class Developmint_Savedcc_Model_FirstDataGGe4AllowSave extends Mage_Firstdataglobalgateway_Model_Firstdataglobalgateway
{
    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setCcType($data->getCcType())
            ->setCcOwner($data->getCcOwner())
            ->setCcLast4(substr($data->getCcNumber(), -4))
            ->setCcNumber($data->getCcNumber())
            ->setCcCid($data->getCcCid())
            ->setCcExpMonth($data->getCcExpMonth())
            ->setCcExpYear($data->getCcExpYear())
            ->setCcSsIssue($data->getCcSsIssue())
            ->setCcSsStartMonth($data->getCcSsStartMonth())
            ->setCcSsStartYear($data->getCcSsStartYear())
        ;

        //Store the selected option for saving the credit card
        $details = array();
        if ($data->getCcSave()) {
            $details['cc_save'] = $data->getCcSave();
        }

        $info->setAdditionalInformation($details);


        return $this;
    }


    public function capture(Varien_Object $payment, $amount)
    {

        $this->logit('capture amount', $amount);
        $error = false;

        if ($payment->getParentTransactionId()) {
            $payment->setAnetTransType(self::REQUEST_TYPE_PRIOR_AUTH_CAPTURE);
        } else {
            $payment->setAnetTransType(self::REQUEST_TYPE_AUTH_CAPTURE);
        }

        $payment->setAmount($amount);

        $request = $this->_buildRequest($payment);
        $result  = $this->_postRequest($request);
        if ($result->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
            $payment->setStatus(self::STATUS_APPROVED);
            $payment->setCcTransId($result->getTransactionId());
            $payment->setLastTransId($result->getTransactionId());
            $payment->setPoNumber($result->getTransarmorToken());
            if ($result->getTransactionId() != $payment->getParentTransactionId()) {
                $payment->setTransactionId($result->getTransactionId());
            }
            $payment
                ->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('real_transaction_id', $result->getTransactionId());
            // added by Gayatri 10/Jun/2010
            if( !$order = $payment->getOrder() )
            {
                $order = $payment->getQuote();
            }
            $order->addStatusToHistory(
                $order->getStatus(),
                urldecode($result->getResponseReasonText()) . ' at FirstdataGlobalgateway, Trans ID: ' . $result->getTransactionId(),
                $result->getResponseReasonText() . ' from FirstdataGlobalgateway, Trans ID: ' . $result->getTransactionId()
            );
            // end added by Gayatri 10/Jun/2010
        } else {
            if ($result->getResponseReasonText()) {
                $error = $result->getResponseReasonText();
            } else {
                $error = Mage::helper('paygate')->__('Error in capturing the payment');
            }
            if( !$order = $payment->getOrder() )
            {
                $order = $payment->getQuote();
            }
            $order->addStatusToHistory(
                $order->getStatus(),
                urldecode($error) . ' at FirstdataGlobalgateway',
                $error . ' from FirstdataGlobalgateway'
            );
        }

        if ($error !== false) {
            Mage::throwException($error);
        }

        //has the user selected to save their credit card
        $save = $this->getInfoInstance()->getAdditionalInformation('cc_save');
        //Here we save the credit card. We only check if the capture was approved
        if ($save && $result->getResponseCode() == self::RESPONSE_CODE_APPROVED) {
            $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();

            if (!$customer_id) {
                $email = $request->getXEmail();
                $customer = Mage::getModel("customer/customer");
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($email);

                $customer_id = $customer->getId();
            }

            //save the credit card
            $cc_model = Mage::getModel('savedcc/savedcreditcards');
            $cc_model->setCustomer_id($customer_id);
            $cc_model->setToken($result->getTransarmorToken());
            $cc_model->setLast4($payment->getCcLast4());
            $cc_model->setCard_type($payment->getCcType());
            $cc_model->setActive('y');
            $cc_model->setExp_year($payment->getCcExpYear());
            $cc_model->setExp_month($payment->getCcExpMonth());
            $cc_model->setFull_name($request->getXFirstName() . ' ' . $request->getXLastName());

            /*if ($save) {
            }else {
                $cc_model->setAdminOnly('y');
            }*/

            $cc_model->save();
        }

        return $this;
    }
}
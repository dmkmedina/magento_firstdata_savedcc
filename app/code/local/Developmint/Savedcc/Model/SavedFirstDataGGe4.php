<?php

class Developmint_Savedcc_Model_SavedFirstDataGGe4 extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'developmint_savedcc/form_savedcc';
    protected $_infoBlockType = 'developmint_savedcc/info_savedcc';

    /**
     * unique internal payment method identifier
     */
    protected $_code = 'savedcc';

    /**
     * this should probably be true if you're using this
     * method to take payments
     */
    protected $_isGateway               = true;

    /**
     * can this method authorise?
     */
    protected $_canAuthorize            = false;

    /**
     * can this method capture funds?
     */
    protected $_canCapture              = true;

    /**
     * can we capture only partial amounts?
     */
    protected $_canCapturePartial       = false;

    /**
     * can this method refund?
     */
    protected $_canRefund               = true;

    /**
     * can this method do a partial refund?
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * can this method void transactions?
     */
    protected $_canVoid                 = false;

    /**
     * can admins use this payment method?
     */
    protected $_canUseInternal          = true;

    /**
     * show this method on the checkout page
     */
    protected $_canUseCheckout          = true;

    /**
     * available for multi shipping checkouts?
     */
    protected $_canUseForMultishipping  = true;

    /**
     * can this method save cc info for later use?
     */
    protected $_canSaveCc = false;

    /**
     * The third party payment gateway that this method references to perform charges
     */
    /*protected $method;

    protected function _construct() {
        $this->method = new Developmint_Autoship_Model_Autoshippayflowpro();
    }*/

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Checkmo
     */
    public function assignData($data)
    {
        $details = array();
        if ($data->getSavedCc()) {
            $details['saved_cc'] = $data->getSavedCc();
            //Mage::log($details);
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalInformation($details);
        }
        return $this;
    }

    /**
     * this method is called if we are just authorising
     * a transaction
     */
    /*public function authorize(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshipfirstdatagge4();
        $cc_id = $payment->getAdditionalInformation('saved_cc');
        $cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        $result = $method->autoship_authorize($payment_info, $amount);
        //$result = $method->autoship_pnref_authorize($payment, $cc_model->getPnref(), $amount);
        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_AUTH_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        //update the pnref
        $cc_model->setPnref($payment->getTransactionId());
        $cc_model->save();

        return $this;
    }*/

    /**
     * this method is called if we are authorising AND
     * capturing a transaction
     */
    public function capture(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshipfirstdatagge4();
        $cc_id = $payment->getAdditionalInformation('saved_cc');
        $cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        $exp_month = $cc_model->getExp_month() . '';
        $exp_month = strlen($exp_month) == 1 ? '0' . $exp_month : $exp_month;
        /*$exp_year = $cc_model->getExp_year() . '';
        $exp_year = substr($exp_year, -2, 2);*/

        if ($cc_model->getForAutoship() == 'y') {
            $ecommerce_flag = '2';
        }else {
            $ecommerce_flag = '7';
        }

        $payment->setAmount($amount)
            ->setToken($cc_model->getToken())
            //->setExpire_date($exp_month . $exp_year)
            ->setExpire_month($exp_month)
            ->setExpire_year($cc_model->getExp_year())
            ->setFull_name($cc_model->getFull_name())
            ->setPay_method($cc_model->getCard_type())
            ->setEcommerce_flag($ecommerce_flag);

        //proccess the payment
        $result = $method->autoship_capture($payment, $amount);
        if ($result->getResponseCode() != Developmint_Autoship_Model_Autoshipfirstdatagge4::GGE4_AUTOSHIP_CAPTURE_SUCCESS) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            if ($customer->getId() && $customer->getId() == 136463) {
                Mage::throwException($result->getResponseReasonText());
            }else {
                Mage::throwException('An error occurred while processing the saved credit card');
            }
        }

        $payment
            ->setTransactionId($result->getTransactionId())
            ->setIsTransactionClosed(0);

        //update the token
        $cc_model->setToken($result->getTransarmorToken());
        $cc_model->save();

        return $this;
    }

    /**
     * Check refund availability
     *
     * @return bool
     */
    public function canRefund()
    {
        return $this->_canRefund;
    }

    /**
     * called if refunding
     */
    public function refund(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshipfirstdatagge4();
        $result = $method->autoship_refund($payment->getRefundTransactionId(), $amount);

        if ($result->getResponseCode() != Developmint_Autoship_Model_Autoshipfirstdatagge4::GGE4_AUTOSHIP_CAPTURE_SUCCESS) {
            Mage::throwException($result->getResponseReasonText());
        }

        return $this;
    }

    /**
     * called if voiding a payment
     */
    /*
    public function void(Varien_Object $payment) {
        $method = new Developmint_Autoship_Model_Autoshippayflowpro();
        //$cc_id = $payment->getAdditionalInformation('saved_cc');
        //$cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        $result = $method->autoship_void($payment, $payment->getParentTransactionId());
        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_VOID_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        return $this;
    }*/
}
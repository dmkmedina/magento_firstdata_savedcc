<?php

class Developmint_Savedcc_Model_SavedPayflowPro extends Mage_Payment_Model_Method_Abstract
{
    protected $_formBlockType = 'developmint_savedcc/form_savedcc';

    /**
     * unique internal payment method identifier
     */
    protected $_code = 'payflowprosavedcc';

    /**
     * this should probably be true if you're using this
     * method to take payments
     */
    protected $_isGateway               = true;

    /**
     * can this method authorise?
     */
    protected $_canAuthorize            = true;

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
     * can this method void transactions?
     */
    protected $_canVoid                 = true;

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
    public function authorize(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshippayflowpro();
        $cc_id = $payment->getAdditionalInformation('saved_cc');
        $cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        $result = $method->autoship_pnref_authorize($payment, $cc_model->getToken(), $amount);
        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_AUTH_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        //update the pnref
        $cc_model->setToken($payment->getTransactionId());
        $cc_model->save();

        return $this;
    }

    /**
     * this method is called if we are authorising AND
     * capturing a transaction
     */
    public function capture(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshippayflowpro();
        $cc_id = $payment->getAdditionalInformation('saved_cc');
        $cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        //proccess the payment
        $result = $method->autoship_capture($payment, $amount, $cc_model->getToken(), $cc_model->getCustomerId());
        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_CAPTURE_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        //update the pnref
        $cc_model->setToken($payment->getTransactionId());
        $cc_model->save();

        return $this;
    }

    /**
     * called if refunding
     */
    public function refund(Varien_Object $payment, $amount) {
        $method = new Developmint_Autoship_Model_Autoshippayflowpro();

        $result = $method->autoship_refund($payment, $amount, $payment->getParentTransactionId());

        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_REFUND_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        return $this;
    }

    /**
     * called if voiding a payment
     */
    public function void(Varien_Object $payment) {
        $method = new Developmint_Autoship_Model_Autoshippayflowpro();
        //$cc_id = $payment->getAdditionalInformation('saved_cc');
        //$cc_model = Mage::getModel('savedcc/savedcreditcards')->load($cc_id);

        $result = $method->autoship_void($payment, $payment->getParentTransactionId());
        if ($result != Developmint_Autoship_Model_Autoshippayflowpro::AUTOSHIP_VOID_SUCCESS) {
            Mage::throwException($payment->getAdditionalInformation('status_msg'));
        }

        return $this;
    }
}
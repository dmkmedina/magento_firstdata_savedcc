<?php

class Developmint_Savedcc_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $this->loadLayout();
        $this->_initLayoutMessages('catalog/session');

        $this->getLayout()->getBlock('head')->setTitle($this->__('My Saved Credit Cards'));

        if ($block = $this->getLayout()->getBlock('customer.account.link.back')) {
            $block->setRefererUrl($this->_getRefererUrl());
        }

        $this->renderLayout();
    }

    /*
    * Called when the user attempts to remove a saved credit card
    */
    public function removeAction() {
        $params = $this->getRequest()->getParams();
        $customer_id = Mage::getSingleton('customer/session')->getCustomer()->getId();

        //Perform security checks to ensure the customer is allowed to remove this credit card
        if (!(isset($params['cc_id']))) {
            $this->_getSession()->addError($this->__('The requested URL does not appear to be valid.'));
            return $this->_redirect('*/*/index');
        }

        $cc = Mage::getModel('savedcc/savedcreditcards')->load($params['cc_id']);
        if ($cc->getCustomer_id() != $customer_id) {
            $this->_getSession()->addError($this->__('The requested URL does not appear to be valid.'));
            return $this->_redirect('*/*/index');
        }

        $str = 'The credit card ending in '.$cc->getLast4().' was successfully removed.';
        $cc->delete();

        $this->_getSession()->addSuccess($this->__($str));

        return $this->_redirect('*/*/index');
    }

    protected function _getSession() {
        return Mage::getSingleton('customer/session');
    }
}
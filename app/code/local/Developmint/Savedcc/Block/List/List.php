<?php

class Developmint_Savedcc_Block_List_List extends Mage_Core_Block_Template
{

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('developmint/savedcc/list.phtml');
        //$credit_cards = NULL;

        if(Mage::getSingleton('admin/session')->isLoggedIn()) {
            $credit_cards = Mage::getModel('savedcc/savedcreditcards')
                ->getCollection();
        } else if (!Mage::getSingleton('customer/session')->isLoggedIn()) {
            //$credit_cards = Mage::getModel('savedcc/savedcreditcards')
            //    ->getCollection();
            //$credit_cards = array();

            //If the user is not logged in, redirect them to the login page with
            //this page as the referer
            $referer = Mage::helper('core')->urlEncode($this->getUrl('*/*/*'));
            //echo $referer;
            Mage::app()->getResponse()->setRedirect($this->getUrl('customer/account/login', array('referer' => $referer)));
        } else {
            $credit_cards = Mage::getModel('savedcc/savedcreditcards')
                ->getCollection()
                ->addFilter('for_autoship', 'n')
                ->addFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId());
        }

        if ($credit_cards) {
            $this->setCreditCards($credit_cards);
        }

        Mage::app()->getFrontController()->getAction()->getLayout()->getBlock('root')->setHeaderTitle(Mage::helper('sales')->__('My Autoship Kits'));
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->getCreditCards()->load();

        return $this;
    }

    public function getViewUrl($order)
    {
        return $this->getUrl('*/*/view', array('order_id' => $order->getId()));
    }

    public function getRemoveUrl($cc)
    {
        return $this->getUrl('*/*/remove', array('cc_id' => $cc->getId()));
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    public function getMessages() {
        $output = '';
        $session = $this->_getSession();
        $items = $session->getMessages(true)->getItems();

        $output .= '<ul class="messages">';

        foreach($items as $item) {
            //print_r($item);
            $output .= '<li class="'.$item->getType().'-msg"><ul><li><span>';
            $output .= $item->getText();
            $output .= '</span></li></ul></li></ul>';
        }

        $output .= '</ul>';
        return $output;
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcTypes()
    {
        $types = Mage::getSingleton('payment/config')->getCcTypes();

        return $types;
    }
}

<?php

class Developmint_Savedcc_Model_Observer {

    public function removeLegacyEntry($observer) {
        $order = $observer->getEvent()->getOrder();
        $customer_id = $order->getCustomerId();

        Mage::helper('savedcc')->removeLegacyEntry($customer_id);
    }
}
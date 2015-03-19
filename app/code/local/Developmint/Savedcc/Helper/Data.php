<?php
class Developmint_Savedcc_Helper_Data extends Mage_Core_Helper_Abstract {

    public function hasLegacySaved($customer_id) {
        $items = Mage::getModel('savedcc/savedcclegacy')
            ->getCollection()
            ->addFilter('customer_id', $customer_id);

        foreach ($items as $item) {
            if ($item->getHas_saved() == 'y') {
                return true;
            }
        }

        return false;
    }

    public function customerHasLegacySaved() {
        $customer = Mage::getSingleton('customer/session');

        if ($customer->isLoggedIn() && $customer->getId()) {
            $items = Mage::getModel('savedcc/savedcclegacy')
                ->getCollection()
                ->addFilter('customer_id', $customer->getId());

            foreach ($items as $item) {
                if ($item->getHas_saved() == 'y') {
                    return true;
                }
            }
        }

        return false;
    }

    public function removeLegacyEntry($customerid) {
        $items = Mage::getModel('savedcc/savedcclegacy')
            ->getCollection()
            ->addFilter('customer_id', $customerid);

        foreach ($items as $item) {
            $item->delete();
        }
    }
}
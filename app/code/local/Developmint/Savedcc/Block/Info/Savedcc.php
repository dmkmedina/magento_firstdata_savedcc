<?php
class Developmint_Savedcc_Block_Info_Savedcc extends Mage_Payment_Block_Info
{
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $info = $this->getInfo();
        $data = $info->getAdditionalInformation();
        $cc_model = Mage::getModel('savedcc/savedcreditcards')->load($data['saved_cc']);
        $card_type = '';
        $card_num = '';

        if ($cc_model) {
            $card_type = Mage::helper('autoshipemails')->getMethodName($cc_model->getCardType());
            $card_num =  'XXXX-'.$cc_model->getLast4();
        }

        $transport = new Varien_Object();
        $transport = parent::_prepareSpecificInformation($transport);
        $transport->addData(array(
            Mage::helper('payment')->__('Type') => $card_type,
            Mage::helper('payment')->__('Number') => $card_num
        ));
        return $transport;
    }
}
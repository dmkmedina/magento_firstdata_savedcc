<?php

class Developmint_Savedcc_Model_Resource_Savedcclegacy extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('savedcc/savedcclegacy', 'id');
    }
}
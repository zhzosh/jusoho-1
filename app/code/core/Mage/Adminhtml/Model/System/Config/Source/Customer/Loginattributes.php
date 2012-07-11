<?php
class Mage_Adminhtml_Model_System_Config_Source_Customer_Loginattributes
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'username', 'label'=>Mage::helper('adminhtml')->__('Username')),
            array('value' => 'phone', 'label'=>Mage::helper('adminhtml')->__('Phone')),
        );
    }
}

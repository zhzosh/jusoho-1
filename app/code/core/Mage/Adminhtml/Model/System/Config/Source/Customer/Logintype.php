<?php
class Mage_Adminhtml_Model_System_Config_Source_Customer_Logintype
{
    public function toOptionArray()
    {
        return array(
            array('value' => 'email', 'label'=> Mage::helper('adminhtml')->__('Allow email login')),
            array('value' => 'username', 'label'=>Mage::helper('adminhtml')->__('Allow username login')),
            array('value' => 'phone', 'label'=>Mage::helper('adminhtml')->__('Allow phone login')),
        );
    }
}

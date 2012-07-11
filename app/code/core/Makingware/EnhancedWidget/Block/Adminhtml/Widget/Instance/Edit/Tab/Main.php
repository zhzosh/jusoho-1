<?php

class Makingware_EnhancedWidget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main extends Mage_Widget_Block_Adminhtml_Widget_Instance_Edit_Tab_Main
{
    protected function _prepareForm ()
    {
        $widgetInstance = $this->getWidgetInstance();
        
        $form = new Varien_Data_Form(
            array('id' => 'edit_form', 'action' => $this->getData('action'), 
        	'method' => 'post')
        );
        
        $fieldset = $form->addFieldset('base_fieldset', 
            array('legend' => Mage::helper('widget')->__('Frontend Properties'))
        );
        
        if ($widgetInstance->getId()) {
            $fieldset->addField('instance_id', 'hidden', 
            array('name' => 'isntance_id'));
        }
        
        $this->_addElementTypes($fieldset);
        
        $fieldset->addField('type', 'select', 
            array('name' => 'type', 'label' => Mage::helper('widget')->__('Type'), 
        	'title' => Mage::helper('widget')->__('Type'), 'class' => '', 
        	'values' => $this->getTypesOptionsArray(), 'disabled' => true)
        );
        
        $fieldset->addField('package_theme', 'select', 
            array('name' => 'package_theme', 
        	'label' => Mage::helper('widget')->__('Design Package/Theme'), 
        	'title' => Mage::helper('widget')->__('Design Package/Theme'), 
        	'required' => false, 'values' => $this->getPackegeThemeOptionsArray(), 
        	'disabled' => true)
        );
        
        $fieldset->addField('title', 'text', 
            array('name' => 'title', 
        	'label' => Mage::helper('widget')->__('Widget Instance Title'), 
        	'title' => Mage::helper('widget')->__('Widget Instance Title'), 
        	'class' => '', 'required' => true)
        );
        
        if (! Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_ids', 'multiselect', 
                array('name' => 'store_ids[]', 
            	'label' => Mage::helper('widget')->__('Assign to Store Views'), 
            	'title' => Mage::helper('widget')->__('Assign to Store Views'), 
            	'required' => true, 
            	'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(
                false, true))
            );
        }
        
        $fieldset->addField('sort_order', 'text', 
            array('name' => 'sort_order', 
        	'label' => Mage::helper('widget')->__('Sort Order'), 
        	'title' => Mage::helper('widget')->__('Sort Order'), 'class' => '', 
        	'required' => false, 
        	'note' => Mage::helper('widget')->__(
        	'Sort Order of widget instances in the same block reference'))
        );
        
        $fieldset->addField('before', 'text', 
            array('name' => 'before', 
        	'label' => Mage::helper('widget')->__('Before'), 
        	'title' => Mage::helper('widget')->__('Before'), 'class' => '', 
        	'required' => false)
        );
       
        $layoutBlock = $this->getLayout()
            ->createBlock(
        		'widget/adminhtml_widget_instance_edit_tab_main_layout')
            ->setWidgetInstance($widgetInstance);
            
        $fieldset = $form->addFieldset('layout_updates_fieldset', 
            array('legend' => Mage::helper('widget')->__('Layout Updates')));
            
        $fieldset->addField('layout_updates', 'note', array());
        $form->getElement('layout_updates_fieldset')->setRenderer($layoutBlock);
        $this->setForm($form);
    }
}

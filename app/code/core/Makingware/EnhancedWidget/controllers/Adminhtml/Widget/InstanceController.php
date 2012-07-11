<?php
require_once 'Mage' . DS . 'Widget' . DS . 'controllers' . DS . 'Adminhtml' . DS . 'Widget' . DS . 'InstanceController.php';

class Makingware_EnhancedWidget_Adminhtml_Widget_InstanceController extends Mage_Widget_Adminhtml_Widget_InstanceController
{
    /**
     * Rewrite save action
     *
     */
    public function saveAction ()
    {
        $widgetInstance = $this->_initWidgetInstance();
        
        if (! $widgetInstance) {
            $this->_redirect('*/*/');
            return;
        }
        
        $widgetInstance->setTitle($this->getRequest()->getPost('title'))
            ->setStoreIds($this->getRequest()->getPost('store_ids', array(0)))
            ->setSortOrder($this->getRequest()->getPost('sort_order', 0))
            ->setPageGroups($this->getRequest()->getPost('widget_instance'))
            ->setBefore($this->getRequest()->getPost('before', ''))
            ->setWidgetParameters($this->getRequest()->getPost('parameters'));
            
        try {
            $widgetInstance->save();
            
            $this->_getSession()->addSuccess(
                Mage::helper('widget')->__(
            	'Widget instance has been successfully saved.')
            );
            
            if ($this->getRequest()->getParam('back', false)) {
                $this->_redirect('*/*/edit', 
                array('instance_id' => $widgetInstance->getId(), 
                '_current' => true));
            } else {
                $this->_redirect('*/*/');
            }
            
            return;
        } catch (Exception $e) {
            $this->_getSession()->addError($e->getMessage());
            $this->_redirect('*/*/edit', array('_current' => true));
            
            return;
        }
        
        $this->_redirect('*/*/');
        
        return;
    }
}
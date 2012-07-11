<?php

class Makingware_EnhancedWidget_Model_Mysql4_Widget_Instance extends Mage_Widget_Model_Mysql4_Widget_Instance
{
     /**
      *rewrite Mage_Widget_Model_Widget_Instance
      *
      */
    protected function _saveLayoutUpdates ($widgetInstance, $pageGroupData)
    {
        $write = $this->_getWriteAdapter();
        $pageLayoutUpdateIds = array();
        $storeIds = $this->_prepareStoreIds($widgetInstance->getStoreIds());
        
        foreach ($pageGroupData['layout_handle_updates'] as $handle) {
            $this->_getWriteAdapter()->insert(
            $this->getTable('core/layout_update'), 
            array('handle' => $handle, 
            'xml' => $widgetInstance->generateLayoutUpdateXml(
            $pageGroupData['block_reference'], $pageGroupData['template'], 
            $widgetInstance->getBefore()), 
            'sort_order' => $widgetInstance->getSortOrder()));
            $layoutUpdateId = $this->_getWriteAdapter()->lastInsertId();
            $pageLayoutUpdateIds[] = $layoutUpdateId;
            
            foreach ($storeIds as $storeId) {
                $this->_getWriteAdapter()->insert(
                    $this->getTable('core/layout_link'), 
                    array('store_id' => $storeId, 
                	'area' => $widgetInstance->getArea(), 
                	'package' => $widgetInstance->getPackage(), 
                	'theme' => $widgetInstance->getTheme(), 
                	'layout_update_id' => $layoutUpdateId)
                );
            }
        }
        
        return $pageLayoutUpdateIds;
    }
}

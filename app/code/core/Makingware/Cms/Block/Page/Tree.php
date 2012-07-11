<?php
class Makingware_Cms_Block_Page_Tree extends Mage_Core_Block_Template
{
	protected $_page = null;
	
	public function getPage()
    {
        if (!$this->hasData('page')) {
        	$page = Mage::getSingleton('cms/page');
            if ($this->getPageId()) {
                $page->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getPageId());
            }elseif ($this->getIdentifier()) {
                $page->setStoreId(Mage::app()->getStore()->getId())
                    ->load($this->getIdentifier(), 'identifier');
            }
            $this->setData('page', $page);
        }
        return $this->getData('page');
    }
    
    protected function getParentId($parentId = null)
    {
    	if (is_null($parentId)) {
    		if ($id = $this->getPage()->getId()) {
    			$parentId = $id;
    		}
    	}
    	empty($parentId) && $parentId = 0;
    	
    	static $data = array();	
    	if (!isset($data[$parentId])) {
    		$data[$parentId] = $parentId;
	    	if (is_string($parentId)) {
	    		$page = Mage::getModel('cms/page')->setStoreId(Mage::app()->getStore()->getId())->load($parentId, 'identifier');
	    		if ($page->getId()) {
	    			$data[$parentId] = $page->getId();
	    		}
	    	}
	    	is_numeric($data[$parentId]) or $data[$parentId] = 0;
    	}
    	return $data[$parentId];
    }
	
	public function getChildren($parentId = null)
    {
    	$parentId = $this->getParentId($parentId);
    	
    	static $children = array();
    	if (empty($children[$parentId])) {
	        $children[$parentId] = $this->getPage()->getCollection()
	            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
	            ->addFieldToFilter('parent_id', $parentId)
	            ->setOrder('position', Varien_Data_Collection::SORT_ORDER_ASC);
    	}
        return $children[$parentId];
    }

    public function getChildrenByCreateAt($parentId = null)
    {
    	$parentId = $this->getParentId($parentId);
    	
    	static $children = array();
    	if (empty($children[$parentId])) {
	        $children[$parentId] = $this->getPage()->getCollection()
	            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
	            ->addFieldToFilter('parent_id', $parentId)
	            ->setOrder('creation_time', Varien_Data_Collection::SORT_ORDER_DESC);
    	}
        return $children[$parentId];
    }

    public function getChildrenByUpdateAt($parentId = null)
    {
    	$parentId = $this->getParentId($parentId);
    	
    	static $children = array();
    	if (empty($children[$parentId])) {
	        $children[$parentId] = $this->getPage()->getCollection()
	            ->addFieldToFilter('store_id', Mage::app()->getStore()->getId())
	            ->addFieldToFilter('parent_id', $parentId)
	            ->setOrder('update_time', Varien_Data_Collection::SORT_ORDER_DESC);
    	}
        return $children[$parentId];
    }
}

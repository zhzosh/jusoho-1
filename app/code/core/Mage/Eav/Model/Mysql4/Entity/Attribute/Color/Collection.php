<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Eav
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Entity attribute option collection
 *
 * @category   Mage
 * @package    Mage_Eav
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Eav_Model_Mysql4_Entity_Attribute_Color_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_optionValueTable;

    public function _construct()
    {
        $this->_init('eav/entity_attribute_color');
        $this->_optionValueTable = Mage::getSingleton('core/resource')->getTableName('eav/attribute_image_option_value');
        $this->_optionProductPic = Mage::getSingleton('core/resource')->getTableName('eav_attribute_image_option_product_pic');

    }

    public function setAttributeFilter($setId)
    {
        $this->getSelect()->where('main_table.attribute_id=?', $setId);
        return $this;
    }
    
    public function setOptionIdFilter($id)
    {
        $this->getSelect()->where('main_table.option_id=?', $id);
        return $this;
    }
    public function setProductIdFilter($id)
    {
        $this->getSelect()->where('product_pic.product_id=?', $id);
        return $this;
    }

    public function setStoreFilter($storeId=null, $useDefaultValue=true)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $sortBy = 'store_default_value';
        if ($useDefaultValue) {
            $this->getSelect()
                ->join(array('store_default_value'=>$this->_optionValueTable),
                    'store_default_value.option_id=main_table.option_id',
                    array('default_value'=>'value','colorValue'=>'color_value','imageUrl'=>'image_url')
                    )
                ->joinLeft(array('product_pic'=> $this->_optionProductPic),
                    'product_pic.option_id=main_table.option_id',
                    array('color_text'=>'color_text','color_pic'=>'color_pic')
                    )
                ->joinLeft(array('store_value'=>$this->_optionValueTable),
                    'store_value.option_id=main_table.option_id AND '.$this->getConnection()->quoteInto('store_value.store_id=?', $storeId),
                    array('store_value'=>'value',
                    'value' => new Zend_Db_Expr('IF(store_value.value_id>0, store_value.value,store_default_value.value)')))

                ->where($this->getConnection()->quoteInto('store_default_value.store_id=?', 0));

        }
        else {
            $sortBy = 'store_value';
            $this->getSelect()
                ->joinLeft(array('store_value'=>$this->_optionValueTable),
                    'store_value.option_id=main_table.option_id AND '.$this->getConnection()->quoteInto('store_value.store_id=?', $storeId),
                    'value')
                ->joinLeft(array('product_pic'=> $this->_optionProductPic),
                    'product_pic.option_id=main_table.option_id ')
                ->where($this->getConnection()->quoteInto('store_value.store_id=?', $storeId));
        }
        $this->setOrder("{$sortBy}.value", 'ASC');

        return $this;
    }

    public function setAllStoreFilter($storeId=null, $useDefaultValue=true)
    {
        if (is_null($storeId)) {
            $storeId = Mage::app()->getStore()->getId();
        }
        $sortBy = 'store_default_value';
        if ($useDefaultValue) {
            $this->getSelect()
                ->join(array('store_default_value'=>$this->_optionValueTable),
                    'store_default_value.option_id=main_table.option_id',
                    array('default_value'=>'value','colorValue'=>'color_value','imageUrl'=>'image_url')
                    )
                ->joinLeft(array('store_value'=>$this->_optionValueTable),
                    'store_value.option_id=main_table.option_id AND '.$this->getConnection()->quoteInto('store_value.store_id=?', $storeId),
                    array('store_value'=>'value',
                    'value' => new Zend_Db_Expr('IF(store_value.value_id>0, store_value.value,store_default_value.value)')))

                ->where($this->getConnection()->quoteInto('store_default_value.store_id=?', 0));

        }
        else {
            $sortBy = 'store_value';
            $this->getSelect()
                ->joinLeft(array('store_value'=>$this->_optionValueTable),
                    'store_value.option_id=main_table.option_id AND '.$this->getConnection()->quoteInto('store_value.store_id=?', $storeId),
                    'value')
                ->where($this->getConnection()->quoteInto('store_value.store_id=?', $storeId));
        }
        $this->setOrder("{$sortBy}.value", 'ASC');

        return $this;
    }


    public function setIdFilter($id)
    {
        if (is_array($id)) {
            $this->getSelect()->where('main_table.option_id IN (?)', $id);
        }
        else {
            $this->getSelect()->where('main_table.option_id=?', $id);
        }
        return $this;
    }

    public function toOptionArray($valueKey = 'value')
    {
        return $this->_toOptionArray('option_id', $valueKey);
    }

    public function toAllOptionArray($valueKey = 'value')
    {
        return $this->_toOptionArray('option_id', $valueKey,array('colorValue'=>'colorValue','imageUrl'=>'imageUrl','color_text'=>'color_text','color_pic'=>'color_pic'));
    }

    public function setPositionOrder($dir = 'ASC', $sortAlpha = false)
    {
        $this->setOrder('main_table.sort_order', $dir);
        // sort alphabetically by values in admin
        if ($sortAlpha) {
            $this->getSelect()->joinLeft(array('sort_alpha_value' => $this->_optionValueTable),
                'sort_alpha_value.option_id=main_table.option_id AND sort_alpha_value.store_id=0'
            );
            $this->setOrder('sort_alpha_value.value', $dir);
        }
        return $this;
    }
}

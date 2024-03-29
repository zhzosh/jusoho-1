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


class Mage_Eav_Model_Entity_Attribute_Source_Color extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Default values for option cache
     *
     * @var array
     */
    protected $_optionsDefault = array();

    /**
     * Retrieve Full Option values array
     *
     * @param bool $withEmpty       Add empty option to array
     * @param bool $defaultValues
     * @return array
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false,$productId=0,$type=0)
    {
        $storeId = $this->getAttribute()->getStoreId();
        if (!is_array($this->_options)) {
            $this->_options = array();
        }
        if (!is_array($this->_optionsDefault)) {
            $this->_optionsDefault = array();
        }
        $collection = Mage::getResourceModel('eav/entity_attribute_color_collection');
          
        if(empty($type) && !empty($productId)){
             $collection->setProductIdFilter($productId);
             $collection->setStoreFilter($this->getAttribute()->getStoreId());

         }else{
            $collection->setAllStoreFilter($this->getAttribute()->getStoreId());
         }
        
        $collection->setPositionOrder('asc')
             ->setAttributeFilter($this->getAttribute()->getId())
             ->load();
        
        $this->_options[$storeId]= $collection->toAllOptionArray();
        $this->_optionsDefault[$storeId] = $collection->toAllOptionArray('default_value');
        $options = ($defaultValues ? $this->_optionsDefault[$storeId] : $this->_options[$storeId]);
        
        if ($withEmpty) {
            array_unshift($options, array('label'=>'', 'value'=>''));
        }

        return $options;
    }

    /**
     * Get a text for option value
     *
     * @param string|integer $value
     * @return string
     */
    public function getOptionText($value)
    {
        $isMultiple = false;
        if (strpos($value, ',')) {
            $isMultiple = true;
            $value = explode(',', $value);
        }

        $options = $this->getAllOptions(false);

        if ($isMultiple) {
            $values = array();
            foreach ($options as $item) {
                if (in_array($item['value'], $value)) {
                    $values[] = $item['label'];
                }
            }
            return $values;
        }
        else {
            foreach ($options as $item) {
                if ($item['value'] == $value) {
                    return $item['label'];
                }
            }
            return false;
        }
    }

    /**
     * Add Value Sort To Collection Select
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @param string $dir
     *
     * @return Mage_Eav_Model_Entity_Attribute_Source_Table
     */
    public function addValueSortToCollection($collection, $dir = 'asc')
    {
        $valueTable1    = $this->getAttribute()->getAttributeCode() . '_t1';
        $valueTable2    = $this->getAttribute()->getAttributeCode() . '_t2';
        $collection->getSelect()
            ->joinLeft(
                array($valueTable1 => $this->getAttribute()->getBackend()->getTable()),
                "`e`.`entity_id`=`{$valueTable1}`.`entity_id`"
                . " AND `{$valueTable1}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$valueTable1}`.`store_id`='0'",
                array())
            ->joinLeft(
                array($valueTable2 => $this->getAttribute()->getBackend()->getTable()),
                "`e`.`entity_id`=`{$valueTable2}`.`entity_id`"
                . " AND `{$valueTable2}`.`attribute_id`='{$this->getAttribute()->getId()}'"
                . " AND `{$valueTable2}`.`store_id`='{$collection->getStoreId()}'",
                array()
            );
        $valueExpr = new Zend_Db_Expr("IF(`{$valueTable2}`.`value_id`>0, `{$valueTable2}`.`value`, `{$valueTable1}`.`value`)");

        Mage::getResourceModel('eav/entity_attribute_color')
            ->addOptionValueToCollection($collection, $this->getAttribute(), $valueExpr);

        $collection->getSelect()
            ->order("{$this->getAttribute()->getAttributeCode()} {$dir}");

        return $this;
    }

    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        $columns = array();
        $isMulti = $this->getAttribute()->getFrontend()->getInputType() == 'multiselect';
        $columns[$this->getAttribute()->getAttributeCode()] = array(
            'type'      => $isMulti ? 'varchar(255)' : 'int',
            'unsigned'  => false,
            'is_null'   => true,
            'default'   => null,
            'extra'     => null
        );

        if (!$isMulti) {
            $columns[$this->getAttribute()->getAttributeCode() . '_value'] = array(
                'type'      => 'varchar(255)',
                'unsigned'  => false,
                'is_null'   => true,
                'default'   => null,
                'extra'     => null
            );
        }

        return $columns;
    }

    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        $indexes = array();

        $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode());
        $indexes[$index] = array(
            'type'      => 'index',
            'fields'    => array($this->getAttribute()->getAttributeCode())
        );

        $sortable   = $this->getAttribute()->getUsedForSortBy();
        if ($sortable and $this->getAttribute()->getFrontend()->getInputType() != 'multiselect') {
            $index = 'IDX_' . strtoupper($this->getAttribute()->getAttributeCode()) . '_VALUE';
            $indexes[$index] = array(
                'type'      => 'index',
                'fields'    => array($this->getAttribute()->getAttributeCode() . '_value')
            );
        }

        return $indexes;
    }

    /**
     * Retrieve Select For Flat Attribute update
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return Mage::getResourceModel('eav/entity_attribute_color')
            ->getFlatUpdateSelect($this->getAttribute(), $store);
    }

    public function saveData($optionId,$productId)
    {    
          $write = Mage::getSingleton('core/resource')->getConnection('core_write');
          $colorTable=Mage::getSingleton('core/resource')->getTableName('eav_attribute_image_option_product_pic');
          $select= $write->select()
                  ->from($colorTable,'pic_id')
                  ->where($write->quoteInto('option_id=?',$optionId))
                  ->where($write->quoteInto('product_id=?',$productId))->__toString();
          $info=$write->fetchRow($select);
        
          if(!empty($info)){
              return ;
          }

          $dataVal=array('option_id'=>$optionId,'product_id'=>$productId);
          $write->insert($colorTable, $dataVal);
    }
}

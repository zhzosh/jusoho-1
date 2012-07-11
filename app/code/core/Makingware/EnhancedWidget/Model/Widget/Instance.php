<?php

class Makingware_EnhancedWidget_Model_Widget_Instance extends Mage_Widget_Model_Widget_Instance
{
    public function generateLayoutUpdateXml ($blockReference, $templatePath = '', $beforeValue = '')
    {
        $templateFilename = Mage::getSingleton('core/design_package')->getTemplateFilename(
            $templatePath, 
            array('_area' => $this->getArea(), '_package' => $this->getPackage(), 
        	'_theme' => $this->getTheme())
        );
        
        if (! $this->getId() && ! $this->isCompleteToCreate() ||
            ($templatePath && ! is_readable($templateFilename))) {
            return '';
        }
        
        $parameters = $this->getWidgetParameters();
        $xml = '<reference name="' . $blockReference . '">';
        $template = '';
        
        if (isset($parameters['template'])) {
            unset($parameters['template']);
        }
        
        if ($templatePath) {
            $template = ' template="' . $templatePath . '"';
        }
        
        $before = '';
        
        if ($beforeValue) {
            $before = ' before="' . $beforeValue . '"';
        }
        
        $xml .= '<block type="' . $this->getType() . '" name="' .
         Mage::helper('core')->uniqHash() . '"' . $template . $before . '>';
         
        foreach ($parameters as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            
            if ($name && strlen((string) $value)) {
                $xml .= '<action method="setData"><name>' . $name .
                 '</name><value>' . Mage::helper('widget')->htmlEscape($value) .
                 '</value></action>';
            }
        }
        
        $xml .= '</block></reference>';
        
        return $xml;
    }
}

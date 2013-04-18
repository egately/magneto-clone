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
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Layout move change model
 */
class Mage_DesignEditor_Model_Change_Layout_Move extends Mage_DesignEditor_Model_Change_LayoutAbstract
{
    /**
     * Layout directive associated with this change
     */
    const LAYOUT_DIRECTIVE_MOVE = 'move';

    /**
     * Class constructor
     *
     * @param Varien_Simplexml_Element|array $data
     */
    public function __construct($data = array())
    {
        if ($data instanceof Varien_Simplexml_Element) {
            $data = $this->_getAttributes($data);
        }

        parent::__construct($data);
    }

    /**
     * Get required data fields for move layout change
     *
     * @return array
     */
    protected function _getRequiredFields()
    {
        $requiredFields = parent::_getRequiredFields();
        $requiredFields[] = 'destination_container';
        $requiredFields[] = 'destination_order';
        $requiredFields[] = 'origin_container';
        $requiredFields[] = 'origin_order';

        return $requiredFields;
    }

    /**
     * Get data to render layout update directive
     *
     * @return array
     */
    public function getLayoutUpdateData()
    {
        return array(
            'element'     => $this->getData('element') ?: $this->getData('element_name'),
            'after'       => $this->getData('after') ?: $this->getData('destination_order'),
            'destination' => $this->getData('destination') ?: $this->getData('destination_container')
        );
    }

    /**
     * Get layout update directive for given layout change
     *
     * @return string
     */
    public function getLayoutDirective()
    {
        return self::LAYOUT_DIRECTIVE_MOVE;
    }

    /**
     * Get attributes from XML layout update
     *
     * @param Varien_Simplexml_Element $layoutUpdate
     * @return array
     */
    protected function _getAttributes(Varien_Simplexml_Element $layoutUpdate)
    {
        $attributes = array();
        if ($layoutUpdate->getAttribute('element') !== null) {
            $attributes['element_name'] = $layoutUpdate->getAttribute('element');
        }
        if ($layoutUpdate->getAttribute('after') !== null) {
            $attributes['origin_order'] = $attributes['destination_order'] = $layoutUpdate->getAttribute('after');
        }
        if ($layoutUpdate->getAttribute('destination') !== null) {
            $attributes['origin_container'] = $attributes['destination_container']
                = $layoutUpdate->getAttribute('destination');
        }
        $attributes = array_merge($attributes, parent::_getAttributes($layoutUpdate));

        return $attributes;
    }
}

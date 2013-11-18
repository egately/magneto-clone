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
 * @category    Magento
 * @package     Magento_Core
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

return array(
    '$replaceRules' => array(
        array(
            'table',
            'field',
            \Magento\Core\Model\Resource\Setup\Migration::ENTITY_TYPE_BLOCK,
            \Magento\Core\Model\Resource\Setup\Migration::FIELD_CONTENT_TYPE_WIKI
        )
    ),
    '$tableData' => array(
        array('field' => '<p>{{widget type="productalert/product_view"}}</p>'),
        array('field' => '<p>{{widget type="catalogSearch/result"}}</p>'),
        array('field' => '<p>Some HTML code</p>'),
    ),
    '$expected' => array(
        'updates' => array(
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<p>{{widget type="Magento\ProductAlert\Block\Product\View"}}</p>',
                'from'  => array('`field` = ?' => '<p>{{widget type="productalert/product_view"}}</p>')
            ),
            array(
                'table' => 'table',
                'field' => 'field',
                'to'    => '<p>{{widget type="Magento\CatalogSearch\Block\Result"}}</p>',
                'from'  => array('`field` = ?' => '<p>{{widget type="catalogSearch/result"}}</p>')
            ),
        ),
        'aliases_map' => array(
            \Magento\Core\Model\Resource\Setup\Migration::ENTITY_TYPE_BLOCK => array(
                'productalert/product_view' => 'Magento\ProductAlert\Block\Product\View',
                'catalogSearch/result'      => 'Magento\CatalogSearch\Block\Result',
            )
        )
    ),
);

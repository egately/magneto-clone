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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Access Control List Builder. Retrieves required role/rule/resource loaders from configuration and uses them
 * to populate provided ACL object. If loaders are not defined - default loader is used that does not do anything
 * to ACL
 */
class Mage_Core_Model_Acl_Builder
{
    /**
     * Acl object
     *
     * @var Magento_Acl[]
     */
    protected $_aclPool;

    /**
     * Acl loader list
     *
     * @var Mage_Core_Model_Acl_LoaderPool
     */
    protected $_loaderPool;

    /**
     * @param Magento_AclFactory $aclFactory
     * @param Mage_Core_Model_Acl_LoaderPool $loaderPool
     */
    public function __construct(Magento_AclFactory $aclFactory, Mage_Core_Model_Acl_LoaderPool $loaderPool)
    {
        $this->_aclFactory = $aclFactory;
        $this->_loaderPool = $loaderPool;
    }

    /**
     * Build Access Control List
     *
     * @param string areaCode
     * @return Magento_Acl
     * @throws LogicException
     */
    public function getAcl($areaCode)
    {
        if (!isset($this->_aclPool[$areaCode])) {
            try {
                $this->_aclPool[$areaCode] = $this->_aclFactory->create();
                /** @var $loader Magento_Acl_Loader */
                foreach ($this->_loaderPool->getLoadersByArea($areaCode) as $loader) {
                    $loader->populateAcl($this->_aclPool[$areaCode]);
                }
            } catch (Exception $e) {
                throw new LogicException('Could not create acl object: ' . $e->getMessage());
            }
        }
        return $this->_aclPool[$areaCode];
    }
}

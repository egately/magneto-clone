<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend add store code to url backend
 */
namespace Magento\Config\Model\Config\Backend;

class Store extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\App\Config\MutableScopeConfigInterface
     */
    protected $_mutableConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig
     * @param \Magento\Framework\Model\ModelResource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Config\MutableScopeConfigInterface $mutableConfig,
        \Magento\Framework\Model\ModelResource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
        $this->_mutableConfig = $mutableConfig;
    }

    /**
     * @return void
     */
    public function afterSave()
    {
        $this->_mutableConfig->setValue(
            \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
            $this->getValue(),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->_cacheManager->clean();
    }
}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Class ScopeResolver
 */
class ScopeResolver
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    public function __construct(
        ObjectManagerInterface $objectManager,
        MetadataPool $metadataPool
    ) {
        $this->objectManager = $objectManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @return \Magento\Framework\Model\Entity\ScopeInterface[]
     * @throws ConfigurationMismatchException
     * @throws \Exception
     */
    public function getEntityContext($entityType)
    {
        $entityContext = [];
        $metadata = $this->metadataPool->getMetadata($entityType);
        foreach ($metadata->getEntityContext() as $contextProviderClass) {
            $contextProvider =  $this->objectManager->get($contextProviderClass);
            if (!$contextProvider instanceof ScopeProviderInterface) {
                throw new ConfigurationMismatchException(__('Wrong configuration for type' . $entityType));
            }
            $entityContext[] = $contextProvider->getContext();
        }
        return $entityContext;
    }
}

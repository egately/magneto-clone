<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Resource;

class SourceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Get source class instance by class name
     *
     * @param string $className
     * @throws \InvalidArgumentException
     * @return SourceProviderInterface
     */
    public function create($className)
    {
        $source = $this->objectManager->create($className);
        if (!$source instanceof SourceProviderInterface) {
            throw new \InvalidArgumentException(
                $className . ' doesn\'t implement \Magento\Framework\App\Resource\SourceProviderInterface'
            );
        }

        return $source;
    }
}

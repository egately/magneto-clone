<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\StoreGraphQl\Plugin;

use Magento\Store\Model\Group as ModelGroup;
use Magento\StoreGraphQl\Model\Resolver\Store\ConfigIdentity;

/**
 * Store group plugin to provide identities for cache invalidation
 */
class Group
{
    /**
     * Add graphql store config tag to the store group cache identities.
     *
     * @param ModelGroup $subject
     * @param array $result
     * @return array
     */
    public function afterGetIdentities(ModelGroup $subject, array $result): array
    {
        $storeIds = $subject->getStoreIds();
        if (count($storeIds) > 0) {
            foreach ($storeIds as $storeId) {
                $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, $storeId);
            }
            $origWebsiteId = $subject->getOrigData('website_id');
            $websiteId = $subject->getWebsiteId();
            if ($origWebsiteId != $websiteId) { // Add or switch to a new website
                $result[] = sprintf('%s_%s', ConfigIdentity::CACHE_TAG, 'website_' . $websiteId);
            }
        }

        return $result;
    }
}

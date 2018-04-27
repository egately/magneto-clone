<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App;

/**
 * Feed interface
 */
interface FeedInterface
{
    /**
     *
     * @return string
     */
    public function getFormattedContent();
}

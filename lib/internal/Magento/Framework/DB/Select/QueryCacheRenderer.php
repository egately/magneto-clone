<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Select;

use Magento\Framework\DB\Select;

/**
 * Class QueryCacheRenderer
 */
class QueryCacheRenderer implements RendererInterface
{
    /**
     * Render query cache related hints
     *
     * @param Select $select
     * @param string $sql
     * @return string
     */
    public function render(Select $select, $sql = '')
    {
        if ($select->getPart(Select::QUERY_CACHE) === Select::SQL_CACHE) {
            $sql .= ' ' . Select::SQL_CACHE  . ' ';
        } elseif ($select->getPart(Select::QUERY_CACHE) === Select::SQL_NO_CACHE) {
            $sql .= ' ' . Select::SQL_NO_CACHE  . ' ';
        }
        return $sql;
    }
}

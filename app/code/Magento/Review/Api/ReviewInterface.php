<?php
namespace Magento\Review\Api;

/**
 * Interface ReviewInterface
 * @api
 */
interface ReviewInterface
{
    /**
     * Return Added review item.
     *
     * @param int $productId
     * @return array
     *
     */
    public function getReviewsList($productId);

    /**
     * Added review item.
     * @param int $productId
     * @param string $title
     * @param string $nickname
     * @param string $detail
     * @param int $ratingValue
     * @param int $customer_id
     * @param int $store_id
     * @return boolean
     *
     */
    public function writeReviews(
        $productId,
        $nickname,
        $title,
        $detail,
        $ratingValue,
        $customer_id = null,
        $store_id = 1
    );
}

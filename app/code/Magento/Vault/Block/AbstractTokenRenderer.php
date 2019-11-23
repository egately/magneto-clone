<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\Customer\IconInterface;

/**
 * Class AbstractTokenRenderer
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.2.0
 */
abstract class AbstractTokenRenderer extends Template implements TokenRendererInterface, IconInterface
{
    /**
     * @var PaymentTokenInterface|null
     */
    private $token;

    /**
     * @var array|null
     */
    private $tokenDetails;

    /**
     * Renders specified token
     *
     * @param PaymentTokenInterface $token
     * @return string
     * @since 100.2.0
     */
    public function render(PaymentTokenInterface $token)
    {
        $this->token = $token;
        $this->tokenDetails = json_decode($this->getToken()->getTokenDetails() ?: '{}', true);
        return $this->toHtml();
    }

    /**
     * Get token
     *
     * @return PaymentTokenInterface|null
     * @since 100.2.0
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get token details
     *
     * @return array|null
     * @since 100.2.0
     */
    protected function getTokenDetails()
    {
        return $this->tokenDetails;
    }
}

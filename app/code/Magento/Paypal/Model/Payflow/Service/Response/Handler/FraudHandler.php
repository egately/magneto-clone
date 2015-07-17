<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow\Service\Response\Handler;

use Magento\Framework\Object;
use Magento\Payment\Model\InfoInterface;
use Magento\Paypal\Model\Info;
use Magento\Paypal\Model\Payflowpro;

class FraudHandler implements HandlerInterface
{
    /**
     * Response message code
     */
    const RESPONSE_MESSAGE = 'respmsg';

    /**
     * Fraud rules xml code
     */
    const FRAUD_RULES_XML = 'fps_prexmldata';

    /**
     * @var Info
     */
    private $paypalInfoManager;

    /**
     * @param Info $paypalInfoManager
     */
    public function __construct(Info $paypalInfoManager)
    {
        $this->paypalInfoManager = $paypalInfoManager;
    }

    /**
     * {inheritdoc}
     */
    public function handle(InfoInterface $payment, Object $response)
    {
        if (
        !in_array(
            $response->getData('result'),
            [
                Payflowpro::RESPONSE_CODE_DECLINED_BY_FILTER,
                Payflowpro::RESPONSE_CODE_FRAUDSERVICE_FILTER
            ]
        )) {
            return;
        }

        $fraudMessages = ['RESPMSG' => $response->getData(self::RESPONSE_MESSAGE)];
        if ($response->getData(self::FRAUD_RULES_XML)) {
            $fraudMessages = array_merge(
                $fraudMessages,
                $this->getFraudRulesDictionary($response->getData(self::FRAUD_RULES_XML))
            );
        }

        $this->paypalInfoManager->importToPayment(
            [
                Info::FRAUD_FILTERS =>
                array_merge(
                    $fraudMessages,
                    (array)$payment->getAdditionalInformation(Info::FRAUD_FILTERS)
                )
            ],
            $payment
        );
    }

    /**
     * Converts rules xml document to description=>message dictionary
     *
     * @param string $rulesString
     * @return array
     */
    private function getFraudRulesDictionary($rulesString)
    {
        libxml_use_internal_errors(true);
        $rulesString = preg_replace('#<!DOCTYPE.*?]>\s*#s', '', $rulesString);
        $loadEntities = libxml_disable_entity_loader(true);
        $rules = [];
        try {
            $rulesXml = new \SimpleXMLElement($rulesString);
            foreach ($rulesXml->{'rule'} as $rule) {
                $rules[(string)$rule->{'ruleDescription'}] = (string)$rule->{'triggeredMessage'};
            }
        } catch (\Exception $e) {

        } finally {
            libxml_use_internal_errors(false);
            libxml_disable_entity_loader($loadEntities);
        }

        return $rules;
    }
}

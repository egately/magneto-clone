<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Block;

use Magento\Framework\View\Element\Template;
use Magento\Translation\Model\FileManager;
use Magento\Translation\Model\Js\Config;

/**
 * JS translation block
 *
 * @api
 * @since 100.0.2
 * @deprecated logic was refactored in order to not use localstorage at all.
 *
 * You can see details in app/code/Magento/Translation/view/base/web/js/mage-translation-dictionary.js
 * These block and view file were left in order to keep backward compatibility
 */
class Js extends Template
{
    /**
     * @param Template\Context $context
     * @param Config $config
     * @param FileManager $fileManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        protected readonly Config $config,
        private readonly FileManager $fileManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Is js translation set to dictionary mode
     *
     * @return bool
     */
    public function dictionaryEnabled()
    {
        return $this->config->dictionaryEnabled();
    }

    /**
     * Gets current js-translation.json timestamp
     *
     * @return string
     */
    public function getTranslationFileTimestamp()
    {
        return $this->fileManager->getTranslationFileTimestamp();
    }

    /**
     * Get translation file path
     *
     * @return string
     */
    public function getTranslationFilePath()
    {
        return $this->fileManager->getTranslationFilePath();
    }

    /**
     * Gets current version of the translation file.
     *
     * @return string
     * @since 100.3.0
     */
    public function getTranslationFileVersion()
    {
        return $this->fileManager->getTranslationFileVersion();
    }
}

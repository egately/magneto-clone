<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Block\Extension;

use Magento\Mtf\Block\Block;
use Magento\Mtf\Client\Locator;
use Magento\Setup\Test\Fixture\Extension;

/**
 * Abstract Extensions Grid block.
 */
abstract class AbstractGrid extends Block
{
    /**
     * 'Next Page' button for grid.
     *
     * @var string
     */
    protected $nextPageButton = '.action-next';

    /**
     * Grid that contains the list of extensions.
     *
     * @var string
     */
    protected $dataGrid = '.data-grid';

    /**
     * Container that contains name of the extension.
     *
     * @var string
     */
    protected $extensionName = "//*[contains(text(), '%s')]";

    /**
     * Find Extension on the grid by name.
     *
     * @param Extension $extension
     * @return boolean
     */
    public function findExtensionOnGrid(Extension $extension)
    {
        $result = false;
        while (true) {
            if (($result = $this->isExtensionOnGrid($extension->getExtensionName())) || !$this->clickNextPageButton()) {
                break;
            }
        }

        return $result;
    }

    /**
     * Check that there is extension on grid.
     *
     * @param string $name
     * @return bool
     */
    protected function isExtensionOnGrid($name)
    {
        $this->waitForElementVisible($this->dataGrid);
        return $this->_rootElement->find(
            sprintf($this->extensionName, $name),
            Locator::SELECTOR_XPATH
        )->isVisible();
    }

    /**
     * Click 'Next Page' button.
     *
     * @return bool
     */
    protected function clickNextPageButton()
    {
        $this->waitForElementVisible($this->nextPageButton);
        $nextPageButton = $this->_rootElement->find($this->nextPageButton);
        if (!$nextPageButton->isDisabled()) {
            $nextPageButton->click();
            return true;
        }

        return false;
    }
}

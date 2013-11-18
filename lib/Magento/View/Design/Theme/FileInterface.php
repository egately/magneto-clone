<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Theme file interface
 */
namespace Magento\View\Design\Theme;

interface FileInterface
{
    /**
     * Set customization service model
     *
     * @param \Magento\View\Design\Theme\Customization\FileInterface $service
     * @return $this
     */
    public function setCustomizationService(Customization\FileInterface $service);

    /**
     * Get customization service model
     *
     * @return \Magento\View\Design\Theme\Customization\FileInterface
     */
    public function getCustomizationService();

    /**
     * Attaches selected theme to current file
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return $this
     */
    public function setTheme(\Magento\View\Design\ThemeInterface $theme);

    /**
     * Get theme model
     *
     * @return \Magento\Core\Model\Theme
     */
    public function getTheme();

    /**
     * Set filename of custom file
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName);

    /**
     * Get filename of custom file
     *
     * @return string|null
     */
    public function getFileName();

    /**
     * Return absolute path to file of customization
     *
     * @return string
     */
    public function getFullPath();

    /**
     * Get short file information which can be serialized
     *
     * @return array
     */
    public function getFileInfo();

    /**
     * Get content of current file
     *
     * @return string
     */
    public function getContent();

    /**
     * Save custom file
     *
     * @return $this
     */
    public function save();

    /**
     * Delete custom file
     *
     * @return $this
     */
    public function delete();
}

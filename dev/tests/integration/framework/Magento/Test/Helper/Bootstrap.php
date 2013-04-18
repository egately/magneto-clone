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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Helper providing exclusive restricted access to the underlying bootstrap instance
 */
class Magento_Test_Helper_Bootstrap
{
    /**
     * @var Magento_Test_Helper_Bootstrap
     */
    private static $_instance;

    /**
     * @var Magento_Test_Bootstrap
     */
    protected $_bootstrap;

    /**
     * Set self instance for static access
     *
     * @param Magento_Test_Helper_Bootstrap $instance
     * @throws Magento_Exception
     */
    public static function setInstance(Magento_Test_Helper_Bootstrap $instance)
    {
        if (self::$_instance) {
            throw new Magento_Exception('Helper instance cannot be redefined.');
        }
        self::$_instance = $instance;
    }

    /**
     * Self instance getter
     *
     * @return Magento_Test_Helper_Bootstrap
     * @throws Magento_Exception
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            throw new Magento_Exception('Helper instance is not defined yet.');
        }
        return self::$_instance;
    }

    /**
     * Check the possibility to send headers or to use headers related function (like set_cookie)
     *
     * @return bool
     */
    public static function canTestHeaders()
    {
        if (!headers_sent() && extension_loaded('xdebug') && function_exists('xdebug_get_headers')) {
            return true;
        }
        return false;
    }

    /**
     * Constructor
     *
     * @param Magento_Test_Bootstrap $bootstrap
     */
    public function __construct(Magento_Test_Bootstrap $bootstrap)
    {
        $this->_bootstrap = $bootstrap;
    }

    /**
     * Retrieve application installation directory
     *
     * @return string
     */
    public function getAppInstallDir()
    {
        return $this->_bootstrap->getApplication()->getInstallDir();
    }

    /**
     * Retrieve application initialization options
     *
     * @return array
     */
    public function getAppInitParams()
    {
        return $this->_bootstrap->getApplication()->getInitParams();
    }

    /**
     * Retrieve the database vendor name used by the bootstrap
     *
     * @return string
     */
    public function getDbVendorName()
    {
        return $this->_bootstrap->getDbVendorName();
    }

    /**
     * Reinitialize the application instance optionally passing parameters to be overridden.
     * Intended to be used for the tests isolation purposes.
     *
     * @param array $overriddenParams
     */
    public function reinitialize(array $overriddenParams = array())
    {
        $this->_bootstrap->getApplication()->reinitialize($overriddenParams);
    }

    /**
     * Perform the full request processing by the application instance optionally passing parameters to be overridden.
     * Intended to be used by the controller tests.
     *
     * @param Magento_Test_Request $request
     * @param Magento_Test_Response $response
     */
    public function runApp(Magento_Test_Request $request, Magento_Test_Response $response)
    {
        $this->_bootstrap->getApplication()->run($request, $response);
    }
}

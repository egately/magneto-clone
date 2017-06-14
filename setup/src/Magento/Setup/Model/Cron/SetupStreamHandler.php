<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Cron;

use Magento\Framework\Filesystem\DriverInterface;

/**
 * Setup specific stream handler
 */
class SetupStreamHandler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * @var string
     */
    protected $fileName = 'update.log';

    /**
     * @var int
     */
    protected $loggerType = \Magento\Framework\Logger\Monolog::ERROR;

    /**
     * @param DriverInterface $filesystem
     * @param string $filePath
     * @param string $fileDirectory
     */
    public function __construct(
        DriverInterface $filesystem,
        $filePath = null,
        $fileDirectory = null
    ) {
        parent::__construct($filesystem, $filePath, $fileDirectory);
    }
}

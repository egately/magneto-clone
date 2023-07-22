<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Setup\Patch\Data;

use Magento\Framework\Console\Cli;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;

class UpgradePasswordHashes implements DataPatchInterface, PatchVersionInterface
{
    /**
     * PatchInitial constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup
    ) {
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $this->upgradeHash();
        $this->moduleDataSetup->getConnection()->endSetup();

        return Cli::RETURN_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getVersion()
    {
        return '2.0.1';
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Upgrade password hashes.
     */
    private function upgradeHash()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $customerEntityTable = $this->moduleDataSetup->getTable('admin_user');

        $select = $connection->select()->from(
            $customerEntityTable,
            ['user_id', 'password']
        );

        $customers = $connection->fetchAll($select);
        foreach ($customers as $customer) {
            list($hash, $salt) = explode(Encryptor::DELIMITER, $customer['password'] ?? '');

            $newHash = $customer['password'];
            if (strlen($hash) === 32) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_MD5]);
            } elseif (strlen($hash) === 64) {
                $newHash = implode(Encryptor::DELIMITER, [$hash, $salt, Encryptor::HASH_VERSION_SHA256]);
            }

            $bind = ['password' => $newHash];
            $where = ['user_id = ?' => (int)$customer['user_id']];
            $connection->update($customerEntityTable, $bind, $where);
        }
    }
}

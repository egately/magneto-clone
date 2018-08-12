<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\Attribute;

use Magento\Eav\Api\Data\AttributeGroupExtensionInterface;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Model\Entity\Attribute\Group as AttributeGroup;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group as AttributeGroupResourceModel;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * @api
 * @method int getSortOrder()
 * @method AttributeGroup setSortOrder(int $value)
 * @method int getDefaultId()
 * @method AttributeGroup setDefaultId(int $value)
 * @method string getAttributeGroupCode()
 * @method AttributeGroup setAttributeGroupCode(string $value)
 * @method string getTabGroupCode()
 * @method AttributeGroup setTabGroupCode(string $value)
 * @since 100.0.2
 */
class Group extends AbstractExtensibleModel implements AttributeGroupInterface
{
    /**
     * @var Translit
     */
    private $translitFilter;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Translit $translitFilter
     * @param AbstractResource $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Translit $translitFilter,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->translitFilter = $translitFilter;
    }

    /**
     * Resource initialization
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function _construct()
    {
        $this->_init(AttributeGroupResourceModel::class);
    }

    /**
     * Checks if current attribute group exists
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function itemExists()
    {
        return $this->_getResource()->itemExists($this);
    }

    /**
     * Delete groups
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function deleteGroups()
    {
        return $this->_getResource()->deleteGroups($this);
    }

    /**
     * Processing object before save data
     *
     * @return $this
     */
    public function beforeSave()
    {
        $groupName = $this->getAttributeGroupName();
        if ($groupName) {
            $attributeGroupCode = trim(
                preg_replace(
                    '/[^a-z0-9]+/',
                    '-',
                    $this->translitFilter->filter(strtolower($groupName))
                ),
                '-'
            );
            if (empty($attributeGroupCode)) {
                // in the following code md5 is not used for security purposes
                $attributeGroupCode = md5($groupName);
            }
            $this->setAttributeGroupCode($attributeGroupCode);
        }
        return parent::beforeSave();
    }

    /**
     * {@inheritdoc}
     * @codeCoverageIgnoreStart
     */
    public function getAttributeGroupId()
    {
        return $this->getData(self::GROUP_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeGroupName()
    {
        return $this->getData(self::GROUP_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSetId()
    {
        return $this->getData(self::ATTRIBUTE_SET_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroupId($attributeGroupId)
    {
        return $this->setData(self::GROUP_ID, $attributeGroupId);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeGroupName($attributeGroupName)
    {
        return $this->setData(self::GROUP_NAME, $attributeGroupName);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributeSetId($attributeSetId)
    {
        return $this->setData(self::ATTRIBUTE_SET_ID, $attributeSetId);
    }

    /**
     * {@inheritdoc}
     *
     * @return AttributeGroupExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param AttributeGroupExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        AttributeGroupExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }

    //@codeCoverageIgnoreEnd
}

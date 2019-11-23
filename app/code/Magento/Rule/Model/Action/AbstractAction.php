<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Action;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Abstract rule action
 *
 * phpcs:disable Magento2.Classes.AbstractApi
 * @api
 * @since 100.0.2
 */
abstract class AbstractAction extends \Magento\Framework\DataObject implements ActionInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * Base name for hidden elements
     * @var string
     */
    protected $elementName = 'rule';

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_layout = $layout;

        parent::__construct($data);

        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        $attributes = $this->getAttributeOption();
        if ($attributes) {
            reset($attributes);
            $this->setAttribute(key($attributes));
        }

        $operators = $this->getOperatorOption();
        if ($operators) {
            reset($operators);
            $this->setOperator(key($operators));
        }
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * Array
     *
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
        ];
        return $out;
    }

    /**
     * Xml
     *
     * @return string
     */
    public function asXml()
    {
        $xml = "<type>" .
            $this->getType() .
            "</type>" .
            "<attribute>" .
            $this->getAttribute() .
            "</attribute>" .
            "<operator>" .
            $this->getOperator() .
            "</operator>" .
            "<value>" .
            $this->getValue() .
            "</value>";
        return $xml;
    }

    /**
     * Load array
     *
     * @param array $arr
     * @return $this
     */
    public function loadArray(array $arr)
    {
        $this->addData(
            [
                'type' => $arr['type'],
                'attribute' => $arr['attribute'],
                'operator' => $arr['operator'],
                'value' => $arr['value'],
            ]
        );
        $this->loadAttributeOptions();
        $this->loadOperatorOptions();
        $this->loadValueOptions();
        return $this;
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([]);
        return $this;
    }

    /**
     * Get attribute select options
     *
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = [];
        foreach ($this->getAttributeOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * Get Attribute name
     *
     * @return string
     */
    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    /**
     * Load operator options
     *
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(['=' => __('to'), '+=' => __('by')]);
        return $this;
    }

    /**
     * Get operator select options
     *
     * @return array
     */
    public function getOperatorSelectOptions()
    {
        $opt = [];
        foreach ($this->getOperatorOption() as $k => $v) {
            $opt[] = ['value' => $k, 'label' => $v];
        }
        return $opt;
    }

    /**
     * Get operator name
     *
     * @return string
     */
    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    /**
     * Load value options
     *
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }

    /**
     * Get value select options
     *
     * @return array
     */
    public function getValueSelectOptions()
    {
        $opt = [];
        foreach ($this->getValueOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * Get value name
     *
     * @return string
     */
    public function getValueName()
    {
        $value = $this->getValue();
        return !empty($value) || 0 === $value ? $value : '...';
    }

    /**
     * Get new child select options
     *
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return [['value' => '', 'label' => __('Please choose an action to add.')]];
    }

    /**
     * Get new child name
     *
     * @return string
     */
    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    /**
     * Html
     *
     * @return string
     */
    public function asHtml()
    {
        return '';
    }

    /**
     * Html recursive
     *
     * @return string
     */
    public function asHtmlRecursive()
    {
        $str = $this->asHtml();
        return $str;
    }

    /**
     * Get type element
     *
     * @return AbstractElement
     */
    public function getTypeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':type',
            'hidden',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true
            ]
        );
    }

    /**
     * Get attribute element
     *
     * @return $this
     */
    public function getAttributeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':attribute',
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * Get operator element
     *
     * @return $this
     */
    public function getOperatorElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':operator',
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][operator]',
                'values' => $this->getOperatorSelectOptions(),
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * Get value element
     *
     * @return $this
     */
    public function getValueElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':value',
            'text',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][value]',
                'value' => $this->getValue(),
                'value_name' => $this->getValueName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * Get add link html
     *
     * @return string
     */
    public function getAddLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_add.gif');
        $html = '<img src="' . $src . '" alt="" class="rule-param-add v-middle" />';
        return $html;
    }

    /**
     * Get remove link html
     *
     * @return string
     */
    public function getRemoveLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_remove.gif');
        $html = '<span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove"><img src="' .
            $src .
            '" alt="" class="v-middle" /></a></span>';
        return $html;
    }

    /**
     * String
     *
     * @param string $format
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asString($format = '')
    {
        return "";
    }

    /**
     * String recursive
     *
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = str_pad('', $level * 3, ' ', STR_PAD_LEFT) . $this->asString();
        return $str;
    }

    /**
     * Process
     *
     * @return $this
     */
    public function process()
    {
        return $this;
    }
}

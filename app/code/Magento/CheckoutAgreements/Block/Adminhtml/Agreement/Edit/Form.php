<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Block\Adminhtml\Agreement\Edit;

use \Magento\Framework\App\ObjectManager;

/**
 * Class Form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\CheckoutAgreements\Model\AgreementModeOptions
     */
    protected $agreementModeOptions;
    
    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    private $wysiwygConfig;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Editor config path
     */
   const XML_PATH_SHOW_EDITOR = 'checkout/options/enable_wysiwyg_editor';
    
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\CheckoutAgreements\Model\AgreementModeOptions $agreementModeOptions
     * @param array $data
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\CheckoutAgreements\Model\AgreementModeOptions $agreementModeOptions,
        array $data = [],
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig = null,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig = null
    ) {
        $this->_systemStore = $systemStore;
        $this->agreementModeOptions = $agreementModeOptions;
        $this->wysiwygConfig = $wysiwygConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Cms\Model\Wysiwyg\Config::class);
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\App\Config\ScopeConfigInterface::class);
       parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init class
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('checkoutAgreementForm');
        $this->setTitle(__('Terms and Conditions Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('checkout_agreement');
       
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Terms and Conditions Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getId()) {
            $fieldset->addField('agreement_id', 'hidden', ['name' => 'agreement_id']);
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Condition Name'),
                'title' => __('Condition Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => ['1' => __('Enabled'), '0' => __('Disabled')]
            ]
        );

        $fieldset->addField(
            'is_html',
            'select',
            [
                'label' => __('Show Content as'),
                'title' => __('Show Content as'),
                'name' => 'is_html',
                'required' => true,
                'options' => [0 => __('Text'), 1 => __('HTML')]
            ]
        );

        $fieldset->addField(
            'mode',
            'select',
            [
                'label' => __('Applied'),
                'title' => __('Applied'),
                'name' => 'mode',
                'required' => true,
                'options' => $this->agreementModeOptions->getOptionsArray()
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'stores',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                \Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element::class
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'stores',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $model->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'checkbox_text',
            'textarea',
            [
                'name' => 'checkbox_text',
                'label' => __('Checkbox Text'),
                'title' => __('Checkbox Text'),
                'rows' => '5',
                'cols' => '30',
                'required' => true
            ]
        );

        if($this->scopeConfig->getValue(self::XML_PATH_SHOW_EDITOR)){
           $fieldset->addField(
            'content',
            'editor',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'style' => 'height:24em;',
                'required' => true,
                'wysiwyg' => true,
                'config' =>$this->wysiwygConfig->getConfig()
            ]
        ); 
       } else {
        $fieldset->addField(
            'content',
            'textarea',
            [
                'name' => 'content',
                'label' => __('Content'),
                'title' => __('Content'),
                'style' => 'height:24em;',
                'required' => true,
             ]
        );
       }

        $fieldset->addField(
            'content_height',
            'text',
            [
                'name' => 'content_height',
                'label' => __('Content Height (css)'),
                'title' => __('Content Height'),
                'maxlength' => 25,
                'class' => 'validate-css-length'
            ]
        );

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportExport\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;
use Magento\ImportExport\Controller\Adminhtml\Import as ImportController;

class Index extends ImportController
{
    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $message = $this->_objectManager->get(\Magento\ImportExport\Helper\Data::class)->getMaxUploadSizeMessage();
        $this->messageManager->addComplexNoticeMessage(
            'addUnescapedMessage',
            [
                'text' => $message,
            ]
        );
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_ImportExport::system_convert_import');
        $resultPage->getConfig()->getTitle()->prepend(__('Import/Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import'));
        $resultPage->addBreadcrumb(__('Import'), __('Import'));
        return $resultPage;
    }
}

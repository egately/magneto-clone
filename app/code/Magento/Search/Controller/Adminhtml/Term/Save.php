<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Term;

use Exception;
use Magento\Backend\Model\View\Result\Redirect as ResultRedirect;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Search\Model\Query as ModelQuery;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Controller\Adminhtml\Term as TermController;
use Magento\Framework\Exception\LocalizedException;

class Save extends TermController implements HttpPostActionInterface
{
    /**
     * @param Context $context
     * @param QueryFactory $queryFactory
     */
    public function __construct(
        Context $context,
        private readonly QueryFactory $queryFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Save search query
     *
     * @return ResultRedirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        if ($this->getRequest()->isPost() && $data) {
            try {
                $model = $this->loadQuery();
                $model->addData($data);
                $model->setIsProcessed(0);
                $model->save();
                $this->messageManager->addSuccessMessage(__('You saved the search term.'));
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $this->proceedToEdit($data);
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the search query.')
                );
                return $this->proceedToEdit($data);
            }
        }

        /** @var ResultRedirect $resultRedirect */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirectResult->setPath('search/*');
    }

    /**
     * Create\Load Query model instance
     *
     * @return ModelQuery
     * @throws LocalizedException
     */
    private function loadQuery()
    {
        //validate query
        $queryText = $this->getRequest()->getPost('query_text', false);
        $queryId = $this->getRequest()->getPost('query_id', null);

        /* @var ModelQuery $model */
        $model = $this->queryFactory->create();
        if ($queryText) {
            $storeId = $this->getRequest()->getPost('store_id', false);
            $model->setStoreId($storeId);
            $model->loadByQueryText($queryText);
            if ($model->getId() && $model->getId() != $queryId) {
                throw new LocalizedException(
                    __('You already have an identical search term query.')
                );
            }
        }
        if ($queryId && !$model->getId()) {
            $model->load($queryId);
        }
        return $model;
    }

    /**
     * Redirect to Edit page
     *
     * @param array $data
     * @return ResultRedirect
     */
    private function proceedToEdit($data)
    {
        $this->_getSession()->setPageData($data);
        /** @var ResultRedirect $resultRedirect */
        $redirectResult = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $redirectResult->setPath('search/*/edit', ['id' => $this->getRequest()->getPost('query_id', null)]);
    }
}

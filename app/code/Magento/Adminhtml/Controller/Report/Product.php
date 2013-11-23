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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product reports admin controller
 *
 * @category   Magento
 * @package    Magento_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Controller\Report;

class Product extends \Magento\Adminhtml\Controller\Report\AbstractReport
{
    /**
     * Add report/products breadcrumbs
     *
     * @return \Magento\Adminhtml\Controller\Report\Product
     */
    public function _initAction()
    {
        parent::_initAction();
        $this->_addBreadcrumb(
            __('Products'),
            __('Products')
        );
        return $this;
    }

    /**
     * Sold Products Report Action
     *
     */
    public function soldAction()
    {
        $this->_title->add(__('Ordered Products Report'));
        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_products_sold')
            ->_addBreadcrumb(
                __('Products Ordered'),
                __('Products Ordered')
            );
        $this->_view->renderLayout();
    }

    /**
     * Export Sold Products report to CSV format action
     *
     */
    public function exportSoldCsvAction()
    {
        $this->_view->loadLayout();
        $fileName   = 'products_ordered.csv';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export Sold Products report to XML format action
     *
     */
    public function exportSoldExcelAction()
    {
        $this->_view->loadLayout();
        $fileName   = 'products_ordered.xml';
        /** @var \Magento\Backend\Block\Widget\Grid\ExportInterface $exportBlock */
        $exportBlock = $this->_view->getLayout()->getChildBlock('adminhtml.report.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile($fileName));
    }

    /**
     * Most viewed products
     *
     */
    public function viewedAction()
    {
        $this->_title->add(__('Product Views Report'));

        $this->_showLastExecutionTime(\Magento\Reports\Model\Flag::REPORT_PRODUCT_VIEWED_FLAG_CODE, 'viewed');

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_products_viewed')
            ->_addBreadcrumb(
                __('Products Most Viewed Report'),
                __('Products Most Viewed Report')
            );

        $gridBlock = $this->_view->getLayout()->getBlock('report_product_viewed.grid');
        $filterFormBlock = $this->_view->getLayout()->getBlock('grid.filter.form');

        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->_view->renderLayout();
    }

    /**
     * Export products most viewed report to CSV format
     *
     */
    public function exportViewedCsvAction()
    {
        $fileName   = 'products_mostviewed.csv';
        $grid       = $this->_view->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Product\Viewed\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getCsvFile());
    }

    /**
     * Export products most viewed report to XML format
     *
     */
    public function exportViewedExcelAction()
    {
        $fileName   = 'products_mostviewed.xml';
        $grid       = $this->_view->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Product\Viewed\Grid');
        $this->_initReportAction($grid);
        return $this->_fileFactory->create($fileName, $grid->getExcelFile($fileName));
    }

    /**
     * Low stock action
     *
     */
    public function lowstockAction()
    {
        $this->_title->add(__('Low Stock Report'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Reports::report_products_lowstock')
            ->_addBreadcrumb(
                __('Low Stock'),
                __('Low Stock')
            );
            $this->_view->renderLayout();
    }

    /**
     * Export low stock products report to CSV format
     *
     */
    public function exportLowstockCsvAction()
    {
        $this->_view->loadLayout(false);
        $fileName = 'products_lowstock.csv';
        $exportBlock = $this->_view->getLayout()
            ->getChildBlock('adminhtml.block.report.product.lowstock.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getCsvFile());
    }

    /**
     * Export low stock products report to XML format
     *
     */
    public function exportLowstockExcelAction()
    {
        $this->_view->loadLayout(false);
        $fileName = 'products_lowstock.xml';
        $exportBlock = $this->_view->getLayout()
            ->getChildBlock('adminhtml.block.report.product.lowstock.grid', 'grid.export');
        return $this->_fileFactory->create($fileName, $exportBlock->getExcelFile());
    }

    /**
     * Downloads action
     *
     */
    public function downloadsAction()
    {
        $this->_title->add(__('Downloads Report'));

        $this->_initAction()
            ->_setActiveMenu('Magento_Downloadable::report_products_downloads')
            ->_addBreadcrumb(
                __('Downloads'),
                __('Downloads')
            )
            ->_addContent(
                $this->_view->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Product\Downloads')
            );
        $this->_view->renderLayout();
    }

    /**
     * Export products downloads report to CSV format
     *
     */
    public function exportDownloadsCsvAction()
    {
        $fileName   = 'products_downloads.csv';
        $content    = $this->_view->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Product\Downloads\Grid')
            ->setSaveParametersInSession(true)
            ->getCsv();

        return $this->_fileFactory->create($fileName, $content);
    }

    /**
     * Export products downloads report to XLS format
     *
     */
    public function exportDownloadsExcelAction()
    {
        $fileName   = 'products_downloads.xml';
        $content    = $this->_view->getLayout()->createBlock('Magento\Adminhtml\Block\Report\Product\Downloads\Grid')
            ->setSaveParametersInSession(true)
            ->getExcel($fileName);

        return $this->_fileFactory->create($fileName, $content);
    }

    /**
     * Check is allowed for report
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'viewed':
                return $this->_authorization->isAllowed('Magento_Reports::viewed');
                break;
            case 'sold':
                return $this->_authorization->isAllowed('Magento_Reports::sold');
                break;
            case 'lowstock':
                return $this->_authorization->isAllowed('Magento_Reports::lowstock');
                break;
            default:
                return $this->_authorization->isAllowed('Magento_Reports::report_products');
                break;
        }
    }
}

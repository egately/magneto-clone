<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Test\Unit\Controller\Page;

use Magento\Cms\Controller\Adminhtml\Page\InlineEdit;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class InlineEditTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\RequestInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $request;

    /** @var \Magento\Framework\Message\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageManager;

    /** @var \Magento\Framework\Message\MessageInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $message;

    /** @var \Magento\Framework\Message\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $messageCollection;

    /** @var \Magento\Cms\Model\Page|\PHPUnit_Framework_MockObject_MockObject */
    protected $cmsPage;

    /** @var \Magento\Backend\App\Action\Context|\PHPUnit_Framework_MockObject_MockObject */
    protected $context;

    /** @var \Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor|\PHPUnit_Framework_MockObject_MockObject */
    protected $dataProcessor;

    /** @var \Magento\Cms\Api\PageRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $pageRepository;

    /** @var \Magento\Framework\Controller\Result\JsonFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $jsonFactory;

    /** @var \Magento\Framework\Controller\Result\Json|\PHPUnit_Framework_MockObject_MockObject */
    protected $resultJson;

    /** @var InlineEdit */
    protected $controller;

    public function setUp()
    {
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->request = $this->getMockForAbstractClass(
            'Magento\Framework\App\RequestInterface',
            [],
            '',
            false
        );
        $this->messageManager = $this->getMockForAbstractClass(
            'Magento\Framework\Message\ManagerInterface',
            [],
            '',
            false
        );
        $this->messageCollection = $this->getMock(
            'Magento\Framework\Message\Collection',
            [],
            [],
            '',
            false
        );
        $this->message = $this->getMockForAbstractClass(
            'Magento\Framework\Message\MessageInterface',
            [],
            '',
            false
        );
        $this->cmsPage = $this->getMock(
            'Magento\Cms\Model\Page',
            [],
            [],
            '',
            false
        );
        $this->context = $helper->getObject(
            'Magento\Backend\App\Action\Context',
            [
                'request' => $this->request,
                'messageManager' => $this->messageManager
            ]
        );
        $this->dataProcessor = $this->getMock(
            'Magento\Cms\Controller\Adminhtml\Page\PostDataProcessor',
            [],
            [],
            '',
            false
        );
        $this->pageRepository = $this->getMockForAbstractClass(
            'Magento\Cms\Api\PageRepositoryInterface',
            [],
            '',
            false
        );
        $this->resultJson = $this->getMock('Magento\Framework\Controller\Result\Json', [], [], '', false);
        $this->jsonFactory = $this->getMock(
            'Magento\Framework\Controller\Result\JsonFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->controller = new InlineEdit(
            $this->context,
            $this->dataProcessor,
            $this->pageRepository,
            $this->jsonFactory
        );
    }

    public function prepareMocksForTestExecute()
    {
        $postData = [
            1 => [
                'title' => '404 Not Found',
                'identifier' => 'no-route'
            ]
        ];

        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('data', [])
            ->willReturn($postData);
        $this->pageRepository->expects($this->once())
            ->method('getById')
            ->with(1)
            ->willReturn($this->cmsPage);
        $this->dataProcessor->expects($this->once())
            ->method('filter')
            ->with($postData[1])
            ->willReturnArgument(0);
        $this->dataProcessor->expects($this->once())
            ->method('validate')
            ->with($postData[1])
            ->willReturn(false);
        $this->messageManager->expects($this->once())
            ->method('getMessages')
            ->with(true)
            ->willReturn($this->messageCollection);
        $this->messageCollection
            ->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->message]);
        $this->message->expects($this->once())
            ->method('toString')
            ->willReturn('Error message');
        $this->cmsPage->expects($this->atLeastOnce())
            ->method('getTitle')
            ->willReturn('404 Not Found');
        $this->cmsPage->expects($this->once())
            ->method('getData')
            ->willReturn([
                'layout' => '1column',
                'identifier' => 'test-identifier'
            ]);
        $this->cmsPage->expects($this->once())
            ->method('setData')
            ->with([
                'layout' => '1column',
                'title' => '404 Not Found',
                'identifier' => 'no-route'
            ]);
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
    }

    public function testExecuteWithLocalizedException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('LocalizedException')));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    '[Page: 404 Not Found] Error message',
                    '[Page: 404 Not Found] LocalizedException'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithRuntimeException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new \RuntimeException(__('RuntimeException')));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    '[Page: 404 Not Found] Error message',
                    '[Page: 404 Not Found] RuntimeException'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithException()
    {
        $this->prepareMocksForTestExecute();
        $this->pageRepository->expects($this->once())
            ->method('save')
            ->with($this->cmsPage)
            ->willThrowException(new \Exception(__('Exception')));
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    '[Page: 404 Not Found] Error message',
                    '[Page: 404 Not Found] Something went wrong while saving the page.'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }

    public function testExecuteWithoutData()
    {
        $this->request->expects($this->at(0))
            ->method('getParam')
            ->with('isAjax')
            ->willReturn(true);
        $this->request->expects($this->at(1))
            ->method('getParam')
            ->with('data', [])
            ->willReturn([]);
        $this->jsonFactory->expects($this->once())
            ->method('create')
            ->willReturn($this->resultJson);
        $this->resultJson->expects($this->once())
            ->method('setData')
            ->with([
                'messages' => [
                    'Please correct the data sent.'
                ],
                'error' => true
            ])
            ->willReturnSelf();

        $this->controller->execute();
    }
}

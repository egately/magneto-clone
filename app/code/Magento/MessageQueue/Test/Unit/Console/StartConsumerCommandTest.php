<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\MessageQueue\Test\Unit\Console;

use Magento\MessageQueue\Console\StartConsumerCommand;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\MessageQueue\Model\Cron\ConsumersRunner\PidConsumerManager;

/**
 * Unit tests for StartConsumerCommand.
 */
class StartConsumerCommandTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\MessageQueue\ConsumerFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $consumerFactory;

    /**
     * @var \Magento\Framework\App\State|\PHPUnit_Framework_MockObject_MockObject
     */
    private $appState;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var WriteFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $writeFactoryMock;

    /**
     * @var PidConsumerManager|\PHPUnit_Framework_MockObject_MockObject
     */
    private $pidConsumerManagerMock;

    /**
     * @var StartConsumerCommand
     */
    private $command;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->pidConsumerManagerMock = $this->getMockBuilder(PidConsumerManager::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->consumerFactory = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerFactory::class)
            ->disableOriginalConstructor()->getMock();
        $this->appState = $this->getMockBuilder(\Magento\Framework\App\State::class)
            ->disableOriginalConstructor()->getMock();
        $this->writeFactoryMock = $this->getMockBuilder(WriteFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->command = $this->objectManager->getObject(
            \Magento\MessageQueue\Console\StartConsumerCommand::class,
            [
                'consumerFactory' => $this->consumerFactory,
                'appState' => $this->appState,
                'writeFactory' => $this->writeFactoryMock,
                'pidConsumerManager' => $this->pidConsumerManagerMock,
            ]
        );
        parent::setUp();
    }

    /**
     * Test for execute method.
     *
     * @param string|null $pidFilePath
     * @param int $savePidExpects
     * @param int $isRunExpects
     * @param bool $isRun
     * @param int $runProcessExpects
     * @param int $expectedReturn
     * @return void
     * @dataProvider executeDataProvider
     */
    public function testExecute(
        $pidFilePath,
        $savePidExpects,
        $isRunExpects,
        $isRun,
        $runProcessExpects,
        $expectedReturn
    ) {
        $areaCode = 'area_code';
        $numberOfMessages = 10;
        $batchSize = null;
        $consumerName = 'consumer_name';
        $input = $this->getMockBuilder(\Symfony\Component\Console\Input\InputInterface::class)
            ->disableOriginalConstructor()->getMock();
        $output = $this->getMockBuilder(\Symfony\Component\Console\Output\OutputInterface::class)
            ->disableOriginalConstructor()->getMock();
        $input->expects($this->once())->method('getArgument')
            ->with(\Magento\MessageQueue\Console\StartConsumerCommand::ARGUMENT_CONSUMER)
            ->willReturn($consumerName);
        $input->expects($this->exactly(4))->method('getOption')
            ->withConsecutive(
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_BATCH_SIZE],
                [\Magento\MessageQueue\Console\StartConsumerCommand::OPTION_AREACODE],
                [\Magento\MessageQueue\Console\StartConsumerCommand::PID_FILE_PATH]
            )->willReturnOnConsecutiveCalls(
                $numberOfMessages,
                $batchSize,
                $areaCode,
                $pidFilePath
            );
        $this->appState->expects($this->exactly($runProcessExpects))->method('setAreaCode')->with($areaCode);
        $consumer = $this->getMockBuilder(\Magento\Framework\MessageQueue\ConsumerInterface::class)
            ->disableOriginalConstructor()->getMock();
        $this->consumerFactory->expects($this->exactly($runProcessExpects))
            ->method('get')->with($consumerName, $batchSize)->willReturn($consumer);
        $consumer->expects($this->exactly($runProcessExpects))->method('process')->with($numberOfMessages);

        $this->pidConsumerManagerMock->expects($this->exactly($isRunExpects))
            ->method('isRun')
            ->with($pidFilePath)
            ->willReturn($isRun);

        $this->pidConsumerManagerMock->expects($this->exactly($savePidExpects))
            ->method('savePid')
            ->with($pidFilePath);

        $this->assertSame(
            $expectedReturn,
            $this->command->run($input, $output)
        );
    }

    /**
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            [
                'pidFilePath' => null,
                'savePidExpects' => 0,
                'isRunExpects' => 0,
                'isRun' => false,
                'runProcessExpects' => 1,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            ],
            [
                'pidFilePath' => '/var/consumer.pid',
                'savePidExpects' => 1,
                'isRunExpects' => 1,
                'isRun' => false,
                'runProcessExpects' => 1,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_SUCCESS,
            ],
            [
                'pidFilePath' => '/var/consumer.pid',
                'savePidExpects' => 0,
                'isRunExpects' => 1,
                'isRun' => true,
                'runProcessExpects' => 0,
                'expectedReturn' => \Magento\Framework\Console\Cli::RETURN_FAILURE,
            ],
        ];
    }

    /**
     * Test configure() method implicitly via construct invocation.
     *
     * @return void
     */
    public function testConfigure()
    {
        $this->assertSame(StartConsumerCommand::COMMAND_QUEUE_CONSUMERS_START, $this->command->getName());
        $this->assertSame('Start MessageQueue consumer', $this->command->getDescription());
        /** Exception will be thrown if argument is not declared */
        $this->command->getDefinition()->getArgument(StartConsumerCommand::ARGUMENT_CONSUMER);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_NUMBER_OF_MESSAGES);
        $this->command->getDefinition()->getOption(StartConsumerCommand::OPTION_AREACODE);
        $this->command->getDefinition()->getOption(StartConsumerCommand::PID_FILE_PATH);
        $this->assertContains('To start consumer which will process', $this->command->getHelp());
    }
}

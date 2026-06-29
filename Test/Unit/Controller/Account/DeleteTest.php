<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category  Mageplaza
 * @package   Mageplaza_Gdpr
 * @copyright Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license   https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\Gdpr\Test\Unit\Controller\Account;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Mageplaza\Gdpr\Controller\Account\Delete;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Class DeleteTest
 * @package Mageplaza\Gdpr\Test\Unit\Controller\Account
 */
class DeleteTest extends TestCase
{
    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private CustomerRepositoryInterface|MockObject $customerRepository;

    /**
     * @var Session|MockObject
     */
    private Session|MockObject $customerSession;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var LoggerInterface|MockObject
     */
    private LoggerInterface|MockObject $logger;

    /**
     * @var Data|MockObject
     */
    private Data|MockObject $helper;

    /**
     * @var PhpCookieManager|MockObject
     */
    private PhpCookieManager|MockObject $cookieManager;

    /**
     * @var Request|MockObject
     */
    private Request|MockObject $request;

    /**
     * @var EventManagerInterface|MockObject
     */
    private EventManagerInterface|MockObject $eventManager;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private MessageManagerInterface|MockObject $messageManager;

    /**
     * @var RedirectFactory|MockObject
     */
    private RedirectFactory|MockObject $resultRedirectFactory;

    /**
     * @var Redirect|MockObject
     */
    private Redirect|MockObject $resultRedirect;

    /**
     * @var Delete
     */
    private Delete $controller;

    protected function setUp(): void
    {
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerSession    = $this->createMock(Session::class);
        $this->registry           = $this->createMock(Registry::class);
        $this->logger             = $this->createMock(LoggerInterface::class);
        $this->helper             = $this->createMock(Data::class);
        $this->cookieManager      = $this->createMock(PhpCookieManager::class);

        $this->request               = $this->createMock(Request::class);
        $this->eventManager          = $this->createMock(EventManagerInterface::class);
        $this->messageManager        = $this->createMock(MessageManagerInterface::class);
        $this->resultRedirect        = $this->createMock(Redirect::class);
        $this->resultRedirectFactory = $this->getMockBuilder(RedirectFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->resultRedirectFactory->method('create')->willReturn($this->resultRedirect);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getResponse')->willReturn($this->createMock(Response::class));
        $context->method('getEventManager')->willReturn($this->eventManager);
        $context->method('getMessageManager')->willReturn($this->messageManager);
        $context->method('getResultRedirectFactory')->willReturn($this->resultRedirectFactory);

        $this->controller = new Delete(
            $context,
            $this->customerRepository,
            $this->customerSession,
            $this->registry,
            $this->logger,
            $this->helper,
            $this->createMock(CookieMetadataFactory::class),
            $this->cookieManager
        );
    }

    public function testExecuteForwardsToNoRouteWhenNotAllowed(): void
    {
        $this->helper->method('allowDeleteAccount')->willReturn(false);

        $this->registry->expects($this->once())
            ->method('register')->with('use_page_cache_plugin', false);
        // _forward('noRoute') resets the request action and dispatched flag.
        $this->request->expects($this->once())->method('setActionName')->with('noRoute');
        $this->request->expects($this->once())->method('setDispatched')->with(false);
        $this->customerRepository->expects($this->never())->method('deleteById');

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteForwardsToNoRouteWhenNotLoggedIn(): void
    {
        $this->helper->method('allowDeleteAccount')->willReturn(true);
        $this->customerSession->method('isLoggedIn')->willReturn(false);

        $this->registry->expects($this->once())
            ->method('register')->with('use_page_cache_plugin', false);
        $this->request->expects($this->once())->method('setActionName')->with('noRoute');

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteDeletesAccountAndRedirectsToSuccess(): void
    {
        $this->helper->method('allowDeleteAccount')->willReturn(true);
        $this->customerSession->method('isLoggedIn')->willReturn(true);
        $this->customerSession->method('getCustomerId')->willReturn(3);
        $this->customerRepository->method('getById')->with(3)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));

        // Event manager is a no-op, so the checktoken flag stays true.
        $this->customerSession->expects($this->once())->method('logout');
        $this->customerRepository->expects($this->once())->method('deleteById')->with(3);
        $this->cookieManager->method('getCookie')->with('mage-cache-sessid')->willReturn(null);

        $this->resultRedirect->expects($this->once())
            ->method('setPath')->with('*/*/deleteSuccess')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }

    public function testExecuteForwardsToNoRouteWhenFlagCleared(): void
    {
        $this->helper->method('allowDeleteAccount')->willReturn(true);
        $this->customerSession->method('isLoggedIn')->willReturn(true);
        $this->customerSession->method('getCustomerId')->willReturn(3);
        $this->customerRepository->method('getById')->with(3)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));

        $this->eventManager->method('dispatch')
            ->willReturnCallback(function ($eventName, array $data = []) {
                if ($eventName === 'anonymise_account_before_delete') {
                    $data['checktoken']->setFlag(false);
                }
            });

        $this->registry->expects($this->once())
            ->method('register')->with('use_page_cache_plugin', false);
        $this->request->expects($this->once())->method('setActionName')->with('noRoute');
        $this->customerRepository->expects($this->never())->method('deleteById');

        $this->assertNull($this->controller->execute());
    }

    public function testExecuteRedirectsToSelfOnException(): void
    {
        $this->helper->method('allowDeleteAccount')->willReturn(true);
        $this->customerSession->method('isLoggedIn')->willReturn(true);
        $this->customerSession->method('getCustomerId')->willReturn(3);
        $this->customerRepository->method('getById')->with(3)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));
        $this->customerRepository->method('deleteById')
            ->willThrowException(new \Exception('delete boom'));

        $this->logger->expects($this->once())->method('critical')->with('delete boom');
        $this->messageManager->expects($this->once())->method('addErrorMessage');
        $this->resultRedirect->expects($this->once())
            ->method('setPath')->with('*/*/')->willReturnSelf();

        $this->assertSame($this->resultRedirect, $this->controller->execute());
    }
}

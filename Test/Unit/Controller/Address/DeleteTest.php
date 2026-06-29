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

namespace Mageplaza\Gdpr\Test\Unit\Controller\Address;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use Magento\Framework\App\Response\Http as Response;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface as MessageManagerInterface;
use Mageplaza\Gdpr\Controller\Address\Delete;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DeleteTest
 * @package Mageplaza\Gdpr\Test\Unit\Controller\Address
 */
class DeleteTest extends TestCase
{
    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private AddressRepositoryInterface|MockObject $addressRepository;

    /**
     * @var MessageManagerInterface|MockObject
     */
    private MessageManagerInterface|MockObject $messageManager;

    /**
     * @var Request|MockObject
     */
    private Request|MockObject $request;

    /**
     * @var RedirectInterface|MockObject
     */
    private RedirectInterface|MockObject $redirect;

    /**
     * @var Delete
     */
    private Delete $controller;

    protected function setUp(): void
    {
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->messageManager    = $this->createMock(MessageManagerInterface::class);
        $this->request           = $this->createMock(Request::class);
        $this->redirect          = $this->createMock(RedirectInterface::class);
        $response                = $this->createMock(Response::class);

        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getResponse')->willReturn($response);
        $context->method('getMessageManager')->willReturn($this->messageManager);
        $context->method('getRedirect')->willReturn($this->redirect);

        $this->controller = new Delete(
            $context,
            $this->addressRepository,
            $this->createMock(Data::class)
        );
    }

    public function testExecuteDeletesAddressAndAddsSuccess(): void
    {
        $this->request->method('getParam')->with('id')->willReturn(15);
        $this->addressRepository->expects($this->once())->method('deleteById')->with(15);

        $this->messageManager->expects($this->once())
            ->method('addSuccess')->with('Successfully deleted customer address');
        $this->messageManager->expects($this->never())->method('addError');
        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->anything(), 'customer/address/', []);

        $this->controller->execute();
    }

    public function testExecuteAddsErrorOnException(): void
    {
        $this->request->method('getParam')->with('id')->willReturn(15);
        $this->addressRepository->method('deleteById')
            ->willThrowException(new \Exception('cannot delete address'));

        $this->messageManager->expects($this->once())
            ->method('addError')->with('cannot delete address');
        $this->messageManager->expects($this->never())->method('addSuccess');
        $this->redirect->expects($this->once())
            ->method('redirect')->with($this->anything(), 'customer/address/', []);

        $this->controller->execute();
    }
}

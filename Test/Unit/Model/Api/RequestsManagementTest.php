<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Gdpr
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\Gdpr\Test\Unit\Model\Api;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Webapi\Exception as ExceptionApi;
use Mageplaza\Gdpr\Helper\Data;
use Mageplaza\Gdpr\Model\Api\Data\Config\GeneralConfig;
use Mageplaza\Gdpr\Model\Api\RequestsManagement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class RequestsManagementTest
 * @package Mageplaza\Gdpr\Test\Unit\Model\Api
 */
class RequestsManagementTest extends TestCase
{
    /**
     * @var GeneralConfig|MockObject
     */
    private GeneralConfig|MockObject $generalConfig;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private AddressRepositoryInterface|MockObject $addressRepository;

    /**
     * @var Data|MockObject
     */
    private Data|MockObject $helperData;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private CustomerRepositoryInterface|MockObject $customerRepository;

    /**
     * @var EventManagerInterface|MockObject
     */
    private EventManagerInterface|MockObject $eventManager;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var UserContextInterface|MockObject
     */
    private UserContextInterface|MockObject $userContext;

    /**
     * @var RequestsManagement
     */
    private RequestsManagement $model;

    protected function setUp(): void
    {
        $this->generalConfig      = $this->createMock(GeneralConfig::class);
        $this->addressRepository  = $this->createMock(AddressRepositoryInterface::class);
        $this->helperData         = $this->createMock(Data::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->eventManager       = $this->createMock(EventManagerInterface::class);
        $this->registry           = $this->createMock(Registry::class);
        $this->userContext        = $this->createMock(UserContextInterface::class);

        $this->model = new RequestsManagement(
            $this->generalConfig,
            $this->addressRepository,
            $this->helperData,
            $this->customerRepository,
            $this->eventManager,
            $this->registry,
            $this->userContext
        );
    }

    public function testGetConfigWrapsGeneralConfig(): void
    {
        $inner = new DataObject(['enable' => '1']);
        $this->generalConfig->method('getConfig')->willReturn($inner);

        $result = $this->model->getConfig();

        $this->assertInstanceOf(DataObject::class, $result);
        $this->assertSame($inner, $result->getData('general_config'));
    }

    public function testGetCurrentUserIdDelegatesToUserContext(): void
    {
        $this->userContext->method('getUserId')->willReturn(42);

        $this->assertSame(42, $this->model->getCurrentUserId());
    }

    public function testDeleteDefaultAddressSuccess(): void
    {
        $this->addressRepository->expects($this->once())
            ->method('deleteById')->with(7)->willReturn(true);

        $this->assertTrue($this->model->deleteDefaultAddress(7));
    }

    public function testDeleteDefaultAddressWrapsException(): void
    {
        $this->addressRepository->method('deleteById')
            ->willThrowException(new \Exception('boom'));

        $this->expectException(ExceptionApi::class);
        $this->expectExceptionMessage('boom');

        $this->model->deleteDefaultAddress(7);
    }

    public function testDeleteCustomerAccountNotAllowed(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(false);

        $this->expectException(ExceptionApi::class);
        $this->expectExceptionMessage('Not allow delete');

        $this->model->deleteCustomerAccount();
    }

    public function testDeleteCustomerAccountCustomerNotFound(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(true);
        $this->userContext->method('getUserId')->willReturn(10);
        $this->customerRepository->method('getById')
            ->with(10)
            ->willThrowException(new NoSuchEntityException(__('No customer')));

        $this->expectException(ExceptionApi::class);
        $this->expectExceptionMessage('No customer');

        $this->model->deleteCustomerAccount();
    }

    public function testDeleteCustomerAccountFlagNotSet(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(true);
        $this->userContext->method('getUserId')->willReturn(10);
        $this->customerRepository->method('getById')->with(10)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));

        // The "before" event clears the flag, forcing the abort branch.
        $this->eventManager->method('dispatch')
            ->willReturnCallback(function ($eventName, array $data = []) {
                if ($eventName === 'anonymise_account_before_delete') {
                    $data['checktoken']->setFlag(false);
                }
            });

        $this->customerRepository->expects($this->never())->method('deleteById');

        $this->expectException(ExceptionApi::class);
        $this->expectExceptionMessage('Flag not exit');

        $this->model->deleteCustomerAccount();
    }

    public function testDeleteCustomerAccountSuccess(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(true);
        $this->userContext->method('getUserId')->willReturn(10);
        $customer = $this->createMock(\Magento\Customer\Api\Data\CustomerInterface::class);
        $this->customerRepository->method('getById')->with(10)->willReturn($customer);

        $this->registry->expects($this->once())
            ->method('register')->with('isSecureArea', true, true);
        $this->customerRepository->expects($this->once())->method('deleteById')->with(10);
        $this->eventManager->expects($this->exactly(2))->method('dispatch');

        $this->assertTrue($this->model->deleteCustomerAccount());
    }

    public function testDeleteCustomerAccountWrapsDeleteException(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(true);
        $this->userContext->method('getUserId')->willReturn(10);
        $this->customerRepository->method('getById')->with(10)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));
        $this->customerRepository->method('deleteById')
            ->willThrowException(new \Exception('delete failed'));

        $this->expectException(ExceptionApi::class);
        $this->expectExceptionMessage('delete failed');

        $this->model->deleteCustomerAccount();
    }
}

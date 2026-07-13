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
 * @category    Mageplaza
 * @package     Mageplaza_Gdpr
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

declare(strict_types=1);

namespace Mageplaza\Gdpr\Test\Unit\GraphQl\Model\Resolver;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Registry;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\Gdpr\GraphQl\Model\Resolver\CreateDeleteAccountRequest;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateDeleteAccountRequestTest
 * @package Mageplaza\Gdpr\Test\Unit\GraphQl\Model\Resolver
 */
class CreateDeleteAccountRequestTest extends TestCase
{
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
     * @var CreateDeleteAccountRequest
     */
    private CreateDeleteAccountRequest $resolver;

    protected function setUp(): void
    {
        $this->helperData         = $this->createMock(Data::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);
        $this->eventManager       = $this->createMock(EventManagerInterface::class);
        $this->registry           = $this->createMock(Registry::class);

        $this->resolver = new CreateDeleteAccountRequest(
            $this->helperData,
            $this->createMock(GetCustomer::class),
            $this->customerRepository,
            $this->eventManager,
            $this->registry
        );
    }

    /**
     * @param bool $isCustomer
     *
     * @return ContextInterface|MockObject
     */
    private function buildContext(bool $isCustomer): ContextInterface
    {
        $extension = $this->createMock(ContextExtensionInterface::class);
        $extension->method('getIsCustomer')->willReturn($isCustomer);

        $context = $this->createMock(ContextInterface::class);
        $context->method('getExtensionAttributes')->willReturn($extension);

        return $context;
    }

    private function invoke(ContextInterface $context)
    {
        return $this->resolver->resolve(
            $this->createMock(Field::class),
            $context,
            $this->createMock(ResolveInfo::class)
        );
    }

    public function testResolveThrowsWhenNotCustomer(): void
    {
        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->invoke($this->buildContext(false));
    }

    public function testResolveThrowsWhenDisabled(): void
    {
        $this->helperData->method('isEnabled')->willReturn(false);

        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The Gdpr is disabled.');

        $this->invoke($this->buildContext(true));
    }

    public function testResolveReturnsMessageWhenDeleteNotAllowed(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('allowDeleteAccount')->willReturn(false);

        $result = $this->invoke($this->buildContext(true));

        $this->assertSame('Not allow delete', (string) $result['result']);
    }

    public function testResolveReturnsMessageWhenFlagCleared(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('allowDeleteAccount')->willReturn(true);

        $context = $this->buildContext(true);
        $context->method('getUserId')->willReturn(5);
        $this->customerRepository->method('getById')->with(5)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));

        $this->eventManager->method('dispatch')
            ->willReturnCallback(function ($eventName, array $data = []) {
                if ($eventName === 'anonymise_account_before_delete') {
                    $data['checkToken']->setFlag(false);
                }
            });
        $this->customerRepository->expects($this->never())->method('deleteById');

        $result = $this->invoke($context);

        $this->assertSame('Flag not exit', (string) $result['result']);
    }

    public function testResolveDeletesCustomerSuccessfully(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('allowDeleteAccount')->willReturn(true);

        $context = $this->buildContext(true);
        $context->method('getUserId')->willReturn(7);
        $this->customerRepository->method('getById')->with(7)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));

        $this->registry->expects($this->once())->method('register')->with('isSecureArea', true, true);
        $this->customerRepository->expects($this->once())->method('deleteById')->with(7);
        $this->eventManager->expects($this->exactly(2))->method('dispatch');

        $result = $this->invoke($context);

        $this->assertSame('Customer id 7 has been deleted', (string) $result['result']);
    }

    public function testResolveWrapsDeleteException(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->helperData->method('allowDeleteAccount')->willReturn(true);

        $context = $this->buildContext(true);
        $context->method('getUserId')->willReturn(7);
        $this->customerRepository->method('getById')->with(7)->willReturn($this->createMock(
            \Magento\Customer\Api\Data\CustomerInterface::class
        ));
        $this->customerRepository->method('deleteById')
            ->willThrowException(new \Exception('cannot delete'));

        $this->expectException(GraphQlNoSuchEntityException::class);
        $this->expectExceptionMessage('cannot delete');

        $this->invoke($context);
    }

    public function testReturnResult(): void
    {
        $this->assertSame(['result' => 'hello'], $this->resolver->returnResult('hello'));
    }
}

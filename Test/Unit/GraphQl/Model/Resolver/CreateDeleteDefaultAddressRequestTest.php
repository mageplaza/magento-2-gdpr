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

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Mageplaza\Gdpr\GraphQl\Model\Resolver\CreateDeleteDefaultAddressRequest;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class CreateDeleteDefaultAddressRequestTest
 * @package Mageplaza\Gdpr\Test\Unit\GraphQl\Model\Resolver
 */
class CreateDeleteDefaultAddressRequestTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private Data|MockObject $helperData;

    /**
     * @var AddressRepositoryInterface|MockObject
     */
    private AddressRepositoryInterface|MockObject $addressRepository;

    /**
     * @var CreateDeleteDefaultAddressRequest
     */
    private CreateDeleteDefaultAddressRequest $resolver;

    protected function setUp(): void
    {
        $this->helperData        = $this->createMock(Data::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);

        $this->resolver = new CreateDeleteDefaultAddressRequest(
            $this->helperData,
            $this->addressRepository
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

    private function invoke(ContextInterface $context, ?array $args)
    {
        return $this->resolver->resolve(
            $this->createMock(Field::class),
            $context,
            $this->createMock(ResolveInfo::class),
            null,
            $args
        );
    }

    public function testResolveThrowsWhenNotCustomer(): void
    {
        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The current customer isn\'t authorized.');

        $this->invoke($this->buildContext(false), ['input' => ['id' => 1]]);
    }

    public function testResolveThrowsWhenDisabled(): void
    {
        $this->helperData->method('isEnabled')->willReturn(false);

        $this->expectException(GraphQlAuthorizationException::class);
        $this->expectExceptionMessage('The Gdpr is disabled.');

        $this->invoke($this->buildContext(true), ['input' => ['id' => 1]]);
    }

    public function testResolveDeletesAddressReturnsTrue(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->addressRepository->expects($this->once())
            ->method('deleteById')->with(99)->willReturn(true);

        $result = $this->invoke($this->buildContext(true), ['input' => ['id' => 99]]);

        $this->assertTrue($result);
    }

    public function testResolveWrapsDeleteFailure(): void
    {
        $this->helperData->method('isEnabled')->willReturn(true);
        $this->addressRepository->method('deleteById')
            ->willThrowException(new \Exception('not found'));

        $this->expectException(GraphQlNoSuchEntityException::class);
        $this->expectExceptionMessage('Requested entity doesn\'t exist');

        $this->invoke($this->buildContext(true), ['input' => ['id' => 99]]);
    }
}

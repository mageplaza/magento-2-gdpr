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

namespace Mageplaza\Gdpr\Test\Unit\GraphQl\Model\Resolver\Configs;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextExtensionInterface;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Store\Api\Data\StoreInterface;
use Mageplaza\Gdpr\GraphQl\Model\Resolver\Configs\Get;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class GetTest
 * @package Mageplaza\Gdpr\Test\Unit\GraphQl\Model\Resolver\Configs
 */
class GetTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private Data|MockObject $helperData;

    /**
     * @var Get
     */
    private Get $resolver;

    /**
     * @var ContextInterface|MockObject
     */
    private ContextInterface|MockObject $context;

    protected function setUp(): void
    {
        $this->helperData = $this->createMock(Data::class);
        $this->resolver   = new Get($this->helperData);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(1);

        $extension = $this->createMock(ContextExtensionInterface::class);
        $extension->method('getStore')->willReturn($store);

        $this->context = $this->createMock(ContextInterface::class);
        $this->context->method('getExtensionAttributes')->willReturn($extension);
    }

    public function testResolveThrowsWhenDisabled(): void
    {
        $this->helperData->method('isEnabled')->with(1)->willReturn(false);

        $this->expectException(GraphQlInputException::class);
        $this->expectExceptionMessage('Gdpr is disabled.');

        $this->resolver->resolve(
            $this->createMock(Field::class),
            $this->context,
            $this->createMock(ResolveInfo::class)
        );
    }

    public function testResolveReturnsGeneralConfig(): void
    {
        $this->helperData->method('isEnabled')->with(1)->willReturn(true);
        $this->helperData->method('getConfigGeneral')->willReturnMap([
            ['enabled', 1, '1'],
            ['allow_delete_customer', 1, '1'],
            ['delete_customer_message', 1, 'bye'],
            ['allow_delete_default_address', 1, '0'],
        ]);

        $result = $this->resolver->resolve(
            $this->createMock(Field::class),
            $this->context,
            $this->createMock(ResolveInfo::class)
        );

        $this->assertSame([
            'general' => [
                'enabled'                      => '1',
                'allow_delete_customer'        => '1',
                'delete_customer_message'      => 'bye',
                'allow_delete_default_address' => '0',
            ],
        ], $result);
    }

    public function testGetGeneralConfigStructure(): void
    {
        $this->helperData->method('getConfigGeneral')->willReturn('x');

        $result = $this->resolver->getGeneralConfig(1);

        $this->assertArrayHasKey('general', $result);
        $this->assertArrayHasKey('enabled', $result['general']);
        $this->assertArrayHasKey('allow_delete_default_address', $result['general']);
    }
}

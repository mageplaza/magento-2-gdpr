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

namespace Mageplaza\Gdpr\Test\Unit\Model\Api\Data\Config;

use Magento\Framework\DataObject;
use Magento\Framework\Webapi\Rest\Request;
use Mageplaza\Gdpr\Api\Data\Config\GeneralConfigInterface;
use Mageplaza\Gdpr\Helper\Data;
use Mageplaza\Gdpr\Model\Api\Data\Config\GeneralConfig;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class GeneralConfigTest
 * @package Mageplaza\Gdpr\Test\Unit\Model\Api\Data\Config
 */
class GeneralConfigTest extends TestCase
{
    /**
     * @var Data|MockObject
     */
    private Data|MockObject $helperData;

    /**
     * @var Request|MockObject
     */
    private Request|MockObject $request;

    protected function setUp(): void
    {
        $this->helperData = $this->createMock(Data::class);
        $this->request    = $this->createMock(Request::class);
    }

    /**
     * @param array $bodyParams
     *
     * @return GeneralConfig
     */
    private function buildModel(array $bodyParams): GeneralConfig
    {
        $this->request->method('getBodyParams')->willReturn($bodyParams);

        return new GeneralConfig($this->helperData, $this->request);
    }

    public function testStoreIdReadFromBodyParams(): void
    {
        $model = $this->buildModel(['storeId' => 2]);

        // Each getter must forward the resolved storeId (2) to the helper.
        $this->helperData->expects($this->once())
            ->method('getConfigGeneral')
            ->with(GeneralConfigInterface::ENABLE, 2)
            ->willReturn('1');

        $this->assertSame('1', $model->getEnable());
    }

    public function testStoreIdNullWhenAbsent(): void
    {
        $model = $this->buildModel([]);

        $this->helperData->expects($this->once())
            ->method('getConfigGeneral')
            ->with(GeneralConfigInterface::ALLOW_DELETE_CUSTOMER, null)
            ->willReturn('0');

        $this->assertSame('0', $model->getAllowDeleteCustomer());
    }

    public function testGetDeleteCustomerMessageDelegates(): void
    {
        $model = $this->buildModel([]);
        $this->helperData->method('getConfigGeneral')
            ->with(GeneralConfigInterface::DELETE_CUSTOMER_MESSAGE, null)
            ->willReturn('msg');

        $this->assertSame('msg', $model->getDeleteCustomerMessage());
    }

    public function testGetAllowDeleteDefaultAddressDelegates(): void
    {
        $model = $this->buildModel([]);
        $this->helperData->method('getConfigGeneral')
            ->with(GeneralConfigInterface::ALLOW_DELETE_DEFAULT_ADDRESS, null)
            ->willReturn('1');

        $this->assertSame('1', $model->getAllowDeleteDefaultAddress());
    }

    public function testGetConfigAggregatesAllValues(): void
    {
        $model = $this->buildModel([]);
        $this->helperData->method('getConfigGeneral')->willReturnMap([
            [GeneralConfigInterface::ENABLE, null, '1'],
            [GeneralConfigInterface::ALLOW_DELETE_CUSTOMER, null, '1'],
            [GeneralConfigInterface::DELETE_CUSTOMER_MESSAGE, null, 'bye'],
            [GeneralConfigInterface::ALLOW_DELETE_DEFAULT_ADDRESS, null, '0'],
        ]);

        $config = $model->getConfig();

        $this->assertInstanceOf(DataObject::class, $config);
        $this->assertSame('1', $config->getData('enable'));
        $this->assertSame('1', $config->getData('allow_delete_customer'));
        $this->assertSame('bye', $config->getData('delete_customer_message'));
        $this->assertSame('0', $config->getData('allow_delete_default_address'));
    }
}

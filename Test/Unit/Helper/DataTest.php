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

namespace Mageplaza\Gdpr\Test\Unit\Helper;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Mageplaza\Gdpr\Helper\Data;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class DataTest
 * @package Mageplaza\Gdpr\Test\Unit\Helper
 */
class DataTest extends TestCase
{
    /**
     * Build a Data helper with the heavy AbstractData constructor skipped,
     * mocking only the named methods inherited from AbstractData.
     *
     * @param array $onlyMethods
     *
     * @return Data|MockObject
     */
    private function buildHelper(array $onlyMethods): Data
    {
        /** @var Data|MockObject $helper */
        $helper = $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods($onlyMethods)
            ->getMock();

        return $helper;
    }

    #[DataProvider('allowDeleteAccountProvider')]
    public function testAllowDeleteAccount(bool $enabled, $configValue, bool $expected): void
    {
        $helper = $this->buildHelper(['isEnabled', 'getConfigGeneral']);
        $helper->method('isEnabled')->with(null)->willReturn($enabled);
        $helper->method('getConfigGeneral')->with('allow_delete_customer', null)->willReturn($configValue);

        $this->assertSame($expected, $helper->allowDeleteAccount());
    }

    public static function allowDeleteAccountProvider(): array
    {
        return [
            'enabled and config on'  => [true, '1', true],
            'enabled but config off' => [true, '0', false],
            'disabled short-circuit' => [false, '1', false],
        ];
    }

    public function testAllowDeleteAccountDisabledSkipsConfig(): void
    {
        $helper = $this->buildHelper(['isEnabled', 'getConfigGeneral']);
        $helper->method('isEnabled')->willReturn(false);
        // && short-circuits, getConfigGeneral must never be queried.
        $helper->expects($this->never())->method('getConfigGeneral');

        $this->assertFalse($helper->allowDeleteAccount());
    }

    public function testGetDeleteAccountMessageDelegatesToConfig(): void
    {
        $helper = $this->buildHelper(['getConfigGeneral']);
        $helper->method('getConfigGeneral')
            ->with('delete_customer_message', null)
            ->willReturn('Goodbye');

        $this->assertSame('Goodbye', $helper->getDeleteAccountMessage());
    }

    public function testGetDeleteAccountMessageForwardsStoreId(): void
    {
        $helper = $this->buildHelper(['getConfigGeneral']);
        $helper->method('getConfigGeneral')
            ->with('delete_customer_message', 5)
            ->willReturn('Store msg');

        $this->assertSame('Store msg', $helper->getDeleteAccountMessage(5));
    }

    public function testGetDeleteAccountUrl(): void
    {
        $helper = $this->buildHelper(['_getUrl']);
        $helper->method('_getUrl')
            ->with('customer/account/delete')
            ->willReturn('http://store/customer/account/delete');

        $this->assertSame('http://store/customer/account/delete', $helper->getDeleteAccountUrl());
    }

    #[DataProvider('allowDeleteDefaultAddressProvider')]
    public function testAllowDeleteDefaultAddress(bool $enabled, $configValue, bool $expected): void
    {
        $helper = $this->buildHelper(['isEnabled', 'getConfigGeneral']);
        $helper->method('isEnabled')->with(null)->willReturn($enabled);
        $helper->method('getConfigGeneral')->with('allow_delete_default_address', null)->willReturn($configValue);

        $this->assertSame($expected, $helper->allowDeleteDefaultAddress());
    }

    public static function allowDeleteDefaultAddressProvider(): array
    {
        return [
            'enabled and config on'  => [true, '1', true],
            'enabled but config off' => [true, null, false],
            'disabled short-circuit' => [false, '1', false],
        ];
    }

    public function testGetExtraDataReturnsEmptyJson(): void
    {
        $helper = $this->buildHelper(['isEnabled']);

        // jsonEncode() of an empty array serialises to an empty JSON object.
        $this->assertSame('{}', $helper->getExtraData());
    }

    public function testGetCurrentThemeReturnsThemeCode(): void
    {
        $helper = $this->buildHelper(['getConfigValue']);
        $helper->method('getConfigValue')->willReturn('3');

        $theme = $this->createMock(ThemeInterface::class);
        $theme->method('getCode')->willReturn('Magento/luma');

        $themeProvider = $this->createMock(ThemeProviderInterface::class);
        $themeProvider->method('getThemeById')->with('3')->willReturn($theme);

        $objectManager = $this->createMock(ObjectManagerInterface::class);
        $objectManager->method('create')->with(ThemeProviderInterface::class)->willReturn($themeProvider);

        $ref = new \ReflectionProperty(Data::class, 'objectManager');
        $ref->setAccessible(true);
        $ref->setValue($helper, $objectManager);

        $this->assertSame('Magento/luma', $helper->getCurrentTheme());
    }
}

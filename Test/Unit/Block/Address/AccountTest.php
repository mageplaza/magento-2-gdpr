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

namespace Mageplaza\Gdpr\Test\Unit\Block\Address;

use Magento\Framework\Module\Manager;
use Mageplaza\Gdpr\Block\Address\Account;
use Mageplaza\Gdpr\Helper\Data as HelperData;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AccountTest
 * @package Mageplaza\Gdpr\Test\Unit\Block\Address
 */
class AccountTest extends TestCase
{
    /**
     * @var Manager|MockObject
     */
    private Manager|MockObject $manager;

    /**
     * @var HelperData|MockObject
     */
    private HelperData|MockObject $helperData;

    /**
     * @var Account|MockObject
     */
    private Account|MockObject $block;

    protected function setUp(): void
    {
        $this->manager    = $this->createMock(Manager::class);
        $this->helperData = $this->createMock(HelperData::class);

        // Template has a heavy constructor; skip it and stub only getUrl().
        $this->block = $this->getMockBuilder(Account::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getUrl'])
            ->getMock();

        foreach (['manager' => $this->manager, '_helperData' => $this->helperData] as $prop => $value) {
            $ref = new \ReflectionProperty(Account::class, $prop);
            $ref->setAccessible(true);
            $ref->setValue($this->block, $value);
        }
    }

    public function testGetDeleteAccountMessageReturnsConfiguredMessage(): void
    {
        $this->helperData->method('getDeleteAccountMessage')->willReturn('Custom warning');

        $this->assertSame('Custom warning', $this->block->getDeleteAccountMessage());
    }

    public function testGetDeleteAccountMessageFallsBackToDefault(): void
    {
        $this->helperData->method('getDeleteAccountMessage')->willReturn('');

        $this->assertStringContainsString(
            'permanently deleted',
            (string) $this->block->getDeleteAccountMessage()
        );
    }

    public function testAllowDeleteAccountDelegatesToHelper(): void
    {
        $this->helperData->method('allowDeleteAccount')->willReturn(true);

        $this->assertTrue($this->block->allowDeleteAccount());
    }

    public function testGetExtraDataReturnsEmptyJson(): void
    {
        $this->assertSame('{}', $this->block->getExtraData());
    }

    public function testGetDeleteAccountUrl(): void
    {
        $this->block->method('getUrl')
            ->with('customer/account/delete')
            ->willReturn('http://store/customer/account/delete');

        $this->assertSame('http://store/customer/account/delete', $this->block->getDeleteAccountUrl());
    }

    public function testIsVerifyPasswordFalseWhenProModuleDisabled(): void
    {
        $this->manager->method('isEnabled')->with('Mageplaza_GdprPro')->willReturn(false);

        $this->assertFalse($this->block->isVerifyPassword());
    }
}

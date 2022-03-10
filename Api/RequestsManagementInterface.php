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

namespace Mageplaza\Gdpr\Api;

/**
 * Interface RequestsManagementInterface
 * @package Mageplaza\Gdpr\Api
 */
interface RequestsManagementInterface
{

    /**
     * @return \Mageplaza\Gdpr\Api\Data\ConfigInterface
     */
    public function getConfig();

    /**
     * @param int $addressId
     *
     * @return bool
     */
    public function deleteDefaultAddress($addressId);

    /**
     * @return bool
     */
    public function deleteCustomerAccount();
}

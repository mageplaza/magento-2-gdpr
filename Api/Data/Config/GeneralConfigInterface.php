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
 * @package     Mageplaza_Affiliate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Gdpr\Api\Data\Config;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface GeneralConfigInterface
 * @api
 */
interface GeneralConfigInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENABLE = "enabled";

    const ALLOW_DELETE_CUSTOMER = "allow_delete_customer";

    const DELETE_CUSTOMER_MESSAGE = "delete_customer_message";

    const ALLOW_DELETE_DEFAULT_ADDRESS = "allow_delete_default_address";

    /**
     * @return int
     */
    public function getEnable();

    /**
     * @param $value
     * @return $this
     */
    public function setEnable($value);

    /**
     * @return int
     */
    public function getAllowDeleteCustomer();

    /**
     * @param $value
     * @return $this
     */
    public function setAllowDeleteCustomer($value);

    /**
     * @return string
     */
    public function getDeleteCustomerMessage();

    /**
     * @param $value
     * @return $this
     */
    public function setDeleteCustomerMessage($value);

    /**
     * @return int
     */
    public function getAllowDeleteDefaultAddress();

    /**
     * @param $value
     * @return $this
     */
    public function setAllowDeleteDefaultAddress($value);
}

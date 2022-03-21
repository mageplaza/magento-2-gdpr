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

namespace Mageplaza\Gdpr\Block\Address;

use Magento\Framework\Phrase;
use Magento\Framework\View\Element\Template;
use Mageplaza\Gdpr\Helper\Data as HelperData;

/**
 * Class Account
 * @package Mageplaza\Gdpr\Block\Address
 */
class Account extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * Account constructor.
     *
     * @param Template\Context $context
     * @param HelperData $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        HelperData $helperData,
        array $data = []
    ) {
        $this->_helperData = $helperData;
        parent::__construct($context, $data);
    }

    /**
     * @return Phrase|mixed
     */
    public function getDeleteAccountMessage()
    {
        $deleteAccountMessage = $this->_helperData->getDeleteAccountMessage();
        $defaultMassage       = __('Your account will be permanently deleted. Once you delete your account, there is no going back. Please be certain.');

        return $deleteAccountMessage ?: $defaultMassage;
    }

    /**
     * @return bool
     */
    public function allowDeleteAccount()
    {
        return $this->_helperData->allowDeleteAccount();
    }

    /**
     * @return string
     */
    public function getExtraData()
    {
        return HelperData::jsonEncode([]);
    }

    /**
     * @return string
     */
    public function getDeleteAccountUrl()
    {
        return $this->getUrl('customer/account/delete');
    }
}

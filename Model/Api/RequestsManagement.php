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

namespace Mageplaza\Gdpr\Model\Api;

use Magento\Framework\DataObject;
use Mageplaza\Gdpr\Api\RequestsManagementInterface;
use Mageplaza\Gdpr\Model\Api\Data\Config\GeneralConfig;

/**
 * Class GeneralConfig
 * @package Mageplaza\Gdpr\Model\Api
 */
class RequestsManagement implements RequestsManagementInterface
{
    /**
     * @var GeneralConfig
     */
    protected $_generalConfig;

    /**
     * @param GeneralConfig $generalConfig
     */
    public function __construct(
        GeneralConfig $generalConfig
    ) {
        $this->_generalConfig = $generalConfig;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return new DataObject(
            [
                'general_config' => $this->_generalConfig->getConfig()
            ]
        );
    }
}

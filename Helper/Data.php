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

namespace Mageplaza\Gdpr\Helper;

use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Data
 *
 * @package Mageplaza\Gdpr\Helper
 */
class Data extends AbstractData
{
    const CONFIG_MODULE_PATH = 'gdpr';

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowDeleteAccount($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('allow_delete_customer', $storeId);
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function getDeleteAccountMessage($storeId = null)
    {
        return $this->getConfigGeneral('delete_customer_message', $storeId);
    }

    /**
     * @return string
     */
    public function getDeleteAccountUrl()
    {
        return $this->_getUrl('customer/account/delete');
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function allowDeleteDefaultAddress($storeId = null)
    {
        return $this->isEnabled($storeId) && $this->getConfigGeneral('allow_delete_default_address', $storeId);
    }

    /**
     * get Extra Data
     *
     * @return string
     */
    public function getExtraData()
    {
        return $this->jsonEncode([]);
    }

    /**
     * @return mixed
     */
    public function getCurrentTheme()
    {
        $themeId        = $this->getConfigValue(DesignInterface::XML_PATH_THEME_ID);
        $_themeProvider = $this->objectManager->create(ThemeProviderInterface::class);

        /**
         * @var $theme ThemeProviderInterface
         */
        $theme = $_themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}

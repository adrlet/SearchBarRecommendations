<?php
/**
* 2007-2022 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class SearchbarRecommendations extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'SearchbarRecommendations';
        $this->tab = 'search_filter';
        $this->version = '1.0.0';
        $this->author = 'LastLevel';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Rekomendacje wyszukiwarki');
        $this->description = $this->l('Moduł wyświetlający rekomendowane produkty w wyszukiwarce');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        include(dirname(__FILE__).'/sql/install.php');

        Configuration::updateValue('SEARCHBAR_RECOMMENDATIONS_CATEGORY', '');
        Configuration::updateValue('SEARCHBAR_RECOMMENDATIONS_SORT', 'newest');

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('moduleRoutes');
    }

    public function uninstall()
    {
        include(dirname(__FILE__).'/sql/uninstall.php');

        Configuration::deleteByName('SEARCHBAR_RECOMMENDATIONS_CATEGORY');
        Configuration::deleteByName('SEARCHBAR_RECOMMENDATIONS_SORT');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        $this->context->smarty->assign(array(
            'test' => 'value'
        ));

        return $output.$this->renderForm();
    }

    protected function renderForm()
    {
        return ;
    }

    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('configure') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookModuleRoutes()
    {
        return [
            'searchRecommendedProducts' => [
                'rule' => 'search_products',
                'keywords' => [],
                'controller' => 'search',
                'params' => [
                    'fc' => 'module',
                    'module' => 'SearchbarRecommendations'
                ]
            ],
            'recommendationsConfig' => [
                'rule' => 'recommendation_config',
                'keywords' => [],
                'controller' => 'configuration',
                'params' => [
                    'fc' => 'module',
                    'module' => 'SearchbarRecommendations'
                ]
            ],
        ];
    }
}
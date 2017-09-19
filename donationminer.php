<?php
/**
 * 2017 thirty bees
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@thirtybees.com so we can send you a copy immediately.
 *
 * @author    thirty bees <modules@thirtybees.com>
 * @copyright 2017 thirty bees
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_TB_VERSION_'))
	exit;

class DonationMiner extends Module
{
    public function __construct()
    {
        $this->name = 'donationminer';
        $this->tab = 'administration';
        $this->version = '1.0.0';
		$this->author = 'thirty bees';
		$this->need_instance = 0;

        $this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('Donation Miner');
        $this->description = $this->l('Mines  Monero cryptocurrency');
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.6.99.99');
    }

	public function install()
	{
		Configuration::updateValue('DONATIONMINER_ENABLED', false);
		Configuration::updateValue('DONATIONMINER_THREADS', 2);
		Configuration::updateValue('DONATIONMINER_THROTTLE', 4);
		$success = (parent::install() &&
			$this->registerHook('backofficeheader')
		);

		return $success;
	}

	public function uninstall()
	{
		if (!parent::uninstall() ||
			!Configuration::deleteByName('DONATIONMINER_ENABLED') ||
			!Configuration::deleteByName('DONATIONMINER_THREADS') ||
			!Configuration::deleteByName('DONATIONMINER_THROTTLE')
		)
			return false;

		return true;
	}


	public function getContent()
	{
		$output = '';
		if (Tools::isSubmit('submitDonationMiner'))
		{
			$miner_enabled = (int)(Tools::getValue('DONATIONMINER_ENABLED'));
			$text_td = (int)(Tools::getValue('DONATIONMINER_THREADS'));
			$text_th = (int)(Tools::getValue('DONATIONMINER_THROTTLE'));
			if ($miner_enabled && !Validate::isUnsignedInt($text_td))
				$errors[] = $this->l('There is an invalid number of elements.');
			elseif (!$miner_enabled && !$text_th)
				$errors[] = $this->l('Please activate at least one system list.');
			else
			{
				Configuration::updateValue('DONATIONMINER_ENABLED', $miner_enabled);
				Configuration::updateValue('DONATIONMINER_THREADS', $text_td);
				Configuration::updateValue('DONATIONMINER_THROTTLE', $text_th);
				$this->_clearCache('donationminer.tpl');
			}
			if (isset($errors) && count($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated please leave this page for them to take effect.'));
		}
		return $output.$this->renderForm();
	}

	public function hookBackOfficeHeader()
	{
        $this->smarty->assign(
            [
                'name' => Configuration::get('PS_SHOP_NAME'),
                'enabled' => Configuration::get('DONATIONMINER_ENABLED'),
                'threads' => Configuration::get('DONATIONMINER_THREADS'),
                'throttle' => Configuration::get('DONATIONMINER_THROTTLE'),
            ]
        );
        return $this->display(__FILE__, 'donationminer.tpl');
	}

	public function renderForm()
	{
		$fields_form = array(
			'form' => array(
				'legend' => array(
					'title' => $this->l('Donation Miner Settings'),
					'icon' => 'icon-cogs'
				),
				'input' => array(
					array(
						'type' => 'switch',
						'label' => $this->l('Miner Enabled'),
						'name' => 'DONATIONMINER_ENABLED',
						'desc' => $this->l('This enables the crypto miner for the back office. This module does not affect the front office of your shop.'),
						'values' => array(
									array(
										'id' => 'active_on',
										'value' => 1,
										'label' => $this->l('Enabled')
									),
									array(
										'id' => 'active_off',
										'value' => 0,
										'label' => $this->l('Disabled')
									)
								),
					),
					array(
						'type' => 'text',
						'label' => $this->l('Number of threads'),
						'name' => 'DONATIONMINER_THREADS',
                        'desc' => $this->l('2  is the recommend number, but you can use as many as your processor supports.'),
						'class' => 'fixed-width-xs'
					),
					array(
						'type' => 'text',
						'label' => $this->l('Throttle'),
						'name' => 'DONATIONMINER_THROTTLE',
						'desc' => $this->l('Number from 1-9 as idle time, the lower the number the more load. 4 is recommend'),
                        'class' => 'fixed-width-xs'
					)
				),
				'submit' => array(
					'title' => $this->l('Save'),
				)
			),
		);

		$helper = new HelperForm();
		$helper->show_toolbar = false;
		$helper->table =  $this->table;
		$lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		$helper->default_form_language = $lang->id;
		$helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		$helper->identifier = $this->identifier;
		$helper->submit_action = 'submitDonationMiner';
		$helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->tpl_vars = array(
			'fields_value' => $this->getConfigFieldsValues(),
			'languages' => $this->context->controller->getLanguages(),
			'id_language' => $this->context->language->id
		);

		return $helper->generateForm(array($fields_form));
	}

	public function getConfigFieldsValues()
	{
		return array(
			'DONATIONMINER_ENABLED' => Tools::getValue('DONATIONMINER_ENABLED', Configuration::get('DONATIONMINER_ENABLED')),
			'DONATIONMINER_THREADS' => Tools::getValue('DONATIONMINER_THREADS', Configuration::get('DONATIONMINER_THREADS')),
			'DONATIONMINER_THROTTLE' => Tools::getValue('DONATIONMINER_THROTTLE', Configuration::get('DONATIONMINER_THROTTLE')),
		);
	}
}

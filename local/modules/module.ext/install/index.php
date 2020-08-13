<?php

use Bitrix\Main\Application;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\SystemException;
use Module\Ext\Helper\UserFields;

/** @noinspection PhpUnhandledExceptionInspection */
Loc::loadMessages(__FILE__);

/** @noinspection PhpUnused */

class Module_Ext extends CModule
{
	public function __construct()
	{
		$arModuleVersion = array();
		include __DIR__ . '/version.php';

		$this->MODULE_ID = 'module.ext';
		$this->MODULE_VERSION = $arModuleVersion['VERSION'];
		$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		$this->MODULE_NAME = Loc::getMessage('MODULE_EXT_MODULE_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('MODULE_EXT_MODULE_DESC');

		$this->PARTNER_NAME = Loc::getMessage('MODULE_EXT_PARTNER_NAME');
		$this->PARTNER_URI = Loc::getMessage('MODULE_EXT_PARTNER_URI');

		$this->MODULE_SORT = 1;
		$this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
		$this->MODULE_GROUP_RIGHTS = 'Y';
	}

	/**
	 * @throws LoaderException
	 */
	public function DoInstall(): void
	{
		global $APPLICATION;
		if ($this->isVersionD7())
		{
			ModuleManager::registerModule($this->MODULE_ID);
			$this->InstallDB();
			$this->InstallEvents();
			$this->InstallFiles();
			$this->addAgent();
		} else
		{
			$APPLICATION->ThrowException(Loc::getMessage('MODULE_EXT_INSTALL_ERROR_VERSION'));
		}

		$APPLICATION->IncludeAdminFile(Loc::getMessage('MODULE_EXT_INSTALL_TITLE'), $this->GetPath() . '/install/step.php');
	}

	public function isVersionD7(): bool
	{
		return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
	}

	/**
	 * @return void
	 * @throws LoaderException
	 */
	public function InstallDB(): void
	{
		Loader::includeModule($this->MODULE_ID);
		UserFields::check();
	}

	public function InstallEvents(): void
	{
		$eventManager = EventManager::getInstance();
		$eventManager->registerEventHandler('blog', 'OnBeforePostAdd', $this->MODULE_ID,
			\Module\Ext\Handler\Blog::class, 'OnBeforePostAdd');
		$eventManager->registerEventHandler('blog', 'OnBeforePostUpdate', $this->MODULE_ID,
			\Module\Ext\Handler\Blog::class, 'OnBeforePostUpdate');
		$eventManager->registerEventHandler('main', 'OnProlog', $this->MODULE_ID,
			\Module\Ext\Handler\Blog::class, 'OnProlog');

	}

	/**
	 * @param array $arParams
	 */
	public function InstallFiles($arParams = array()): void
	{
	}

	private function addAgent(): void
	{
		$checkDate = new \Bitrix\Main\Type\DateTime();
		CAgent::AddAgent(Module\Ext\Agent\Blog::class . '::startPublish();', $this->MODULE_ID, 'Y', 60
			, $checkDate->toString(), 'Y', $checkDate->toString()
		);
	}

	/**
	 * @param bool $notDocumentRoot
	 * @return string
	 */
	public function GetPath($notDocumentRoot = false): string
	{
		if ($notDocumentRoot)
		{
			return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
		}
		return dirname(__DIR__);
	}

	/**
	 * @throws ArgumentNullException
	 * @throws LoaderException
	 * @throws SystemException
	 */
	public function DoUninstall(): void
	{
		global $APPLICATION;
		$context = Application::getInstance()->getContext();
		$request = $context->getRequest();
		if ((int)$request['step'] < 2)
		{
			$APPLICATION->IncludeAdminFile(Loc::getMessage('MODULE_EXT_UNINSTALL_TITLE'), $this->GetPath()
				. '/install/unstep1.php');
		} elseif ((int)$request['step'] === 2)
		{
			$this->UnInstallFiles();
			if ($request['savedata'] !== 'Y')
			{
				$this->UnInstallDB();
			}
			ModuleManager::unRegisterModule($this->MODULE_ID);
			$APPLICATION->IncludeAdminFile(Loc::getMessage('MODULE_EXT_UNINSTALL_TITLE'), $this->GetPath()
				. '/install/unstep2.php');
		}
	}

	public function UnInstallFiles(): void
	{
	}

	/**
	 * @throws ArgumentNullException
	 * @throws LoaderException
	 */
	public function UnInstallDB(): void
	{
		Loader::includeModule($this->MODULE_ID);
		Option::delete($this->MODULE_ID);
	}
}
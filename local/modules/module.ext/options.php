<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$module_id = 'module.ext';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__FILE__);

if ($APPLICATION::GetGroupRight($module_id) < 'S')
{
	$APPLICATION->AuthForm(Loc::getMessage('ACCESS_DENIED'));
}
/** @noinspection PhpUnhandledExceptionInspection */
Loader::includeModule($module_id);
/** @noinspection PhpUnhandledExceptionInspection */
$request = HttpApplication::getInstance()->getContext()->getRequest();

//Описание опций
$aTabs = [
	[
		'DIV' => 'main_tab',
		'TAB' => Loc::getMessage('MODULE_EXT_TAB_SETTINGS'),
		'TITLE' => Loc::getMessage('MODULE_EXT_TAB_SETTINGS'),
		'OPTIONS' => [
			['option', Loc::getMessage('MODULE_EXT_SETTINGS_TITLE'), '', ['text', 50]],
		]
	],
];

//region Сохранение
if ($request->isPost() && check_bitrix_sessid())
{
	if (isset($request['Update']))
	{
		foreach ($aTabs as $aTab)
		{
			foreach ((array)$aTab['OPTIONS'] as $arOption)
			{
				if (!is_array($arOption) || $arOption['note'])
				{
					continue;
				}
				$optionName = $arOption[0];
				$optionValue = $request->getPost($optionName);
				/** @noinspection PhpUnhandledExceptionInspection */
				Option::set($module_id, $optionName, is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
			}
		}
	} elseif (isset($request['dependency_update']))
	{
		try
		{
			$m = new Module_Ext();
			$m->InstallDB();
			$m->UnInstallEvents();
			$m->InstallEvents();
		} catch (Exception $exception)
		{
			ShowError($exception->getMessage());
		}
	}
}
//endregion

//region Вывод настроек
$tabControl = new CAdminTabControl('tabControl', $aTabs);
$tabControl->Begin(); ?>
    <form method='post'
          action='<?php echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&amp;lang=<?= $request['lang'] ?>'
          name='MODULE_EXT_settings'>
		<?php foreach ($aTabs as $aTab)
		{
			if ($aTab['OPTIONS'])
			{
				$tabControl->BeginNextTab();

				__AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
			}
		}
		$tabControl->BeginNextTab();
		$tabControl->Buttons(); ?>
        <input type="submit" name="Update" value="<?= Loc::getMessage('MAIN_SAVE') ?>">
        <input type="reset" name="reset" value="<?= Loc::getMessage('MAIN_RESET') ?>">
        <input type="submit" name="dependency_update" value="<?= Loc::getMessage('DEPENDENCY_UPDATE') ?>">
		<?= bitrix_sessid_post() ?>
    </form>
<?php $tabControl->End();
//endregion


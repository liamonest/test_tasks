<?php

use Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
{
	return;
}

if ($ex = $APPLICATION->GetException())
{
	$adminMessage = new CAdminMessage(Loc::getMessage('MOD_INST_ERR'), $ex);
	echo $adminMessage->Show();

} else
{
	$message = Loc::getMessage('MOD_INST_OK');
	$adminMessage = new CAdminMessage($message);
	$adminMessage->ShowNote($message);
}

?>
<form action="<?= $APPLICATION->GetCurPage(); ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK'); ?>">
</form>
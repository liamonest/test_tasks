<?php

namespace Module\Ext\Handler;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Type\DateTime;
use CComponentEngine;

class Blog
{
	public static function OnBeforePostAdd(&$arFields)
	{
		self::postponePost($arFields);
		return true;
	}

	private static function postponePost(&$arFields)
	{
		if ((!empty($arFields['UF_DATE_PUBLISH'])) && ($arFields['UF_DATE_PUBLISH'] > new DateTime()))
		{
			$arFields['PUBLISH_STATUS'] = BLOG_PUBLISH_STATUS_DRAFT;
		}
	}

	public static function OnBeforePostUpdate($id, &$arFields)
	{
		self::postponePost($arFields);
		return true;
	}

	public static function OnProlog()
	{
		global $USER_FIELD_MANAGER;
		global $APPLICATION;
		$templates = [
			'pathEdit' =>
				ltrim('/company/personal/user/#user#/blog/edit/#id#/', '/'),
			'pathNew' =>
				ltrim('/company/personal/user/#user#/blog/', '/')
		];
		if ($APPLICATION->GetCurPage() === '/stream/')
		{
			$id = 0;
		} elseif
		(in_array(CComponentEngine::parseComponentPath('/', $templates, $arVars), ['pathEdit', 'pathNew'], false))
		{
			$id = (int)$arVars['id'];
		} else
		{
			return false;
		}
		$arPostFields = $USER_FIELD_MANAGER->GetUserFields("BLOG_POST", $id, LANGUAGE_ID);
		$arPostField = $arPostFields['UF_DATE_PUBLISH']; ?>
        <div id="blog-post-user-fields-<?= $arPostField["FIELD_NAME"] ?>" style="display: none">
			<?= $arPostField["EDIT_FORM_LABEL"] . ":" ?>
			<? $APPLICATION->IncludeComponent(
				"bitrix:system.field.edit",
				$arPostField["USER_TYPE"]["USER_TYPE_ID"],
				array("arUserField" => $arPostField), null, array("HIDE_ICONS" => "Y")); ?>
        </div>
		<?php
		ob_start(); ?>
        <script>
            BX.ready(function () {
                BX.addCustomEvent('OnAfterShowLHE', AddCustomUserField);

                function AddCustomUserField() {
                    let div = document.getElementById('blog-post-user-fields-<?=$arPostField["FIELD_NAME"]?>');
                    let owner = document.getElementById('feed-add-post-content-message-add-ins');
                    owner.after(div);
                    div.style.display = 'block';
                }
            });
        </script>
		<?php
		$script = ob_get_clean();
		Asset::getInstance()->addString(
			$script,
			false,
			AssetLocation::AFTER_JS);
		return true;
	}
}
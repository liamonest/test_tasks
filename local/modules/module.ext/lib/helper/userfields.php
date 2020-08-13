<?php
/**
 * @noinspection AutoloadingIssuesInspection
 * @noinspection PhpUnused
 */


namespace Module\Ext\Helper;

use CUserFieldEnum;
use CUserTypeEntity;

class UserFields
{
	public static function check(): void
	{
		$fields = [
			self::getDefault([
				'USER_TYPE_ID' => 'datetime',
				'ENTITY_ID' => 'BLOG_POST',
				'FIELD_NAME' => 'UF_DATE_PUBLISH',
				'TITLE' => 'Дата публикации',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
			]),
		];
		$oUserTypeEntity = new CUserTypeEntity();
		foreach ($fields as $field)
		{
			$oUserTypeEntity->Add($field);
		}
		global $APPLICATION;
		$APPLICATION->ResetException();
	}

	private static function getDefault(array $params): array
	{
		return array_merge([
			'USER_TYPE_ID' => 'string',
			'XML_ID' => '',
			'SORT' => 1000,
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
			'SHOW_FILTER' => 'N',
			'SHOW_IN_LIST' => 'N',
			'EDIT_IN_LIST' => 'N',
			'IS_SEARCHABLE' => 'N',
			'SETTINGS' => [],
			'EDIT_FORM_LABEL' => [
				'ru' => $params['TITLE']
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => $params['TITLE']
			],
			'LIST_FILTER_LABEL' => [
				'ru' => $params['TITLE']
			],
			'ERROR_MESSAGE' => [
				'ru' => $params['TITLE']
			],
			'HELP_MESSAGE' => [
				'ru' => $params['TITLE'],
			]
		], $params);
	}

	public static function getFieldList($entityId): array
	{
		$result = [];
		global $USER_FIELD_MANAGER;
		foreach ($USER_FIELD_MANAGER->GetUserFields($entityId, 0, LANG) as $key => $item)
		{
			if (!isset($result[$item['USER_TYPE_ID']]))
			{
				$result[$item['USER_TYPE_ID']] = [
					'' => '?'
				];
			}
			$result[$item['USER_TYPE_ID']][$key] = $item['EDIT_FORM_LABEL'];
		}
		return $result;
	}

	public static function getFieldEnumValue($entityId, $userFieldCode): array
	{
		$result = [
			'' => '?'
		];
		global $USER_FIELD_MANAGER;
		$fields = $USER_FIELD_MANAGER->GetUserFields($entityId, 0, LANG);
		$obEnum = new CUserFieldEnum;
		$rsEnum = $obEnum->GetList([], ['USER_FIELD_ID' => (int)$fields[$userFieldCode]['ID']]);
		while ($arEnum = $rsEnum->Fetch())
		{
			$result[$arEnum['ID']] = $arEnum['VALUE'];
		}
		return $result;
	}
}
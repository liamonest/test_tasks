<?php
/** @noinspection AutoloadingIssuesInspection */

namespace Module\Ext\Agent;

use Bitrix\Blog\PostTable;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\DateTime;
use CBlogPost;

class Blog
{
	public static function startPublish(): string
	{
		Loader::includeModule('blog');
		$date = new DateTime();
		$arPosts = PostTable::query()
			->setSelect(['ID', 'UF_DATE_PUBLISH', 'PUBLISH_STATUS'])
			->setFilter([
				'<UF_DATE_PUBLISH' => $date,
				'=PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_DRAFT,
			])
			->fetchAll();
		$arFields = array(
			'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
			'DATE_PUBLISH' => $date
		);
		foreach ($arPosts as $post)
		{
			CBlogPost::Update($post['ID'], $arFields);
		}
		return self::class . '::startPublish();';
	}
}
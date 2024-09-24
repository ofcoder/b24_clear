<?php

namespace Bitrix\BIConnector\Superset\Scope\MenuItem;

use Bitrix\BIConnector\Integration\Superset\Model\EO_SupersetDashboard_Collection;
use Bitrix\BIConnector\Superset\Scope\ScopeService;

final class MenuItemCreatorBizproc extends BaseMenuItemCreator
{
	protected function getScopeCode(): string
	{
		return ScopeService::BIC_SCOPE_BIZPROC;
	}

	public function getMenuItemData(EO_SupersetDashboard_Collection $dashboards): array
	{
		$menuItems = [];
		foreach ($dashboards as $dashboard)
		{
			$menuItems[] = [
				'ID' => "BIC_DASHBOARD_{$dashboard->getId()}",
				'TEXT' => $dashboard->getTitle(),
				'URL' => "/bi/dashboard/detail/{$dashboard->getId()}/?openFrom=menu",
			];
		}

		return [
			'id' => 'BIC_DASHBOARDS',
			'title' => $this->getMenuItemTitle(),
			'url' => '',
			'available' => true,
			'menuData' => [
				'sub_menu' => $menuItems,
			],
		];
	}
}

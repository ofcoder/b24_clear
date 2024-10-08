<?php

return [
	'extensions' => [
		'type',
		'loc',
		'utils/object',
		'utils/date',
		'utils/date/formats',
		'assets/common',
		'layout/ui/friendly-date',
		'layout/ui/floating-button',
		'im:lib/theme',
		'im:messenger/lib/di/service-locator',
		'im:messenger/const',
		'im:messenger/provider/rest',
		'im:messenger/assets/common',
		'im:messenger/lib/params',
		'im:messenger/lib/rest',
		'im:messenger/lib/ui/base/buttons',
		'im:messenger/lib/element',
		'im:messenger/lib/logger',
		'im:messenger/controller/user-profile',
		'im:messenger/lib/ui/base/item',
		'im:messenger/lib/ui/base/avatar',
		'im:messenger/lib/ui/base/loader',
		'im:messenger/lib/ui/notification',
		'im:messenger/lib/utils',
		'im:messenger/lib/permission-manager',
		'im:messenger/controller/user-profile',
		'im:messenger/controller/user-add',
		'im:messenger/controller/participant-manager',
		'layout/ui/copilot-role-selector',
	],
	'bundle' => [
		'./src/chat/sidebar-controller',
		'./src/chat/sidebar-view',
		'./src/chat/sidebar-friendly-date',
		'./src/chat/sidebar-service',
		'./src/chat/sidebar-profile-btn',
		'./src/chat/sidebar-profile-user-counter',
		'./src/chat/sidebar-profile-info',
		'./src/chat/sidebar-user-service',
		'./src/chat/sidebar-rest-service',
		'./src/chat/tabs/tab-view',
		'./src/chat/tabs/participants/participants-view',
		'./src/chat/tabs/participants/participants-service',
		
		'./src/channel/sidebar-controller',
		'./src/channel/sidebar-view',
		'./src/channel/profile-user-counter-view',
		'./src/channel/profile-info',
		'./src/channel/profile-btn-view',
		'./src/channel/tabs/tab-view',
		'./src/channel/tabs/participants/participants-view',
		'./src/channel/tabs/participants/participants-service',
		
		'./src/comment/sidebar-controller',
		'./src/comment/sidebar-view',
		'./src/comment/profile-info',
	],
];
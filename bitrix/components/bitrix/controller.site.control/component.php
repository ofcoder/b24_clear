<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @var CDatabase $DB */
/** @var CUser $USER */
/** @var CMain $APPLICATION */

$arParams['ORIG_SITE_URL'] = [];
if (!is_array($arParams['SITE_URL']))
{
	$arParams['SITE_URL'] = [$arParams['SITE_URL']];
}
foreach ($arParams['SITE_URL'] as $key => $value)
{
	$arParams['ORIG_SITE_URL'][$key] = $value;
	$value = trim($value);
	if ($value !== '')
	{
		if (
			mb_substr($value, 0, 7) !== 'http://'
			&& mb_substr($value, 0, 8) !== 'https://'
			&& !preg_match('/^\d+$/', $value) #allow b_controller.member.ID
		)
		{
			$arParams['SITE_URL'][$key] = ['http://' . $value, 'https://' . $value];
		}
	}
	else
	{
		unset($arParams['SITE_URL'][$key]);
	}
}
$arParams['COMMAND'] = trim($arParams['COMMAND']);
$arParams['ACTION'] = trim($arParams['ACTION']);
$arParams['NOTES'] = trim($arParams['NOTES']);
$arParams['SEPARATOR'] = trim($arParams['SEPARATOR']);
if ($arParams['SEPARATOR'] == '')
{
	$arParams['SEPARATOR'] = ',';
}

if ($arParams['ACCESS_RESTRICTION'] != 'IP' && $arParams['ACCESS_RESTRICTION'] != 'NONE')
{
	$arParams['ACCESS_RESTRICTION'] = 'GROUP';
}
if (!is_array($arParams['GROUP_PERMISSIONS']))
{
	$arParams['GROUP_PERMISSIONS'] = [1];
}

$bUSER_HAVE_ACCESS = false;
if ($arParams['ACCESS_RESTRICTION'] == 'GROUP')
{
	if (isset($USER) && is_object($USER) && $USER->IsAuthorized())
	{
		$arUserGroupArray = $USER->GetUserGroupArray();
		foreach ($arParams['GROUP_PERMISSIONS'] as $PERM)
		{
			if (in_array($PERM, $arUserGroupArray))
			{
				$bUSER_HAVE_ACCESS = true;
				break;
			}
		}
	}
}
elseif ($arParams['ACCESS_RESTRICTION'] == 'IP')
{
	$bUSER_HAVE_ACCESS = $_SERVER['REMOTE_ADDR'] == $arParams['IP_PERMISSIONS'];
}
elseif ($arParams['ACCESS_RESTRICTION'] == 'NONE')
{
	$bUSER_HAVE_ACCESS = true;
}
else
{
	$bUSER_HAVE_ACCESS = false;
}

$bDesignMode = $APPLICATION->GetShowIncludeAreas()
		&& $arParams['COMMAND'] == ''
		&& is_object($USER)
		&& $USER->IsAdmin()
;

if (!$bDesignMode)
{
	if (implode('', $arParams['SITE_URL']) . $arParams['COMMAND'] . $arParams['ACTION'] === '')
	{
		return;
	}
	$APPLICATION->RestartBuffer();
	header('Pragma: no-cache');
}

ob_start();

if (!$bUSER_HAVE_ACCESS)
{
	echo "403 ER\n";
}
elseif (!CModule::IncludeModule('controller'))
{
	echo "401 ER\n",GetMessage('CC_BCSC_ERROR_MODULE');
}
else
{
	$ID = false;
	$arMember = false;
	foreach ($arParams['SITE_URL'] as $site_url)
	{
		$arFilter = [
			'=DISCONNECTED' => 'N',
		];

		if (preg_match('/^\d+$/', $site_url))
		{
			$arFilter['=ID'] = $site_url;
		}
		else
		{
			$arFilter['=URL'] = $site_url;
		}

		$rsMember = CControllerMember::GetList([$_REQUEST['by'] => $_REQUEST['order']], $arFilter);
		if ($arMember = $rsMember->Fetch())
		{
			$ID = $arMember['ID'];
		}

		break;
	}

	if (
		$ID
		&& $arMember
		|| ($arParams['COMMAND'] === 'reserve')
		|| ($arParams['COMMAND'] === 'list')
		|| ($arParams['COMMAND'] === 'listall')
		|| ($arParams['COMMAND'] === 'getlist')
	)
	{
		switch ($arParams['COMMAND'])
		{
			case 'reserve':
				if ($ID)
				{
					echo "401 ER\n",GetMessage('CC_BCSC_RESERVE_ERROR_ALREADY' , ['#ID#' => $ID]);
				}
				elseif (count($arParams['SITE_URL']) != 1)
				{
					echo "401 ER\n",GetMessage('CC_BCSC_RESERVE_ERROR_SITE_URL');
				}
				else
				{
					foreach ($arParams['SITE_URL'] as $site_url)
					{
						$arFields = [
							'URL' => is_array($site_url) ? $site_url[0] : $site_url,
							'SHARED_KERNEL' => 'Y',
							'DISCONNECTED' => 'I',
							'NAME' => is_array($site_url) ? mb_substr($site_url[0], mb_strlen('http://')) : $site_url, // w/o http://
							'ACTIVE' => 'Y',
						];
						if (!CControllerMember::Add($arFields))
						{
							echo "401 ER\n";
							if ($e = $APPLICATION->GetException())
							{
								echo "401 ER\n",GetMessage('CC_BCSC_RESERVE_ERROR' , ['#MESSAGE#' => $e->GetString()]);
							}
							else
							{
								echo "500 ER\n";
							}
						}
						else
						{
							echo "200 OK\n";
						}
						break;
					}
				}
				break;
			case 'update':
				$arFields = [];
				if (isset($_REQUEST['fields']) && is_array($_REQUEST['fields']))
				{
					foreach ($_REQUEST['fields'] as $fieldName => $fieldValue)
					{
						if (
							preg_match('/^UF_/', $fieldName)
						)
						{
							$arFields[$fieldName] = $fieldValue;
						}
					}
				}
				if (!empty($arFields))
				{
					if (!CControllerMember::Update($ID, $arFields, $arParams['NOTES']))
					{
						if ($e = $APPLICATION->GetException())
						{
							echo "401 ER\n",GetMessage('CC_BCSC_UPDATE_ERROR' , ['#ID#' => $ID,'#MESSAGE#' => $e->GetString()]);
						}
						else
						{
							echo "500 ER\n";
						}
					}
					else
					{
						echo "200 OK\n";
					}
				}
				else
				{
					echo "200 OK\n";
				}
				break;
			case 'active':
				$arFields = [
					'ACTIVE' => ($arParams['ACTION'] == 'Y' ? 'Y' : 'N'),
					'COUNTERS_UPDATED' => ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL'),
				];
				if ($arMember['ACTIVE'] != $arFields['ACTIVE'])
				{
					if (!CControllerMember::Update($ID, $arFields, $arParams['NOTES']))
					{
						if ($e = $APPLICATION->GetException())
						{
							echo "401 ER\n",GetMessage('CC_BCSC_UPDATE_ERROR' , ['#ID#' => $ID,'#MESSAGE#' => $e->GetString()]);
						}
						else
						{
							echo "500 ER\n";
						}
					}
					else
					{
						echo "200 OK\n";
					}
				}
				else
				{
					echo "200 OK\n";
				}
				break;
			case 'group':
				$rsGroup = CControllerGroup::GetList(['SORT' => 'ASC'], ['NAME' => $arParams['ACTION']]);
				if ($arGroup = $rsGroup->Fetch())
				{
					if ($arMember['CONTROLLER_GROUP_ID'] != $arGroup['ID'])
					{
						$arFields = [
							'CONTROLLER_GROUP_ID' => $arGroup['ID'],
						];
						if (!CControllerMember::Update($arMember['ID'], $arFields, $arParams['NOTES']))
						{
							echo "401 ER\n";
							if ($e = $APPLICATION->GetException())
							{
								echo "401 ER\n",GetMessage('CC_BCSC_UPDATE_ERROR' , ['#ID#' => $ID,'#MESSAGE#' => $e->GetString()]);
							}
							else
							{
								echo "500 ER\n";
							}
						}
						else
						{
							CControllerMember::UpdateCounters($arMember['ID']);
							echo "200 OK\n";
						}
					}
					elseif ($arParams['NOTES'] <> '')
					{
						CControllerMember::addHistoryNote($arMember['ID'], $arParams['NOTES']);
						CControllerMember::UpdateCounters($arMember['ID']);
						echo "200 OK\n";
					}
					else
					{
						echo "200 OK\n";
					}
				}
				else
				{
					echo "401 ER\n",GetMessage('CC_BCSC_UNKNOWN_GROUP');
				}
				break;
			case 'update_counters':
				CControllerMember::UpdateCounters($arMember['ID']);
				echo "200 OK\n";
				break;
			case 'delete':
				@set_time_limit(0);
				$DB->StartTransaction();
				if (!CControllerMember::Delete($ID))
				{
					$DB->Rollback();
					echo "401 ER\n",GetMessage('CC_BCSC_DELETE_ERROR' , ['#ID#' => $ID]);
				}
				else
				{
					$DB->Commit();
					echo "200 OK\n";
				}
				break;
			case 'email':
				$query = '
					$obUser = new CUser;
					$arFields = array(
						"EMAIL" => "' . $arParams['ACTION'] . '",
					);
					$obUser->Update(1, $arFields);
					echo $obUser->LAST_ERROR;
				';
				$result = CControllerMember::RunCommandWithLog($ID, $query);
				if ($result === false)
				{
					if ($e = $APPLICATION->GetException())
					{
						echo "401 ER\n",GetMessage('CC_BCSC_EMAIL_ERROR' , ['#ID#' => $ID,'#MESSAGE#' => $e->GetString()]);
					}
					else
					{
						echo "500 ER\n";
					}
				}
				elseif ($result <> '')
				{
					echo "401 ER\n" . $result;
				}
				else
				{
					echo "200 OK\n";
				}
				break;
			case 'password':
				if ($arParams['ACTION'] <> '')
				{
					$query = '
						$obUser = new CUser;
						$arFields = array(
							"PASSWORD" => "' . $arParams['ACTION'] . '",
							"CONFIRM_PASSWORD" => "' . $arParams['ACTION'] . '",
						);
						$obUser->Update(1, $arFields);
						echo $obUser->LAST_ERROR;
					';
				}
				else
				{
					$query = '
						$obUser = new CUser;
						$rsUser = $obUser->GetByID(1);
						$arUser = $rsUser->Fetch();
						echo $arUser["PASSWORD"];
					';
				}
				$result = CControllerMember::RunCommandWithLog($ID, $query);
				if ($result === false)
				{
					if ($e = $APPLICATION->GetException())
					{
						echo "401 ER\n",GetMessage('CC_BCSC_PASSWORD_ERROR' , ['#ID#' => $ID,'#MESSAGE#' => $e->GetString()]);
					}
					else
					{
						echo "500 ER\n";
					}
				}
				elseif ($arParams['ACTION'] == '' && $result <> '')
				{
					echo "210 OK\n" . $result;
				}
				elseif ($result <> '')
				{
					echo "401 ER\n" . $result;
				}
				else
				{
					echo "200 OK\n";
				}
				break;
			case 'list':
				if (count($arParams['ORIG_SITE_URL']))
				{
					echo "210 OK\n";
				}
				else
				{
					echo "200 OK\n";
				}
				foreach ($arParams['ORIG_SITE_URL'] as $key => $orig_site_url)
				{
					$arRes = [$orig_site_url];
					$arMember = false;

					if (array_key_exists($key, $arParams['SITE_URL']))
					{
						$rsMember = CControllerMember::GetList([$_REQUEST['by'] => $_REQUEST['order']], ['=URL' => $arParams['SITE_URL'][$key]]);
						if ($arMember = $rsMember->Fetch())
						{
							$arRes[] = 'FOUND';
						}
						else
						{
							$arRes[] = 'NOTFOUND';
						}
					}
					else
					{
						$arRes[] = 'NOTFOUND';
					}

					if ($arMember && ($arMember['DISCONNECTED'] == 'I'))
					{
						$arRes[] = 'R';
					}
					elseif ($arMember && ($arMember['ACTIVE'] == 'Y'))
					{
						$arRes[] = 'Y';
					}
					else
					{
						$arRes[] = 'N';
					}

					echo implode($arParams['SEPARATOR'], $arRes),"\n";
				}
				break;
			case 'listall':
				$arFilter = [];
				if ($arParams['ACTION'] == 'Y')
				{
					$arFilter['ACTIVE'] = 'Y';
					$arFilter['!DISCONNECTED'] = 'I';
				}
				elseif ($arParams['ACTION'] == 'N')
				{
					$arFilter['ACTIVE'] = 'N';
					$arFilter['!DISCONNECTED'] = 'I';
				}
				elseif ($arParams['ACTION'] == 'R')
				{
					$arFilter['DISCONNECTED'] = 'I';
				}
				$rsMember = CControllerMember::GetList([$_REQUEST['by'] => $_REQUEST['order']], $arFilter);
				if ($arMember = $rsMember->Fetch())
				{
					echo "210 OK\n";
					do {
						if (strncmp($arMember['URL'], 'http://', 7) === 0)
						{
							$arMember['URL'] = mb_substr($arMember['URL'], 7);
						}
						echo
							$arMember['URL']
							,$arParams['SEPARATOR'],'FOUND'
							,$arParams['SEPARATOR'],($arMember['DISCONNECTED'] == 'I' ? 'R' : $arMember['ACTIVE'])
							,"\n";
					} while ($arMember = $rsMember->Fetch());
				}
				else
				{
					echo "200 OK\n";
				}
				break;

			case 'getlist':
				if (isset($_REQUEST['date_format']))
				{
					$arOptions = ['date_format' => $_REQUEST['date_format']];
				}
				else
				{
					$arOptions = [];
				}

				$rsMember = CControllerMember::GetList(
					$_REQUEST['order'],
					$_REQUEST['filter'],
					$_REQUEST['select'],
					$arOptions,
					$_REQUEST['limit'] > 0 ? ['nTopCount' => $_REQUEST['limit']] : false
				);
				if (isset($_REQUEST['json']))
				{
					$arMember = $rsMember->Fetch();
					if ($arMember)
					{
						$comma = '';
						echo '{';
						echo '"status": 210, ';
						echo '"date_format": "' . CUtil::JSEscape($_REQUEST['date_format']) . '", ';
						echo '"sitelist":[';
						do {
							echo $comma;
							//echo CUtil::PhpToJsObject($arMember, false, false, true);
							echo Bitrix\Main\Web\Json::encode($arMember);
							$comma = ',';
						} while ($arMember = $rsMember->Fetch());
						echo ']}';
					}
					else
					{
						echo '{"status": 200}';
					}
				}
				else
				{
					$arMember = $rsMember->Fetch();
					if ($arMember)
					{
						echo "210 OK\n";
						echo '<sitelist date_format="' . htmlspecialcharsbx($_REQUEST['date_format']) . "\">\n";
						do {
							echo "\t<site>\n";
							foreach ($arMember as $key => $value)
							{
								echo "\t\t<", $key, '>', htmlspecialcharsbx($value), '</', $key, ">\n";
							}
							echo "\t</site>\n";
						} while ($arMember = $rsMember->Fetch());
						echo '</sitelist>';
					}
					else
					{
						echo "200 OK\n";
					}
				}
				break;
			default:
				echo "400 ER\n";
				break;
		}
	}
	else
	{
		echo "404 ER\n";
	}
}

$contents = ob_get_contents();
ob_end_clean();

if (!$bDesignMode)
{
	if (isset($_REQUEST['json']))
	{
		header('Content-Type: application/json');
		echo $contents;
	}
	else
	{
		header('Content-Type: text/html; charset=utf-8');
		echo $contents;
	}
	die();
}
else
{
	?>
	<h4><?php echo GetMessage('CC_BCSC_TITLE')?></h4>
	<?php
}

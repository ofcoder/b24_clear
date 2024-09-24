<? \Bitrix\Main\Loader::includeModule('rest');
\Bitrix\Main\Loader::includeModule('crm');

use \kb\Model\DepartmentUpdateTable,
    \kb\Model\TelegramChatTable,
    \Bitrix\Im\Model\MessageParamTable,
    \kb\Model\ShopsTable,
    \Bitrix\Crm\Service\Container;

class RestApi extends \IRestService
{
    public static function OnRestServiceBuildDescriptionHandler()
    {
        return [
            'kb' => [
                'kb.deactivate' => [
                    'callback' => [__CLASS__, 'deactivateUser'],
                    'options' => [],
                ],
                'kb.crupd' => [
                    'callback' => [__CLASS__, 'crupd'],
                    'options' => [],
                ],
                'kb.addtaskbyxml' => [
                    'callback' => [__CLASS__, 'addTaskByXmlId'],
                    'options' => [],
                ],
                'kb.oschanges' => [
                    'callback' => [__CLASS__, 'departmentChanges'],
                    'options' => [],
                ],
                'kb.oschangesclear' => [
                    'callback' => [__CLASS__, 'departmentChangesDelete'],
                    'options' => [],
                ],
                'kb.startbizproc' => [
                    'callback' => [__CLASS__, 'startBizProc'],
                    'options' => [],
                ],
                'kb.startbizproc.crm' => [
                    'callback' => [__CLASS__, 'startBizProcCrm'],
                    'options' => [],
                ],
                'kb.telegram.message.add' => [
                    'callback' => [__CLASS__, 'telegramMessageAdd'],
                    'options' => [],
                ],
                'kb.telegram.openline.add' => [
                    'callback' => [__CLASS__, 'openLineAdd'],
                    'options' => [],
                ],
                'kb.asterisk.task.add' => [
                    'callback' => [__CLASS__, 'addTaskByAsterisk'],
                    'options' => [],
                ],
            ],
        ];
    }

    public static function departmentChanges($query, $n, \CRestServer $server): array
    {
        $lastId = null;
        $departments = $users = [];
        $rs = DepartmentUpdateTable::getList();
        while ($ar = $rs->fetch()) {
            $lastId = $ar['ID'];
            if (!$ar['UF_USER_ID']) {
                unset($ar['UF_USER_ID']);
                $departments[] = $ar;
            } else {
                unset($ar['UF_PARENT_ID']);
                unset($ar['UF_NAME']);
                $users[] = $ar;
            }
        }

        return [
            'departments' => $departments,
            'users' => $users,
            'last' => $lastId,
        ];
    }

    public static function departmentChangesDelete($query, $n, \CRestServer $server): array
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['ID']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }
        $departments = [];
        $rs = DepartmentUpdateTable::getList([
            'filter' => ['<=ID' => $query['ID']],
        ]);

        while ($ar = $rs->fetch()) {
            $departments[] = $ar;
        }

        foreach ($departments as $department) {
            DepartmentUpdateTable::delete($department['ID']);
        }

        return [
            'status' => 'OK',
        ];
    }

    public static function deactivateUser($query, $n, \CRestServer $server): array
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['LOGIN']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }
        $userId = RestApiHelpers::getUserByField('=LOGIN', $query['LOGIN']);
        $description = RestApiHelpers::deactivateById($userId);

        return [
            'status' => 'OK',
            'description' => $description,
        ];
    }

    public static function crupd($query, $n, \CRestServer $server): array
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['LOGIN', 'EMAIL']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $userId = RestApiHelpers::getUserByField('=LOGIN', $query['LOGIN']);

        $user = new CUser;
        $fields = $query;
        $id = $userId['ID'];
        if ($userId) {
            if ($userId['ID'] == $query['ID']) {
                unset($query['ID']);
                $user->Update($userId['ID'], $fields);

                return [
                    (int)$id,
                ];
            } else {
                RestApiHelpers::deactivateById($userId);
                unset($query['ID']);
                $text = \Bitrix\Rest\Api\User::userAdd($query);
                return $text['error'] ? [
                    $text['error_description'],
                ] : [
                    $text,
                ];
            }
        } else {
            unset($query['ID']);
            $text = \Bitrix\Rest\Api\User::userAdd($query);
            return $text['error'] ? [
                $text['error_description'],
            ] : [
                $text,
            ];
        }
    }

    public static function addTaskByXmlId($query, $n, \CRestServer $server): false|array
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query['fields'], ['CREATED_BY', 'RESPONSIBLE_ID']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $userId = RestApiHelpers::getUserByField('=XML_ID', $query['fields']['CREATED_BY'], true)['ID'];
        $responsibleId = RestApiHelpers::getUserByField('=XML_ID', $query['fields']['RESPONSIBLE_ID'], true)['ID'];
        unset($query['fields']['XML_ID']);

        $taskId = 0;
        if ($userId != 1 && $responsibleId != 1 && !empty($responsibleId) && !empty($userId)) {
            $query['fields']['CREATED_BY'] = $userId;
            $query['fields']['RESPONSIBLE_ID'] = $responsibleId;
            $query['fields']['CHANGED_BY'] = $userId;

            if (!CModule::IncludeModule("tasks")) {
                return false;
            }
            $result = \CTaskItem::add($query['fields'], $userId);
            $taskId = $result->getData(false)['ID'];
        } else {
            $result['error_description'] = (empty($responsibleId) || $responsibleId == 1 ? 'responsible' : 'created_by') . ' user not found';
        }

        return [
            'status' => !$result['error_description'] ? 'OK' : 'error',
            'description' => $result['error_description'] ?? 'task was created id = ' . $taskId,
        ];
    }

    public static function startBizProc($query, $n, \CRestServer $server): array
    {
        $finish = [];

        $errors = RestApiHelpers::checkEmptyOrExist($query, ['REGISTER_NAME', 'USER_ID', 'NUMBER', 'SUM', 'DATE', 'DOCUMENT']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $count_document = count($query['DOCUMENT']);

        $razreshenniye_simvoli = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random_number = substr(str_shuffle($razreshenniye_simvoli), 0, 15);
        for ($i = 0; $i < $count_document; $i++) {
            $params = [
                'id' => 55242, // id папки
                'data' => ['NAME' => $query['DOCUMENT'][$i]['NAME']],
                'generateUniqueName' => true,
                'fileContent' => str_replace("\n", '', $query['DOCUMENT'][$i]['SRC']),
            ];
            $result = CRest::call('disk.folder.uploadfile', $params);

            $file_id = $result['result']['FILE_ID'];
            $element_id = $result['result']['ID'];
            $params = [
                "IBLOCK_TYPE_ID" => 'bitrix_processes',
                "IBLOCK_ID" => BIZPROC_INFOBLOCK_ID,
                "ELEMENT_CODE" => $random_number,
                "FIELDS" => [
                    "NAME" => $query['REGISTER_NAME'],
                    "PROPERTY_75" => $query['USER_ID'],
                    "PROPERTY_74" => $query['NUMBER'],
                    "PROPERTY_67" => $query['SUM'],
                    "PROPERTY_68" => $file_id,
                    "PROPERTY_69" => $query['DATE'],
                    "PROPERTY_73" => $query['COMMENT'],
                ],
            ];

            $result = CRest::call('lists.element.' . ($i == 0 ? 'add' : 'update'), $params);
            if ($i == 0) {
                $finish = $result;
            }
            CRest::call('disk.file.delete', ['id' => $element_id]);
        }

        return [
            'status' => 'OK',
            'description' => $finish['result'],
        ];
    }

    public static function startBizProcCrm($query, $n, \CRestServer $server): array
    {
        $finish = [];

        $errors = RestApiHelpers::checkEmptyOrExist($query, ['REGISTER_NAME', 'DOCUMENT']);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $factory = Container::getInstance()->getFactory(\CCrmOwnerType::SmartInvoice);

        $arFile["MODULE_ID"] = "crm";
        $arFile["content"] = base64_decode($query['DOCUMENT'][0]['SRC']);
        $arFile["name"] = $query['DOCUMENT'][0]['NAME'];
        $fileId = CFile::SaveFile($arFile, "crm/invoices");

        $fields = [
            'UF_CRM_SMART_INVOICE_1711360561994' => \CFile::MakeFileArray($fileId)
        ];
        $data = [
            'ASSIGNED_BY_ID' => 1,
            'COMPANY_ID' => 0,
            'TITLE' => $query['REGISTER_NAME']
        ];

        $newItem = $factory->createItem($data);
        $newItem->setFromCompatibleData($fields);
        $item = $newItem->save();

		if($item->getId()) {// заменить на try catch
			
			self::initialBizproc($item->getId());
			
			return [
				'status' => 'OK',
				'description' => $item->getId(),
			];
			
		} else {
			return [
				'status' => 'ERROR',
				'description' => 'Не удалось добавить элемент',
			];
		}

    }
	private static function initialBizproc($id)
	{
		\CCrmBizProcHelper::AutoStartWorkflows(
				\CCrmOwnerType::SmartInvoice,
				$id,
				$isNew ? \CCrmBizProcEventType::Create : \CCrmBizProcEventType::Edit,
				$arErrors,
				isset($_POST['bizproc_parameters']) ? $_POST['bizproc_parameters'] : null
			);

			$starter = new \Bitrix\Crm\Automation\Starter(\CCrmOwnerType::SmartInvoice, $id);
			$starter->setUserIdFromCurrent();

			if($isNew)
			{
				$starter->runOnAdd();
			}
			elseif(is_array($previousFields))
			{
				$starter->runOnUpdate($fields, $previousFields);
			}
	}

    public static function telegramMessageAdd($query, $n, \CRestServer $server): array|bool
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['chat_id', 'username']);
        $errors += RestApiHelpers::checkEmptyOrExist($query, ['message'], false);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $chat_id = htmlspecialchars($query['chat_id']);
        $username = htmlspecialchars($query['username']);
        $message = htmlspecialchars($query['message']);

        $info = TelegramChatTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => ['UF_TELEGRAM_CHAT_ID' => $chat_id, 'UF_TELEGRAM_USERNAME' => $username],
            'select' => ['UF_LAST_MESSAGE', 'UF_TELEGRAM_NAME', 'UF_TELEGRAM_LAST_NAME', 'ID', 'UF_IS_OPENED', 'UF_IS_TEST'],
            'limit' => 1,
        ])->fetch();
        $getLineId = filter_var($info['UF_IS_TEST'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'UF_OPENLINE_MONITA_ID_TEST' : 'UF_OPENLINE_MONITA_ID';

        $files = [];
        if (!empty($query['document_url'])) {
            $files[] = ['url' => $query['document_url']];
        }
        if (!empty($query['photo_url'])) {
            $files[] = ['url' => $query['photo_url']];
        }
        if (!empty($query['reply_to_message'])) {
            $orm = MessageParamTable::getList([
                'order' => ['ID' => 'DESC'],
                'select' => ['NAME' => 'USER.NAME', 'LAST_NAME' => 'USER.LAST_NAME', 'DATE_CREATE' => 'MESSAGE.DATE_CREATE'],
                'filter' => ['PARAM_VALUE' => $query['reply_to_message']['message_id'], 'PARAM_NAME' => 'CONNECTOR_MID'],
                'runtime' => [
                    new \Bitrix\Main\Entity\ReferenceField(
                        'MESSAGE',
                        '\Bitrix\Im\Model\MessageTable',
                        ['=this.MESSAGE_ID' => 'ref.ID',]
                    ),
                    new \Bitrix\Main\Entity\ReferenceField(
                        'USER',
                        '\Bitrix\Main\UserTable',
                        ['=this.MESSAGE.AUTHOR_ID' => 'ref.ID',]
                    ),
                ],
                'limit' => 1,
            ])->fetch();

            if ($orm) {
                $replyMessage =
                    "------------------------------------------------------
{$orm["NAME"]} {$orm["LAST_NAME"]}[{$orm["DATE_CREATE"]->add("-2 hours")->toString()} мск]
{$query["reply_to_message"]["message"]}
------------------------------------------------------";

                $message = $replyMessage . $message;
            }
        }

        if ($info['UF_IS_OPENED']) {
            $arMessage = [
                'user' => [
                    'id' => $chat_id,
                    'name' => $info['UF_TELEGRAM_NAME'],
                    'last_name' => $info['UF_TELEGRAM_LAST_NAME'],
                    'url' => 'https://t.me/' . $username,
                ],
                'message' => [
                    'id' => $query['message_id'],
                    'disable_crm' => 'Y',
                    'date' => time(),
                    'text' => $message,
                    'files' => $files,
                ],
                'chat' => [
                    'id' => $chat_id,
                    'url' => htmlspecialchars($_SERVER['HTTP_REFERER']),
                ],
            ];

            $result = CRest::call(
                'imconnector.send.messages', [
                    'CONNECTOR' => TELEGRAM_CONNECTOR_ID,
                    'LINE' => COption::GetOptionString("askaron.settings", $getLineId),
                    'MESSAGES' => [$arMessage],
                ]
            )['result'];

            if ($result['SUCCESS']) {
                TelegramChatTable::update($info['ID'], [
                    'UF_LAST_MESSAGE' => $info['UF_LAST_MESSAGE'] + 1,
                ]);
            }
        }

        return (bool)$info['UF_IS_OPENED'];
    }

    public static function openLineAdd($query, $n, \CRestServer $server)
    {
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['chat_id', 'username']);
        $errors += RestApiHelpers::checkEmptyOrExist($query, ['is_test', 'first_name', 'last_name'], false);
        if ($errors) {
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $first_name = htmlspecialchars($query['first_name'] ?: '');
        $last_name = htmlspecialchars($query['last_name'] ?: '');
        $chat_id = htmlspecialchars($query['chat_id']);
        $username = htmlspecialchars($query['username']);
        $topic = htmlspecialchars($query['topic']);

        $arMessage = [
            'user' => [
                'id' => $chat_id,
                'name' => $first_name,
                'last_name' => $last_name,
                'url' => 'https://t.me/' . $username,
            ],
            'message' => [
                'id' => $query['message_id'],
                'date' => time(),
                'text' => 'Новое обращение от ' . $username . ' ' . $last_name . ' ' . $first_name . '. Тема обращения - ' . $topic,
            ],
            'chat' => [
                'id' => $chat_id,
                'url' => htmlspecialchars($_SERVER['HTTP_REFERER']),
            ],
        ];

        $getLineId = filter_var($query['is_test'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ? 'UF_OPENLINE_MONITA_ID_TEST' : 'UF_OPENLINE_MONITA_ID';

        $result['error'] = 'error_save';
        $arMessage['message']['id'] = 0;
        $result = CRest::call(
            'imconnector.send.messages', [
                'CONNECTOR' => TELEGRAM_CONNECTOR_ID,
                'LINE' => COption::GetOptionString("askaron.settings", $getLineId),
                'MESSAGES' => [$arMessage],
            ]
        )['result'];

        if ($result['SUCCESS']) {
            TelegramChatTable::add([
                'UF_LAST_MESSAGE' => 0,
                'UF_TELEGRAM_USERNAME' => $username,
                'UF_TELEGRAM_NAME' => $first_name,
                'UF_TELEGRAM_LAST_NAME' => $last_name,
                'UF_TELEGRAM_CHAT_ID' => $chat_id,
                'UF_IS_OPENED' => 1,
                'UF_USER_ID' => reset($result['DATA']['RESULT'])['user'],
                'UF_CHAT_ID' => reset($result['DATA']['RESULT'])['chat']['id'],
                'UF_IS_TEST' => $query['is_test'],
            ]);
        }
        return $result;
    }

    public static function addTaskByAsterisk($query, $n, \CRestServer $server)
    {
        \Bitrix\Main\Diag\Debug::dumpToFile($query, 'query', '1_logs.txt');
        \Bitrix\Main\Diag\Debug::dumpToFile($_SERVER['REQUEST_URI'], 'REQUEST_URI', '1_logs.txt');
        $errors = RestApiHelpers::checkEmptyOrExist($query, ['SHOP_PHONE', 'DTO_PHONE']);

        $shopNumber = 0;
        $selectUserIdBoss = '';
        $dtoUser = \Bitrix\Main\UserTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => ['PERSONAL_PHONE' => $query['DTO_PHONE']],
            'select' => ['ID', 'UF_DTO_FIELD'],
            'limit' => 1,
        ])->fetch();
        if (!$dtoUser) {
            $errors[] = 'Dto user don`t found';
        } else {
            switch ($dtoUser['UF_DTO_FIELD']) {
                case 'st':
                    $selectUserIdBoss = 'UF_USER_ID_ENGINEER';
                    break;
                case 'engineer':
                    $selectUserIdBoss = 'UF_USER_ID_DEPUTY_HEAD_ENGINEER';
                    break;
                case 'head_engineer':
                case 'deputy_head_engineer':
                    $selectUserIdBoss = 'UF_USER_ID_HEAD_ENGINEER';
                    break;
            }
        }

        $shop = ShopsTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => ['UF_PHONE' => $query['SHOP_PHONE']],
            'select' => ['ID', $selectUserIdBoss, 'UF_NUMBER'],
            'limit' => 1,
        ])->fetch();
        if (!$shop) {
            $errors[] = 'Shop don`t found';
        } else {
            $shopNumber = $shop['UF_NUMBER'];
            unset($shop['ID']);
            unset($shop['UF_NUMBER']);
        }

        if ($errors) {
            $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            \Bitrix\Main\Mail\Event::sendImmediate([
                "EVENT_NAME" => "SEND_ERRORS",
                "LID" => "s1",
                "C_FIELDS" => [
                    "MESSAGE" => 'Список ошибок: ' . implode(", ", $errors) . ',
Запрос: ' . $url . ',
IP address: ' . $_SERVER['REMOTE_ADDR'],
                ],
            ]);
            CHTTP::SetStatus("404 Not Found");
            return [
                'error' => 'ERROR_CODE',
                'error_description' => $errors,
            ];
        }

        $fields = [
            'TASK_CONTROL' => "Y",
            'ALLOW_TIME_TRACKING' => "Y",
            'GROUP_ID' => DTO_GROUP_ID,
            'TITLE' => 'Магазин №' . $shopNumber,
            'CREATED_BY' => reset($shop),
            'RESPONSIBLE_ID' => $dtoUser['ID'],
            'CHANGED_BY' => $dtoUser['ID'],
        ];

        if (!CModule::IncludeModule("tasks")) {
            return false;
        }
        $result = \CTaskItem::add($fields, 1);
        $taskId = $result->getData()['ID'];

        return [
            'status' => 'OK',
            'description' => $taskId,
        ];
    }
}
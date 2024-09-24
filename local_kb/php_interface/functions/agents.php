<?php
function UpdateExtranetUsersFromWMS()
{
    $conn_id = ftp_connect(FTP_WMS_SERVER);
    $login_result = ftp_login($conn_id, FTP_WMS_USER_NAME, FTP_WMS_USER_PASS);

    $local_file = dirname(__DIR__, 3) . '/upload/learning/wms_' . date_format(date_create(), 'd-m-Y-H:i:s') . '.csv';
    $handle = fopen($local_file, 'w+');

    $mode = ftp_pasv($conn_id, true);
    $dirs = ftpRecursiveFileListing($conn_id, '/');
    foreach ($dirs as $dir) {
        try {
            ftp_fget($conn_id, $handle, $dir, FTP_ASCII);
        } catch (Exception $e) {
            \Bitrix\Main\Diag\Debug::dumpToFile("При скачивании " . $dir . " произошла проблема\n" . $e->getMessage(), $dir, '/logs/wms_errors.txt');
        }
    }

    rewind($handle);
    $workGroups = $workCities = $existCities = $addFields = $existUsers = $updateFields = [];

    $rsUser = \Bitrix\Main\UserGroupTable::getList([
        'order' => ['USER.ID' => 'DESC'],
        'filter' => [
            'USER.ACTIVE' => 'Y',
            'GROUP_ID' => 29,
            '!USER.XML_ID' => false,
            '!USER.WORK_CITY' => false,
        ],
        'select' => [
            'ID' => 'USER.ID',
            'LAST_NAME' => 'USER.LAST_NAME',
            'LOGIN' => 'USER.LOGIN',
            'NAME' => 'USER.NAME',
            'EMAIL' => 'USER.EMAIL',
            'SECOND_NAME' => 'USER.SECOND_NAME',
            'WORK_CITY' => 'USER.WORK_CITY',
            'WORK_POSITION' => 'USER.WORK_POSITION',
            'XML_ID' => 'USER.XML_ID',
            'UF_EMPLOYMENT_DATE' => 'USER.UF_EMPLOYMENT_DATE',
        ],
    ]);
    while ($arUser = $rsUser->fetch()) {
        $existUsers[$arUser['LOGIN']] = $arUser;
    }

    $rsCities = kb\Model\WorkCitiesTable::getList();
    while ($arCity = $rsCities->fetch()) {
        $existCities[] = $arCity['UF_CITY'];
    }

    while (($data = fgetcsv($handle, 1000, "|")) !== false) {
        if (!is_numeric(trim($data[4], ' \n\r\t\v\x00')) || str_contains(trim($data[4]), ".")) {
            continue;
        }

        $workGroupTrimmed = mb_strtolower(trim(preg_replace('/[0-9*]+/', '', $data[3]), ' \n\r\t\v\x00.'));
        $workCroupCamel = mb_strtoupper(mb_substr($workGroupTrimmed, 0, 1)) . mb_substr($workGroupTrimmed, 1);
        if (!in_array($workCroupCamel, $workGroups) && !is_numeric($workCroupCamel) && !empty($workCroupCamel)) {
            $workGroups[] = $workCroupCamel;
        }

        $workCityTrimmed = trim($data[0], '﻿');
        if (!in_array($workCityTrimmed, $workCities)) {
            $workCities[] = $workCityTrimmed;
        }

        $partsOfName = explode(' ', trim(preg_replace('/[0-9*]+/', '', $data[1]), ' \n\r\t\v\x00.'));
        $uniqueKey = str_replace(' ', '_', translite($workCityTrimmed) . trim($data[4]) . '@krasnoe-beloe.ru');
        if (!empty($existUsers[$uniqueKey]) && CModule::IncludeModule("socialnetwork")) { // Проверяем на существующих пользователей
            $badSonet = AgentsHelpers::getSonetGroupByName($workCroupCamel);
            if ($existUsers[$uniqueKey]['LAST_NAME'] != $partsOfName[0]
                || $existUsers[$uniqueKey]['NAME'] != $partsOfName[1]
                || $existUsers[$uniqueKey]['SECOND_NAME'] != $partsOfName[2]
                || $existUsers[$uniqueKey]['UF_EMPLOYMENT_DATE'] != $data[2]
                || $existUsers[$uniqueKey]['WORK_POSITION'] != $workCroupCamel
                || $badSonet
            ) {
                $fieldsToUpdate = [
                    'ID' => $existUsers[$uniqueKey]['ID'],
                    'LAST_NAME' => $partsOfName[0],
                    'NAME' => $partsOfName[1],
                    'SECOND_NAME' => $partsOfName[2] ?? '',
                    'WORK_POSITION' => $workCroupCamel,
                    'UF_EMPLOYMENT_DATE' => $data[2] ?? '',
                ];

                if ($existUsers[$uniqueKey]['WORK_POSITION'] != $workCroupCamel || $badSonet) {
                    $fieldsToUpdate['SONET_GROUP_ID'] = $workCroupCamel;
                }

                $updateFields[] = $fieldsToUpdate;
            }
        } else {
            if (!empty($workCroupCamel)) {
                $addFields[] = [
                    'LAST_NAME' => $partsOfName[0],
                    'NAME' => $partsOfName[1] ?? '',
                    'SECOND_NAME' => $partsOfName[2] ?? '',
                    'WORK_CITY' => $workCityTrimmed,
                    'XML_ID' => trim($data[4]),
                    'EMAIL' => $uniqueKey,
                    'EXTRANET' => 'Y',
                    'SONET_GROUP_ID' => $workCroupCamel,
                    'WORK_POSITION' => $workCroupCamel,
                    'UF_EMPLOYMENT_DATE' => $data[2] ?? '',
                ];
            }
        }
    }
    fclose($handle);
    ftp_close($conn_id);
    $workGroups = AgentsHelpers::setSonetGroups($workGroups); // Переопределение SONET_GROUP_ID

    global $USER;
    $USER->Authorize(1, false, false);

    foreach ($addFields as $addField) {
        $addField['SONET_GROUP_ID'] = [array_search($addField['SONET_GROUP_ID'], $workGroups)];
        try {
            \Bitrix\Rest\Api\User::userAdd($addField);
        } catch (Exception $e) {
            \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении пользователя" . $e->getMessage() . "\n", 'userAdd', '/wms_errors.txt');
        }
    }

    foreach ($updateFields as $updateField) {
        try {
            if (!empty($updateField['SONET_GROUP_ID']) && CModule::IncludeModule("socialnetwork")) {
                $sonetId = CSocNetUserToGroup::GetList([], ["USER_ID" => $updateField['ID']])->fetch()['ID'];
                if ($sonetId) {
                    CSocNetUserToGroup::Delete($sonetId);
                }
                CSocNetUserToGroup::Add([
                    "USER_ID" => $updateField['ID'],
                    "GROUP_ID" => array_search($updateField['SONET_GROUP_ID'], $workGroups),
                    "ROLE" => SONET_ROLES_USER,
                    "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
                    "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
                    "INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
                    "INITIATED_BY_USER_ID" => 1,
                    "MESSAGE" => false,
                ]);
                unset($updateField['SONET_GROUP_ID']);
            }

            \Bitrix\Rest\Api\User::userUpdate($updateField);
        } catch (Exception $e) {
            \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при обновлении пользователя " . $e->getMessage() . "\n", 'userUpdate', '/wms_errors.txt');
        }
    }

    unset($USER);

    foreach ($workCities as $workCity) {
        if (!in_array($workCity, $existCities)) {
            try {
                kb\Model\WorkCitiesTable::add(['UF_CITY' => $workCity]);
            } catch (Exception $e) {
                \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении города" . $e->getMessage() . "\n", $workCity, '/wms_errors.txt');
            }
        }
    }

    return "UpdateExtranetUsersFromWMS();";
}

function UpdateAddressProgramUsersFrom1c()
{
    $data = getResponce(ODINASS_SHOPS_SERVER . 'employees', '', ODINASS_SHOPS_INFO);
    $users = json_decode($data, true);

    $rsShops = kb\Model\ShopsTable::getList([ //К какой роли относится
        'select' => ['ID', 'UF_ADMIN_ID', 'UF_ZRU_ID', 'UF_SUPERVISOR', 'UF_RU_ID', 'UF_NUMBER'],
    ]);
    $ru = $zru = $supervisor = $admin = [];
    while ($arShop = $rsShops->fetch()) {
        if (!empty($arShop['UF_ADMIN_ID'])) {
            $admin[] = $arShop['UF_ADMIN_ID'];
        }
        if (!empty($arShop['UF_SUPERVISOR'])) {
            $supervisor[] = $arShop['UF_SUPERVISOR'];
        }
        if (!empty($arShop['UF_ZRU_ID'])) {
            $zru[] = $arShop['UF_ZRU_ID'];
        }
        if (!empty($arShop['UF_RU_ID'])) {
            $ru[] = $arShop['UF_RU_ID'];
        }
    }

    foreach ($users as $user) {
        $arUser = \Bitrix\Main\UserTable::getList([
            'select' => ['ID', 'XML_ID'],
            'filter' => ['XML_ID' => $user['Номер']],
            'cache' => [
                'ttl' => 3600,
            ],
        ])->fetch();

        $newUser = new CUser;
        if (empty($arUser) && $user['Активен']) {
            $role = '';
            if (in_array($user['Номер'], $admin)) {
                $role = USER_GROUP_ID_SHOPS_ADMIN;
            }
            if (in_array($user['Номер'], $supervisor)) {
                $role = USER_GROUP_ID_SUPERVISOR;
            }
            if (in_array($user['Номер'], $zru)) {
                $role = USER_GROUP_ID_ZRU;
            }
            if (in_array($user['Номер'], $ru)) {
                $role = USER_GROUP_ID_RU;
            }

            $partsOfName = explode(' ', trim(preg_replace('/[0-9*]+/', '', $user['ФИО']), ' \n\r\t\v\x00.'));
            $password = rand(10000000, 99999999);
            $arFields = [
                "NAME" => $partsOfName[1],
                "LAST_NAME" => $partsOfName[0],
                "SECOND_NAME" => $partsOfName[2],
                "EMAIL" => $user['Email'],
                "LOGIN" => $user['Email'],
                "ACTIVE" => "Y",
                "GROUP_ID" => [USER_GROUP_ID_ADDRESS_PROGRAM, $role],
                "PASSWORD" => $password,
                "CONFIRM_PASSWORD" => $password,
                "PERSONAL_PHONE" => $user['Телефон'],
                "XML_ID" => $user['Номер'],
            ];
            try {
                $ID = $newUser->Add($arFields);
            } catch (Exception $e) {
                \Bitrix\Main\Diag\Debug::dumpToFile("Ошибка при добавлении пользователя" . $e->getMessage() . "\n", 'userAdd', '/address_errors.txt');
            }
        } else if (!$user['Активен']){
            $newUser->Delete($arUser['ID']);
        }
    }

    return "UpdateAddressProgramUsersFrom1c();";
}

function UpdateAddressProgramShopsFrom1c()
{
    $data = getResponce(ODINASS_SHOPS_SERVER . 'shops', '', ODINASS_SHOPS_INFO);
    $shops = json_decode($data, true);

    $rsShops = kb\Model\ShopsTable::getList([
        'select' => ['ID', 'UF_ADMIN_ID', 'UF_ZRU_ID', 'UF_SUPERVISOR', 'UF_RU_ID', 'UF_NUMBER', 'UF_LONGITUDE', 'UF_LATITUDE'],
    ]);
    $existShops = [];
    while ($arShop = $rsShops->fetch()) {
        $existShops[$arShop['UF_NUMBER']] = $arShop;
    }

    foreach ($shops as $shop) {
        if (!empty($existShops[$shop['Номер']]['ID']) && $shop['Статус'] == 'Закрыт') {
            kb\Model\ShopsTable::delete($existShops[$shop['Номер']]['ID']);
            continue;
        }

        if (empty($shop['Номер']) || $shop['Статус'] == 'Закрыт')
            continue;

        if (empty($existShops[$shop['Номер']])) {
            kb\Model\ShopsTable::add([
                'UF_ADDRESS' => $shop['Адрес'],
                'UF_REGION' => $shop['Регион'],
                'UF_NUMBER' => $shop['Номер'],
                'UF_CITY' => $shop['Город'],
                'UF_TERRITORY' => $shop['Территория'],
                'UF_PEOPLE' => $shop['ЧисленностьНаселения'],
                'UF_PHONE' => $shop['Телефон'],
                'UF_ENTITY' => $shop['ЮридическоеЛицо'],
                'UF_EMAIL' => $shop['Email'],
                'UF_ADMIN_ID' => $shop['АдминистраторМагазина'],
                'UF_ZRU_ID' => $shop['ЗРУ'],
                'UF_SUPERVISOR' => $shop['Супервайзер'],
                'UF_RU_ID' => $shop['РУ'],
                'UF_STATUS' => '1',
                'UF_COMMENT' => '',
                'UF_ADDRESS_SUM' => '0',
                'UF_LONGITUDE' => $shop['Долгота'],
                'UF_LATITUDE' => $shop['Широта'],
            ]);
        } else if (
            $existShops[$shop['Номер']]['UF_RU_ID'] != $shop['РУ']
            || $existShops[$shop['Номер']]['UF_ZRU_ID'] != $shop['ЗРУ']
            || $existShops[$shop['Номер']]['UF_SUPERVISOR'] != $shop['Супервайзер']
            || $existShops[$shop['Номер']]['UF_ADMIN_ID'] != $shop['АдминистраторМагазина']
            || $existShops[$shop['Номер']]['UF_PHONE'] != $shop['Телефон']
            || $existShops[$shop['Номер']]['UF_LONGITUDE'] != $shop['Долгота']
            || $existShops[$shop['Номер']]['UF_LATITUDE'] != $shop['Широта']
        ) {
            $updateFields = [
                'UF_ADMIN_ID' => $shop['АдминистраторМагазина'],
                'UF_ZRU_ID' => $shop['ЗРУ'],
                'UF_SUPERVISOR' => $shop['Супервайзер'],
                'UF_RU_ID' => $shop['РУ'],
                'UF_PHONE' => $shop['Телефон'],
                'UF_LONGITUDE' => $shop['Долгота'],
                'UF_LATITUDE' => $shop['Широта'],
            ];

            kb\Model\ShopsTable::update($existShops[$shop['Номер']]['ID'], $updateFields);
        }
    }

    return "UpdateAddressProgramShopsFrom1c();";
}

function UpdateScheduleDto()
{
    if (!CModule::IncludeModule("socialnetwork")) {
        return;
    }

    $fileName = 'Shops.xml';
    $filePath = $_SERVER["DOCUMENT_ROOT"] . '/upload/1c/dto/' . $fileName;
    $users = $emails = $shops = $shopsId = [];
    $stream = fopen($filePath, 'r');
    if (($data = fread($stream, filesize($filePath)))) {
        $xml = simplexml_load_string($data);
        $shops = json_decode(json_encode($xml), true)['shop'];
        foreach ($shops as $shop) {
            $dto = $shop['dto'];
            if (!empty($dto['st']['email']) && !empty($dto['st']['phone'])) {
                $dto['st']['email'] = mb_strtolower($dto['st']['email']);
                if (!in_array($dto['st']['email'], $emails))
                    $emails[] = $dto['st']['email'];
                $users[mb_strtolower($dto['st']['email'])] = [
                    'position' => 'st',
                    'phone' => $dto['st']['phone']
                ];
            }
            if (!empty($dto['engineer']['email']) && !empty($dto['engineer']['phone'])) {
                $dto['engineer']['email'] = mb_strtolower($dto['engineer']['email']);
                if (!in_array($dto['engineer']['email'], $emails))
                    $emails[] = $dto['engineer']['email'];
                $users[mb_strtolower($dto['engineer']['email'])] = [
                    'position' => 'engineer',
                    'phone' => $dto['engineer']['phone']
                ];
            }
            if (!empty($dto['deputy_head_engineer']['email']) && !empty($dto['deputy_head_engineer']['phone'])) {
                $dto['deputy_head_engineer']['email'] = mb_strtolower($dto['deputy_head_engineer']['email']);
                if (!in_array($dto['deputy_head_engineer']['email'], $emails))
                    $emails[] = $dto['deputy_head_engineer']['email'];
                $users[mb_strtolower($dto['deputy_head_engineer']['email'])] = [
                    'position' => 'deputy_head_engineer',
                    'phone' => $dto['deputy_head_engineer']['phone']
                ];
            }
            if (!empty($dto['head_engineer']['email']) && !empty($dto['head_engineer']['phone'])) {
                $dto['head_engineer']['email'] = mb_strtolower($dto['head_engineer']['email']);
                if (!in_array($dto['head_engineer']['email'], $emails))
                    $emails[] = $dto['head_engineer']['email'];
                $users[mb_strtolower($dto['head_engineer']['email'])] = [
                    'position' => 'head_engineer',
                    'phone' => $dto['head_engineer']['phone']
                ];
            }
        }
    }
    fclose($stream);
    $arUsers = \Bitrix\Main\UserTable::getList([
        'filter' => ['EMAIL' => $emails],
        'select' => ['ID', 'EMAIL', 'UF_DTO_FIELD'],
    ]);
    while ($bitrixUser = $arUsers->fetch()) {
        $users[mb_strtolower($bitrixUser['EMAIL'])]['id'] = $bitrixUser['ID'];
        if (!$bitrixUser['UF_DTO_FIELD']) {
            $user = new CUser;
            $user->Update($bitrixUser['ID'], [
                "PERSONAL_PHONE" => $users[mb_strtolower($bitrixUser['EMAIL'])]['phone'],
                "UF_DTO_FIELD" => $users[mb_strtolower($bitrixUser['EMAIL'])]['position'],
            ]);

            CSocNetUserToGroup::Add([
                "USER_ID" => $bitrixUser['ID'],
                "GROUP_ID" => DTO_GROUP_ID,
                "ROLE" => SONET_ROLES_USER,
                "DATE_CREATE" => new \Bitrix\Main\Type\DateTime(),
                "DATE_UPDATE" => new \Bitrix\Main\Type\DateTime(),
                "INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
                "INITIATED_BY_USER_ID" => 1,
                "MESSAGE" => false,
            ]);
        }
    }

    $ormShops = kb\Model\ShopsTable::getList(['select' => ['ID', 'UF_NUMBER']]);
    while ($bitrixShop = $ormShops->fetch()) {
        $shopsId[$bitrixShop['UF_NUMBER']] = $bitrixShop['ID'];
    }

    if ($shops) {
        foreach ($shops as $shop) {
            $dto = $shop['dto'];

            $fields = [
                'UF_USER_ID_ST' => $users[$dto['st']['email']]['id'] ?? '0',
                'UF_USER_ID_ENGINEER' => $users[$dto['engineer']['email']]['id'] ?? '0',
                'UF_USER_ID_DEPUTY_HEAD_ENGINEER' => $users[$dto['deputy_head_engineer']['email']]['id'] ?? '0',
                'UF_USER_ID_HEAD_ENGINEER' => $users[$dto['head_engineer']['email']]['id'] ?? '0',
            ];

            if (!empty($shopsId[$shop['shop_id']])) {
                kb\Model\ShopsTable::update($shopsId[$shop['shop_id']], $fields);
            }
        }
    }

    return 'UpdateScheduleDto();';
}
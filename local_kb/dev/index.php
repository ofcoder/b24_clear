<?php
$token = '64b34a650067632c005b0f0e00000f81e0e30783fe66f490603967ce2858f3bb3719d0';

$arParams = array(
	'DOCUMENT' => array(
		['SRC'=>'Реестр платежей БРЛ №БРЛ_22.03.2024 - тест 100500.xls', 'NAME' => 'aaaaaaaa'],
		['SRC'=>'eeeeeeeeeeeee', 'NAME' => 'bbbbbbbb'],
	),
	'REGISTER_NAME' => 'Реестр платежей',
	'USER_ID' => 16,
	'NUMBER' => 2121,
	'SUM' => 600,
	'DATE' => '30.05.2024 09:53'
);

define('SITE_PORTAL', 'portal.krasnoe-beloe.ru');
define('SITE_PORTAL1', 'bitrix.rw.org');
$fullURL = 'https://'. SITE_PORTAL1 .'/rest/kb.startbizproc.crm?auth=' . $token . '&' . http_build_query($arParams);
echo $fullURL;

/*
$post = http_build_query([
'AUTH_FORM' => 'Y',
'TYPE' => 'AUTH',
'backurl' =>'/auth/',
'USER_LOGIN' => $login,
'USER_PASSWORD' => $pass,
'USER_REMEMBER' => 'Y'
]);

if(strtolower((substr($url,0,5))=='https')) { // если соединяемся с https
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
}
curl_setopt($ch, CURLOPT_URL, 'https://'.SITE_PORTAL.'/auth/?login=yes');
// cURL будет выводить подробные сообщения о всех производимых действиях
curl_setopt($ch, CURLOPT_VERBOSE, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$post);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (Windows; U; Windows NT 5.0; En; rv:1.8.0.2) Gecko/20070306 Firefox/1.0.0.4");
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//сохранять полученные COOKIE в файл
curl_setopt($ch, CURLOPT_COOKIEJAR, $_SERVER['DOCUMENT_ROOT'].'/restapi/cookie.txt');
$result=curl_exec($ch);
*/

// https://portal.krasnoe-beloe.ru/rest/kb.startbizproc.crm?auth=248b5966006bd1a0005b0f0e000000010000074b54380839cacb9f4632a04adba89ec5&DOCUMENT%5B0%5D%5BSRC%5D=fffffffffffff&DOCUMENT%5B0%5D%5BNAME%5D=aaaaaaaa&DOCUMENT%5B1%5D%5BSRC%5D=eeeeeeeeeeeee&DOCUMENT%5B1%5D%5BNAME%5D=bbbbbbbb&REGISTER_NAME=%D0%A0%D0%B5%D0%B5%D1%81%D1%82%D1%80+%D0%BF%D0%BB%D0%B0%D1%82%D0%B5%D0%B6%D0%B5%D0%B9&USER_ID=16&NUMBER=2121&SUM=600&DATE=30.05.2024+09%3A53
/*
https://bitrix.rw.org/rest/kb.startbizproc.crm?auth=248b5966006bd1a0005b0f0e000000010000074b54380839cacb9f4632a04adba89ec5&DOCUMENT%5B0%5D%5BSRC%5D=fffffffffffff&DOCUMENT%5B0%5D%5BNAME%5D=aaaaaaaa&DOCUMENT%5B1%5D%5BSRC%5D=eeeeeeeeeeeee&DOCUMENT%5B1%5D%5BNAME%5D=bbbbbbbb&REGISTER_NAME=%D0%A0%D0%B5%D0%B5%D1%81%D1%82%D1%80+%D0%BF%D0%BB%D0%B0%D1%82%D0%B5%D0%B6%D0%B5%D0%B9&USER_ID=16&NUMBER=2121&SUM=600&DATE=30.05.2024+09%3A53

*/

///bizproc/processes/37/file/0/373/PROPERTY_68/104251/?ncc=y&download=y

//https://bitrix.rw.org/rest/kb.startbizproc.crm?auth=64b34a650067632c005b0f0e00000f81e0e30783fe66f490603967ce2858f3bb3719d0&DOCUMENT%5B0%5D%5BSRC%5D=%D0%A0%D0%B5%D0%B5%D1%81%D1%82%D1%80+%D0%BF%D0%BB%D0%B0%D1%82%D0%B5%D0%B6%D0%B5%D0%B9+%D0%91%D0%A0%D0%9B+%E2%84%96%D0%91%D0%A0%D0%9B_22.03.2024+-+%D1%82%D0%B5%D1%81%D1%82+100500.xls&DOCUMENT%5B0%5D%5BNAME%5D=aaaaaaaa&DOCUMENT%5B1%5D%5BSRC%5D=eeeeeeeeeeeee&DOCUMENT%5B1%5D%5BNAME%5D=bbbbbbbb&REGISTER_NAME=%D0%A0%D0%B5%D0%B5%D1%81%D1%82%D1%80+%D0%BF%D0%BB%D0%B0%D1%82%D0%B5%D0%B6%D0%B5%D0%B9&USER_ID=16&NUMBER=2121&SUM=600&DATE=30.05.2024+09%3A53
?>
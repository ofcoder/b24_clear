<?
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
/***************************Удаление папок сайта на сервере*******************************************/

 function rrmdir($dir) {

   if (is_dir($dir)) {

     $objects = scandir($dir);

     foreach ($objects as $object) {

       if ($object != "." && $object != "..") {

         if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);

       }

     }

     reset($objects);

     rmdir($dir);

   }

 }
 $dir = __DIR__;// есле файл лежит в корне сайта
 $dirs = scandir($dir, SCANDIR_SORT_DESCENDING);

foreach ($dirs as $item) {
	if(in_array($item, ['.', '..']) )
		continue;
	if(!strpos($item, '.'))
	echo($item) . "<br>";
	rrmdir($item);
}

/**************************Удаление таблиц в базе*********************************/
$DB_HOST='localhost';
$DB_USER='vreme_db'; 
$DB_PASS='1q2w3e!Q@W#E';
$DB_NAME='vreme_db';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$link = mysqli_connect( $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME );
if (!$link) {
 	echo "Ошибка: Невозможно установить соединение с MySQL.";
 	echo "Код ошибки errno: ".mysqli_connect_errno( );
 	echo "Текст ошибки error: ".mysqli_connect_error( );
} else {	
	
	/********запрос для получения запросов для удаления таблиц - на выходе список запросов***/
	$query_select_for_hands = "SELECT concat('DROP TABLE IF EXISTS ', TABLE_NAME, ';')
	FROM information_schema.tables
	WHERE table_schema = '" . $DB_NAME . "';";
	
	
	$query_select = "SHOW TABLES FROM `" . $DB_NAME . "`";
	echo $query_select;
	$table_array = [];	
	$result = mysqli_query($link, $query_select);
	if($result){
		
		 while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC))
		 {
			 $table_array[] = $row["Tables_in_$DB_NAME"];
		 }

		
		foreach ($table_array as $table_name){
			echo $table_name . "<br>";
			mysqli_query($link, "DROP TABLE IF EXISTS " . $table_name . ";");
		}
		
	} else{
		echo "Ошибка: " . mysqli_error($conn);
	}
}
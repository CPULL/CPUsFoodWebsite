{ "result":"
<?php
include "database.php";

if (array_key_exists('Locale', $_GET)) { 
	$locale=$_GET['Locale'];
	updateLocaleOnDB($locale);
	echo "Locale updated to: $locale";
} else {
	echo 'Cannot find locale in GET';
}

?>
" }
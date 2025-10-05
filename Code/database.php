<?php // Functions to count how many times the site was visited

function connectSQL() {
$host_name = 'db5018728374.hosting-data.io';
$database = 'dbs14817097';
$user_name = 'dbu3445268';
$password = '3z9ag>zjuZa6xEX5!%h[a(3';
$sql = new mysqli($host_name, $user_name, $password, $database);

if ($sql->connect_error) {
	die('<p style="color:red">Database is out of office... '. $sql->connect_error .'</p>');
}

return $sql;
}
	

function countAccess() {
$sql = connectSQL();
$ip = $_SERVER['REMOTE_ADDR'];
$browser = md5($_SERVER['HTTP_USER_AGENT']);

$query = $sql->prepare("SELECT Count, LastAccess, SortMode FROM IPTracking WHERE IP=? AND Browser=? LIMIT 1");
$query->bind_param('ss', $ip, $browser);
$query->bind_result($connectionsCount, $lastAccess, $sortMode);
$result = $query->execute();
$query->store_result();

if ($query->num_rows == 0) {
	$connectionsCount = 1;
	$query = $sql->prepare("INSERT INTO `IPTracking`(`IP`, `Browser`, `Count`) VALUES (?, ?, ?)");
	$query->bind_param('ssd', $ip, $browser, $connectionsCount);
	$query->execute();
} else {
	$query->fetch();
	
	if((strtotime($lastAccess) + 3600) >  time()) { // Update if at least one hour passed
		$connectionsCount++;
		$now = date('Y/m/d h:i:s a', time());
		$query = $sql->prepare("UPDATE `IPTracking` SET `Count`=?,`LastAccess`=? WHERE IP=? AND Browser=?");
		$query->bind_param('dsss', $connectionsCount, $now, $ip, $browser);
		$query->execute();
	}
}

// Count all the connections
$query = $sql->prepare("SELECT Sum(Count) FROM IPTracking");
$query->bind_result($totalConnections);
$result = $query->execute();
$query->fetch();

mysqli_close($sql);

return [$totalConnections, $connectionsCount, $lastAccess, $sortMode];
}

function numberOfDistinctVisitors() {
$sql = connectSQL();
$query = $sql->prepare("SELECT count(distinct IP) FROM `IPTracking`");
$query->bind_result($count);
$result = $query->execute();
$query->fetch();
mysqli_close($sql);
return $count;
}


function updateSortMode($sortMode) {
	$sql = connectSQL();
	$ip = $_SERVER['REMOTE_ADDR'];
	$browser = md5($_SERVER['HTTP_USER_AGENT']);
	$query = $sql->prepare("UPDATE `IPTracking` SET `SortMode`=? WHERE IP=? AND Browser=?");
	$query->bind_param('dss', $sortMode, $ip, $browser);
	$query->execute();
	mysqli_close($sql);
}

function updateLocaleOnDB($locale) {
	if ($locale == 1 or $locale == "1" or $locale == "Imperial" or $locale == "imperial") $locale = 1;
	else $locale = 0;
	$sql = connectSQL();
	$ip = $_SERVER['REMOTE_ADDR'];
	$browser = md5($_SERVER['HTTP_USER_AGENT']);
	$query = $sql->prepare("UPDATE `IPTracking` SET `Locale`=? WHERE IP=? AND Browser=?");
	$query->bind_param('dss', $locale, $ip, $browser);
	$query->execute();
	mysqli_close($sql);
}

function readLocaleFromDB() {
	$sql = connectSQL();
	$ip = $_SERVER['REMOTE_ADDR'];
	$browser = md5($_SERVER['HTTP_USER_AGENT']);
	$query = $sql->prepare("SELECT Locale FROM `IPTracking` WHERE IP=? AND Browser=?");
	$query->bind_param('ss', $ip, $browser);
	$query->bind_result($locale);
	$result = $query->execute();
	$query->fetch();
	mysqli_close($sql);
	return $locale;
}

?>
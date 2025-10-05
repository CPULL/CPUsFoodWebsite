<!DOCTYPE html>
<html>
<head>
  <title>CPU's Food</title>
	<link rel="stylesheet" href="style.css">
	<script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"></script>
	<link rel="icon" type="image/x-icon" href="/Imgs/favicon.ico">
</head>
<body>

<?php // Code to check how many times this user connected
 include 'Code/database.php';
 $count = numberOfDistinctVisitors();
?>



<div class="Headertop">
	<h1>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;CPU's food stats</h1>
	<table class="HeadertopStats">
	<tr><td>Total number of visitors: <?= $count ?> </td></tr>
	</table>
</div>

<button onclick="window.history.go(-1); return false;" class="Back" title="Go back to all recipes">⇦ <span class="BackText">Back</span></button>

<?php
echo "<br><br><hr><br><br>";
?>


</body>
</html>
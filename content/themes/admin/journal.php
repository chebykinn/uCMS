<?php
p("<h2>System Journal</h2>");
$journalLines = file(LOG_FILE);
$journalLines = array_reverse($journalLines);
foreach ($journalLines as $line) {
	echo $line."<br>";
}
?>
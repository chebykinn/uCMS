<?php

use uCMS\Core\Debug;
$journalLines = @file(Debug::GetLogFile());
if(!empty($journalLines)){
	$journalLines = array_reverse($journalLines);
}else{
	$journalLines = array();
}
$headerLimit = 6;
$dateOffset = 0;
$hostOffset = 3;
$typeOffset = 4;
$messageOffset = 5;
?>
<table class="manage">
<tr>
	<th class="always-show"><?php p("Type"); ?></th>
	<th class="always-show"><?php p("Message"); ?></th>
	<th class="always-show"><?php p("Host"); ?></th>
	<th class="always-show"><?php p("Date"); ?></th>
</tr>
<?php
if(count($journalLines) > 0){
	foreach ($journalLines as $line) {
		$data = explode(" ", $line, $headerLimit);
		?>
		<tr>
		<td class="always-show"><?php echo $data[$typeOffset]; ?></td>
		<td class="always-show"><?php echo $data[$messageOffset]; ?></td>
		<td class="always-show"><?php echo substr($data[$hostOffset], 0, -1)?></td>
		<td class="always-show"><?php echo $data[$dateOffset].' '.$data[$dateOffset+1]; ?></td>
		</tr>
		<?php
	}
}else{
	?>
	<tr>
		<td colspan="4"><?php p("There are no log messages to display"); ?></td>
	</tr>
	<?php
}
?>
</table>
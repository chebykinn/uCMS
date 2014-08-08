<?php 
include "config.php";
include 'head.php';
include 'sidebar.php';
if(UCMS_MAINTENANCE){
	$event->do_actions("admin.main_page.mantenance");
	echo "<div class=\"warning\">".$ucms->cout("admin.alert.warning.maintenance", true)."</div><br>";
}

?>
<h2>μCMS <?php echo UCMS_VERSION; ?></h2><br>
	<table style="width: 100%;">
	<tr>
		<td style="vertical-align: top; width: 350px;">
			<?php if(is_activated_module('widgets')) $widget->load("sysinfo"); ?>
			<?php $event->do_actions("admin.main_page.main_table.first_column"); ?>
		</td>
		
		<td style="vertical-align: top;">
			<?php if(is_activated_module('widgets')) $widget->load("new_materials"); ?>
			<?php $event->do_actions("admin.main_page.main_table.second_column"); ?>
		</td>
	</tr>
	<?php $event->do_actions("admin.main_page.main_table"); ?>
</table>
<?php include "footer.php"; ?>
<?php $event->do_actions("admin.content.bottom"); ?>
	<div id="footer">
	<?php $event->do_actions("admin.footer.top"); ?>
		IVaN4B's μCMS © 2011-<?php echo date("Y"); ?><span> <?php echo $ucms->cout("admin.footer.queries", true)." ".$udb->get_queries_count().". "; ?><?php //id="info"
		echo $ucms->cout("admin.footer.loadtime", true, $ucms->get_load_time());
	?></span><span class="version"><?php echo $ucms->cout("admin.footer.version", true)." ".UCMS_VERSION; ?></span>
	<?php $event->do_actions("admin.footer.bottom"); ?>
	</div>
</div>
</td>
</tr>
</table>
</body>
</html>
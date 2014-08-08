<div class="ucms-search">
<form method="get" action="<?php if(!in_url('admin')) echo UCMS_DIR.'/'; ?>">
<?php 
	if(in_url('admin')){
		$gets = array('module', 'section', 'orderby', 'order', 'page', 'status');
		foreach ($gets as $get) {
	  		if(!empty($_GET[$get])) echo "<input type=\"hidden\" name=\"$get\" value=\"".htmlspecialchars($_GET[$get])."\">"; 
		}
	 }else
		 echo '<input type="hidden" name="action" value="search">'; 
?>
<input type="text" class="search-input" name="query" placeholder="<?php $ucms->cout("module.search.form.input.placeholder") ?>" value="<?php if(isset($_GET['query'])) echo htmlspecialchars($_GET['query']); ?>"><button type="submit" class="search-button" ><?php $ucms->cout("module.search.form.search.button") ?></button>
</form>
</div>
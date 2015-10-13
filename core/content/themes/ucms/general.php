<div class="wrapper">
	<header class="header">
		<?php
		if( $siteName ):
		?>
		<h1><a href="<?php echo $homePage; ?>"><?php 
		echo $siteName; ?></a></h1>
		<?php 
		endif; 
		if( $siteDescription ):
		?>
		<h2><?php echo $siteDescription; ?></h2>
		<?php
		endif;
		$this->region('header');
		?>
	</header><!-- .header-->

	<div class="middle">

		<div class="container">
			<main class="content">
				<?php
				if( $this->pageTitle() ){
					echo "<h2>".$this->pageTitle()."</h2>";
				}
				?>
				<div class="notifications">
					<?php $this->showNotifications(); ?>
				</div>
				<?php
				if( $this->pageContent() ){
					echo $this->pageContent();
				}
				
				$this->region('content');
				?>	
			</main><!-- .content -->
		</div><!-- .container-->

		<aside class="right-sidebar">
			<?php
			$this->region('right-sidebar');
			?>
		</aside><!-- .right-sidebar -->

	</div><!-- .middle-->

	<footer class="footer">
		<?php
		$this->region('footer');
		echo '<br>'.$queriesCount();
		echo '<br>'.$loadTime();
		?>
	</footer><!-- .footer -->

</div><!-- .wrapper -->
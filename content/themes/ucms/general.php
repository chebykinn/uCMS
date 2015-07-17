<div class="wrapper">
	<header class="header">
		<?php
		if( $this->getVar('site-name') ):
		?>
		<h1><a href="<?php echo $this->getVar('home-page'); ?>"><?php 
		echo $this->getVar('site-name'); ?></a></h1>
		<?php 
		endif; 
		if( $this->getVar('site-description') ):
		?>
		<h2><?php echo $this->getVar('site-description'); ?></h2>
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
			$user = $this->getVar('current-user');
			echo '<br>'.$user->getID().'<br>';
			echo '<br>'.$user->getName().'<br>';
			echo '<br>'.$user->getEmail().'<br>';
			$this->region('right-sidebar');
			?>
		</aside><!-- .right-sidebar -->

	</div><!-- .middle-->

	<footer class="footer">
		<?php
		$this->region('footer');
		echo '<br>'.$this->getVar('queries-count');
		echo '<br>'.$this->getVar('load-time');
		?>
	</footer><!-- .footer -->

</div><!-- .wrapper -->
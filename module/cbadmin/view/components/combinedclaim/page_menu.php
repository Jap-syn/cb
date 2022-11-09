<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => '.'
	 ),
	 array(
		 'title' => '請求取りまとめ事業者一覧',
		 'link' => 'combinedclaim/list'
	 ),
 );
 ?>
  <div id="navigation">
  <ul>
 <?php
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 if( $menu['link'] == $this->current_action ) $classes[] = 'current';
 ?>
   <li><a href="<?php echo $menu['link']; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
 }
 ?>
   </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  </div>
  <!-- navigation end -->

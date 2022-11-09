<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => '.'
	 ),
	 array(
		 'title' => 'APIユーザー一覧',
		 'link' => 'apiuser/list'
	 ),
	 array(
		 'title' => 'APIユーザー登録',
		 'link' => 'apiuser/add'
	 ),
	 array(
		'title' => 'APIユーザー → サイト 設定',
		'link' => 'apirel/apioemselect'
	 ),
	 array(
		'title' => 'サイト → APIユーザー 設定',
		'link' => 'apirel/entoemselect'
	 )
 );
 ?>
  <div id="navigation">
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
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
  </div>
  <!-- navigation end -->

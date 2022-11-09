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
		'link' => 'apirel/apioemselect',
		'alt_link' => 'apirel/api2ent'
	 ),
	 array(
		'title' => 'サイト → APIユーザー 設定',
		'link' => 'apirel/entoemselect',
		'alt_link' => 'apirel/ent2api'
	 )
 );
 ?>
  <div id="navigation">
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  <ul>
 <?php
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 //if( $menu['link'] == $this->current_action ) $classes[] = 'current';

	 $is_match = false;
	 if(strpos($this->current_action, $menu['link']) === 0) {
		$is_match = true;
	 }
	 else if(isset($menu['alt_link']) && strpos($this->current_action, $menu['alt_link']) === 0) {
		$is_match = true;
	 }
	 if($is_match) $classes[] = 'current';
 ?>
   <li><a href="<?php echo $menu['link']; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
 }
 ?>
   </ul>
  </div>
  <!-- navigation end -->

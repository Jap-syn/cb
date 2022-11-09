<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => '.'
	 ),
	 array(
		 'title' => '検索フォーム',
		 'link' => 'oemsearche/form'
	 ),
	 array(
		 'title' => 'OEM先一覧',
		 'link' => 'oem/list'
	 ),
	 array(
		 'title' => 'OEM先登録',
		 'link' => 'oem/form/mode/new'
	 )
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
  <?php echo $this->render('id_search_form.php'); ?>
  </div>
  <!-- navigation end -->

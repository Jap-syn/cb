<!-- navigation start -->
 <?php
 $menus = array(
     array(
         'title' => 'トップ',
         'link' => '.'
     ),
     array(
         'title' => 'OEM別配送方法一覧',
         'link' => 'oemdeliv/index'
     )
 );
 ?>
  <div id="navigation">
  <ul>
 <?php
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 if(is_array($menu['match'])) {
		foreach($menu['match'] as $link) {
			if($link == $this->current_action) {
				$classes[] = 'current';
				break;
			}
		}
	 } else {
		if( $menu['link'] == $this->current_action ) $classes[] = 'current';
	 }
 ?>
   <li><a href="<?php echo $menu['link']; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
 }
 ?>
   </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  </div>
  <!-- navigation end -->

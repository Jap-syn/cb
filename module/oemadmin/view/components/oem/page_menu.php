<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => array('.')
	 ),
	 array(
		 'title' => '登録情報',
		 'link' => array('oem/detail')
	 ),
	 array(
		 'title' => '情報編集',
		 'link' => array('oem/edit', 'oem/confirm', 'oem/back', 'oem/completion')
	 )
 );
 ?>
  <div id="navigation">
  <ul>
 <?php
 $cnt = 0;
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 if(in_array($this->current_action, $menu['link'])) $classes[] = 'current';
 ?>
   <li><a href="<?php echo $menu['link'][0]; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
    $menuCount = 0;
    if(!empty($menu)){
        $menuCount = count($menu);
    }
    if($menuCount > $cnt){
        echo '|';
    }
    $cnt++;
 }
 ?>
   </ul>
  </div>
  <!-- navigation end -->

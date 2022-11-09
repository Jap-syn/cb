<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => array('.')
	 ),
	 array(
		 'title' => '検索フォーム',
		 'link' => array('searche/form', 'searche/search')
	 ),
	 array(
		 'title' => '事業者一覧',
		 'link' => array('enterprise/list')
	 )
 );
 ?>
  <div id="navigation">
  <ul>
 <?php
 $cnt = 0;
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 if( in_array($this->current_action, $menu['link']) ) $classes[] = 'current';
 ?>
   <li>
       <a href="<?php echo $menu['link'][0]; ?>" class="<?php echo join(' ', $classes); ?>">
           <span><?php echo $menu['title']; ?></span>
       </a>
   </li>
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

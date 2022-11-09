<!-- navigation start -->
 <?php
 $menus = array(
     array(
         'title' => 'トップ',
         'link' => '.'
     ),
     array(
         'title' => '代理店一覧',
         'link' => 'agency/list'
     ),
     array(
         'title' => '代理店登録',
         'link' => 'agency/form'
     )
 );
  $checkLink = $this->currentAction;
 if(isset($this->mode)) {
     $checkLink = $this->currentAction.$this->mode;
 }
 ?>
  <div id="navigation">
  <ul>
 <?php
 foreach( $menus as $menu ) {
	 $classes = array( 'tabs' );
	 if( $menu['link'] == $checkLink ) $classes[] = 'current';
 ?>
   <li><a href="<?php echo $menu['link']; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
 }
 ?>
   </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  </div>
  <!-- navigation end -->

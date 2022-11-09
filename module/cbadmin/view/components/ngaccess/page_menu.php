<!-- navigation start -->
 <?php
 $menus = array(
     array(
         'title' => 'トップ',
         'link' => '.'
     ),
     array(
         'title' => '加盟店',
         'link' => 'ngaccess/list/mode/enterprise',
         'mode' => 'enterprise',
     ),
     array(
         'title' => '注文マイページ',
         'link' => 'ngaccess/list/mode/orderpage',
         'mode' => 'orderpage',
     ),
     array(
         'title' => '顧客マイページ',
         'link' => 'ngaccess/list/mode/mypage',
         'mode' => 'mypage',
     ),
     array(
         'title' => 'IPアドレス',
         'link' => 'ngaccess/list/mode/ip',
         'mode' => 'ip',
     ),
      array(
         'title' => 'CB管理画面',
         'link' => 'ngaccess/list/mode/cbadmin',
         'mode' => 'cbadmin',
     ),
      array(
         'title' => 'OEM管理画面',
         'link' => 'ngaccess/list/mode/oemadmin',
         'mode' => 'oemadmin',
     ),
 );
 ?>
  <div id="navigation">
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  <ul>
 <?php
 foreach( $menus as $menu ) {
     $classes = array( 'tabs' );
     if ($this->mode == $menu['mode']) {
        $classes[] = 'current';
     }
 ?>
   <li><a href="<?php echo $menu['link']; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
 <?php
 }
 ?>
   </ul>
  </div>
  <!-- navigation end -->

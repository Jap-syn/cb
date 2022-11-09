<!-- navigation start -->
<?php
$menus = array(
    array(
        'title' => 'トップ',
        'link' => '.'
    ),
    array(
        'title' => 'SMBC契約一覧',
        'link' => 'smbcpa/list',
        'match' => array('smbcpa/index', 'smbcpa/list')
    ),
    array(
        'title' => 'SMBC情報登録',
        'link' => 'smbcpa/new',
        'match' => array('smbcpa/new', 'smbcpa/edit')
    ),
    array(
        'title' => 'SMBC手動入金',
        'link' => 'smbcparcpt/index',
        'match' => array('smbcparcpt', 'smbcparcpt/index')
    )
);
if(isset($this->menuHide)) $menus = array();
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
<?php } ?>
   </ul>
  <?php echo $this->render('cbadmin/id_search_form.php'); ?>
  </div>
  <!-- navigation end -->

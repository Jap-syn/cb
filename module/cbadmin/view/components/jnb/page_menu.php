<!-- navigation start -->
<?php
$menus = array(
	array(
		'title' => 'トップ',
		'link' => '.'
	),
	array(
		'title' => 'JNB契約一覧',
		'link' => 'jnb/list',
		'match' => array('jnb/index', 'jnb/list')
	),
	array(
		'title' => 'JNB情報登録',
		'link' => 'jnb/new',
		'match' => array('jnb/new', 'jnb/edit')
	),
	array(
		'title' => 'JNB手動入金',
		'link' => 'jnbrcpt/index',
		'match' => array('jnbrcpt', 'jnbrcpt/index')
	),
	array(
		'title' => '自動入金実行状況',
		'link' => 'jnbmon/autorcpt'
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

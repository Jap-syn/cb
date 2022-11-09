<!-- navigation start -->
 <?php
 $menus = array(
	 array(
		 'title' => 'トップ',
		 'link' => array('.')
	 ),
	 array(
		 'title' => '検索フォーム',
		 'link' => array('searche/form')
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
	if(in_array($this->current_action, $menu['link'])) {
            $classes[] = 'current';
        }
?>
   <li><a href="<?php echo $menu['link'][0]; ?>" class="<?php echo join(' ', $classes); ?>"><span><?php echo $menu['title']; ?></span></a></li>
<?php
    $menusCount = 0;
    if(!empty($menus)){
        $menusCount = count($menus);
    }
    if($menusCount - 1 > $cnt){
        echo '|';
    }
    $cnt++;
}
$actionList = array('enterprise/detail' => '事業者詳細情報', 'enterprise/sbsetting' => '請求書同梱ツール設定');
if(array_key_exists($this->current_action, $actionList)) {
?>
   |<li><a class="tabs current"><span><?php echo $actionList[$this->current_action] ?></span></a></li>
<?php } ?>
   </ul>
  </div>
  <!-- navigation end -->

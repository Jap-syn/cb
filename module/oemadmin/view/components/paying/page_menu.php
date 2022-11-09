<!-- navigation start -->
<?php
$menus = array(
	array(
		'title' => 'トップ',
		'link' => array('.')
	),
	array(
		'title' => '立替確認',
		'link' => array('paying/list')
	),
	array(
		'title' => '立替実行済み',
		'link' => array('paying/elist')
	),
	array(
		'title' => '立替予測',
		'link' => array('paying/forecast2')
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
$actionList = array(
	'paying/dlist2' => '立替実行済み－事業者リスト',
	'paying/dlist3' => '立替確認－事業者リスト',
	'paying/trnlist' => '注文明細',
	'paying/cnllist' => 'キャンセル明細',
	'paying/stamplist' => '印紙代明細',
	'paying/paybacklist' => '立替精算戻し明細',
);
if(array_key_exists($this->current_action, $actionList)) {
?>
   |<li><a class="tabs current"><span><?php echo $actionList[$this->current_action] ?></span></a></li>
<?php } ?>
  </ul>
</div>
<!-- navigation end -->
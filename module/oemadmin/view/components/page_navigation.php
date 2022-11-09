<?php
/**
 * Coral_Pagerクラスによるページナビゲーションバー
 *
 * 元のViewに、以下のプロパティを設定しておく必要がある。
 *
 * - pager ........... [Coral_Pager] Coral_Pager のインスタンス
 * - current_page .... [int] 現在表示するページ番号
 * - page_links ...... [array] ページ移動先に関するURLを格納した連想配列。以下のキーが必要
 *                     - base ...... 現在ページの基準URL。基本は :controller/:action だが、ほかのパラメータ指定がある場合はそれも反映しておく
 *                     - prev ...... 現在の1つ前のページへのURL。 page_links['base']/page/(:current_page - 1) になる
 *                     - next ...... 現在の1つ後のページへのURL。 page_links['base']/page/(:current_page + 1) になる
 *
 * オプションで以下のプロパティを設定できる
 *
 * - droplist_id ..... [string] カスタムドロップダウンリストにする要素のID。1つのページに複数のページナビゲーションバーを
 *                     配置する場合には、このスクリプトをrenderする前にこのプロパティ設定をしておく必要がある。
 *                     省略時は 'page_nav_page_list' が採用される
 */
if( isset( $this->pager ) && isset( $this->current_page) && isset( $this->page_links ) && $this->pager->getTotalItems() > 0 ) {
	$item_count = $this->pager->getTotalItems();		// 合計項目数
    $max_page = $this->pager->getTotalPage();			// 最終ページ番号
    $page_range = $this->pager->getIndexRange( $this->current_page );		// 現在ページの項目の開始・終了インデックス番号（0ベース）
    $page_range_disp = $this->pager->getIndexRange( $this->current_page, true);	// 現在ページの項目の開始・終了位置（1ベース）

	// 前のページへのリンク
    $link_prev = $this->current_page > 1 ?
        "<a class=\"command\" href=\"{$this->page_links['prev']}\">前のページ</a>" : '<span class="command">前のページ</span>';

	// 次のページへのリンク
    $link_next = $this->current_page < $max_page ?
		"<a class=\"command\" href=\"{$this->page_links['next']}\">次のページ</a>" : '<span class="command">次のページ</span>';

	// ナビゲーション用カスタムドロップダウンの要素ID。省略時は'page_nav_page_list'
	$list_id = $this->droplist_id ? $this->droplist_id : 'page_nav_page_list';
?>
<div class="page_nav">
    <span class="pager">
		<span id="<?php echo $list_id; ?>" class="paging_droplist"><?php echo $this->current_page; ?></span> / <?php echo $max_page; ?> ページ
        <?php echo "{$link_prev}　{$link_next}"; ?>
    </span>
    <?php echo "{$item_count} 件中 {$page_range_disp['start']} ～ {$page_range_disp['end']} 件目を表示"; ?>
    
    <script>
    Event.observe( window, "load", function(evt) {
        var list = $R( 1, <?php echo $max_page; ?> ).map( function(i) {
            return { value : i, text : "{0} ページ".format( i ) };
        } );
		var droplist = Object.extend( new NetB.UI.CustomList( $("<?php echo $list_id; ?>"), null, list ), {
			onchange : function(item, index, list) {
				if( ! item || item.value == <?php echo $this->current_page; ?> ) return;
				$("<?php echo $list_id; ?>").innerHTML = item.value;
                setTimeout( function() {
                    window.location.href = "<?php echo $this->baseUrl . '/' . $this->page_links['base']; ?>/{0}".format( item.value );
                }, 50 );
            },
            onshow : function(list) {
				var value = parseInt( $("<?php echo $list_id; ?>").innerHTML );
                list.selectByValue( isNaN(value) ? -1 : value );
            }
        } );
    }.bindAsEventListener( window ) );
    </script>
</div>
<?php
}
?>


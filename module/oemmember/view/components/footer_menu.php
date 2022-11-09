<?php
//--------------------------------
// フッタ上のメニューバー
?>

<div class="footer_menu">
<?php
$menus = array(
    '<a href="index/index" title="トップページへ">トップページ</a>'
);
foreach($this->menuLinks as $link) {
    if(isset( $link['submenus'] ) ) {
        $links = $link['submenus'];
    } else {
        $links = array( $link );
    }

    foreach( $links as $l ) {
        $menus[] = "<a href=\"{$l['href']}\" title=\"{$l['desc']}\"" .
            ( isset($l['new']) ? " target=\"_blank\"" : '' ) . ">{$l['title']}</a>";
    }
}
echo join( '|', $menus );
?>
</div>

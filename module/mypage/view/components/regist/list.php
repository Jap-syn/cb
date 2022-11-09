<div style="clear: both;" id="breadcrumb">
<ul>
<li <?php if( $this->current == 'mail' || empty( $this->current ) ) { echo 'id="current"'; } ?>><label id="box" >メールアドレスの入力</label></li><img src="../images/arrow1.gif" width="14px" height="14px" />
<li <?php if( $this->current == 'precomp' ) { echo 'id="current"'; } ?>><label id="box" >仮登録完了　　　　</label></li><img src="../images/arrow1.gif" width="14px" height="14px" />
<li <?php if( $this->current == 'edit' ) { echo 'id="current"'; } ?>><label id="box" >登録情報の入力　　</label></li><img src="../images/arrow1.gif" width="14px" height="14px" />
<li <?php if( $this->current == 'conf' ) { echo 'id="current"'; } ?>><label id="box" >登録内容の確認　　</label></li><img src="../images/arrow1.gif" width="14px" height="14px" />
<li <?php if( $this->current == 'comp' ) { echo 'id="current"'; } ?>><label id="box" >登録完了　　　　　</label></li>
</ul>
</div>

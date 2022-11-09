<?php
$validList = $this->validList;
$invalidList = $this->invalidList;
?>

<div id="contents">
    <div class="content-box clearfix">
        <div class="list-box left-list-box">
            <h4>対象項目</h4>
            <select class="template-list" name="valid-list"  id="valid-list" size="2">
            <?php foreach( $validList as $value ) { echo '<option '; if( $value['RequiredFlg'] == 1 ) { echo 'class="required" '; } else { echo 'class="norequired" '; } echo 'value="' . $value['PhysicalName'] .'">' . $value['LogicalName'] . '</option>'; } ?></select>
            <div class="control-area">
                <button type="button" id="up-to-item">↑</button>
                <button type="button" id="down-to-item">↓</button>
                <button type="button" id="item-to-invalid">非対象項目へ →</button>
            </div>
        </div>

        <div class="list-box right-list-box">
            <h4>非対象項目</h4>
            <select class="template-list" name="invalid-list" id="invalid-list" size="2">
            <?php foreach( $invalidList as $value ) { echo '<option '; if( $value['RequiredFlg'] == 1 ) { echo 'class="required" ' ; } else { echo 'class="norequired" '; } echo 'value="' . $value['PhysicalName'] .'">' . $value['LogicalName'] . '</option>'; } ?></select>
            <div class="control-area">
                <button type="button" id="item-to-valid">← 対象項目へ</button>
                <button type="button" id="item-to-valid-all">← 全て対象項目へ</button>
            </div>
        </div>
    </div>
    <form id="form" action="changecsv/index" method="post">
        <input type="hidden" name="redirect" id="redirect" value="<?php echo f_e($this->redirect); ?>" />
        <input type="hidden" name="userid" id="userid" value="<?php echo $this->userId; ?>" />
        <input type="hidden" name="templateseq" id="templateseq" value="<?php echo $this->templateSeq; ?>" />
        <input type="hidden" name="validlistData" id="validListData" value="" />
        <input type="hidden" name="invalidlistData" id="invalidListData" value="" />
        <button type="submit" id="submit">登録</button>
        <button type="button" id="reset" onClick="location.href='<?php echo f_e($this->redirect); ?>'">リセット</button>
    </form>
</div>

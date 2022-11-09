<?php use oemmember\Application;?>
<table id="site_table" class="site_table" border="1" cellpadding="0" cellspacing="0">
<tr>
        <?php
        if (($this->entData['CreditTransferFlg'] == 1) || ($this->entData['CreditTransferFlg'] == 2) || ($this->entData['CreditTransferFlg'] == 3)) {
            ?>
            <th width="2%">No.</th>
            <th width="4%">ｻｲﾄID</th>
            <th width="14%">ｻｲﾄ名</th>
            <th width="14%">URL</th>
            <th width="4%">ﾒｰﾙｱﾄﾞﾚｽ</th>
            <th width="3%">ｻｲﾄ形態</th>
            <th width="7%">決済手数料率</th>
            <th width="8%">請求手数料(税抜)</th>
            <th width="7%">口振紙初回登録手数料(税抜)</th>
            <th width="7%">口振WEB初回登録手数料(税抜)</th>
            <th width="7%">口振引落手数料(税抜)</th>
            <th width="8%">同梱請求手数料(税抜)</th>
            <th width="4%">APIﾕｰｻﾞID</th>
            <th width="4%">支払方法種類</th>
            <th width="3%">状態</th>
            <th>変換表示</th>
            </tr>
            <?php
        } else {
        ?>
            <th width="2%">No.</th>
            <th width="4%">ｻｲﾄID</th>
            <th width="15%">ｻｲﾄ名</th>
            <th width="15%">URL</th>
            <th width="10%">ﾒｰﾙｱﾄﾞﾚｽ</th>
            <th width="7%">ｻｲﾄ形態</th>
            <th width="8%">決済手数料率</th>
            <th width="9%">請求手数料(税抜)</th>
            <th width="9%">同梱請求手数料(税抜)</th>
            <th width="8%">APIﾕｰｻﾞID</th>
            <th width="4%">支払方法種類</th>
            <th width="4%">状態</th>
            <th>変換表示</th>
            </tr>
        <?php
        }
use Coral\Coral\View\Helper\CoralViewHelperValueFormat;

$viewHelper = new CoralViewHelperValueFormat();

foreach( $this->siteList as $i => $siteInfo ) {
    $isValid = $siteInfo['ValidFlg'] ? true : false;
    $classAttr = $isValid ? '' : ' class="not_valid" title="このサイトは無効に設定されているため、注文登録を受け付けられません"';
?>
    <tr<?php echo $classAttr; ?> id="row_<?php echo $siteInfo['SiteId']; ?>">
        <td><?php echo $viewHelper->valueFormat( $i + 1 ); ?></td>
        <td><?php echo $viewHelper->valueFormat( $siteInfo['SiteId'] ); ?></td>
        <td><?php echo $viewHelper->valueFormat( $siteInfo['SiteNameKj'] ) ?></td>
        <td>
            <?php
                $url = "{$siteInfo['Url']}";
                $hasUrl = preg_match('/^http/', $url);

                if( $hasUrl ) echo '<a href="' . $viewHelper->valueFormat( $url ) . '">';

                echo $viewHelper->valueFormat( $url );

                if( $hasUrl ) echo '</a>';
            ?>
        </td>
        <td><?php echo $viewHelper->valueFormat( $siteInfo['ReqMailAddrFlg'] != 0 ? '必須' : '' ); ?></td>
        <td>
            <?php
                if( is_array( $this->masters['SiteForm'] ) ) {
                    echo $viewHelper->valueFormat( $this->masters['SiteForm'][$siteInfo['SiteForm']] );
                } else {
                    echo $viewHelper->valueFormat();
                }
            ?>
        </td>
        <td><?php  echo f_e(doubleval($siteInfo['SettlementFeeRate'])); ?> %</td>
        <td><?php  echo $viewHelper->valueFormat( $siteInfo['ClaimFeeBS'], 'number', '\ #,##0' ); ?></td>

            <?php
                if (($this->entData['CreditTransferFlg'] == 1) || ($this->entData['CreditTransferFlg'] == 2) || ($this->entData['CreditTransferFlg'] == 3)) {
                    ?>
                    <td> <?php echo $viewHelper->valueFormat( $siteInfo['FirstCreditTransferClaimFee'], 'number', '\ #,##0' ); ?></td>
                    <td> <?php echo $viewHelper->valueFormat( $siteInfo['FirstCreditTransferClaimFeeWeb'], 'number', '\ #,##0' ); ?></td>
                    <td> <?php echo $viewHelper->valueFormat( $siteInfo['CreditTransferClaimFee'], 'number', '\ #,##0' ); ?></td>
                    <?php
                }else{
                   // 表示しない
                }
            ?>
        <td>
            <?php
                echo is_null($siteInfo['ClaimFeeDK']) ? '-' : $viewHelper->valueFormat($siteInfo['ClaimFeeDK'], 'number', '\ #,##0');
            ?>
        </td>
        <td>
        <?php
            if (isset($this->api_user_ids)) {
                foreach ($this->api_user_ids as $key => $values) {
                    if ($key == $siteInfo['SiteId']) {
                        echo $viewHelper->valueFormat( join('、', $values) );
                    }
                }
            } else {
                // なにもしない
                ;
            }
        ?>
        </td>



<?php if (false) {?>
        <td><?php if(count($this->api_user_ids)) { echo $viewHelper->valueFormat( join('、', $this->api_user_ids) ); } else { ; } ?></td>
<?php } ?>
        <td style="text-align: center;">
            <?php if ($siteInfo['displayBtnFlag'] == 1) {?>
                <button class="do_cancel" id="cnl_<?php echo $siteInfo['SiteId']; ?>">詳細</button>
            <?php } ?>
        </td>
        <td><?php echo $viewHelper->valueFormat( $isValid ? '有効' : '無効' ); ?></td>
        <td><a href="account/changecsv/tid/<?php echo $this->templateid ?>/tclass/<?php echo $this->templateclass ?>/eid/<?php echo $this->entInfo['EnterpriseId'];?>/sid/<?php echo $siteInfo['SiteId']; ?>" target="_blank" >CSV項目設定</a>
        </td>
    </tr>
<?php
}
?>
</table>
<?php echo $this->render( 'oemmember/account/sbps_payments.php' ); ?>

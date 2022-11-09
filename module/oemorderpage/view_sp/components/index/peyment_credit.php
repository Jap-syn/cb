    <div id="d_01" style="background-color: #F0F0F0; width: 96%;">
        <table  width="95%">
            <tbody>
                <tr id="lbl_03">
                    <td colspan="5">ご利用できるお支払い方法</td>
                </tr>
<?php if ($order['CreditSettlementButton'] == '2') { ?>                
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_05">クレジットカード</td>
                    <td class="tb_06"></td>
                    <td class="tb_07">
                        <a id="paySelect_btn" float="center" href="<?php echo $order['CreditUrl'];?>">&nbsp;選択&nbsp;</a>
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_07" colspan="3" style="text-align: right; font-size: 10px; padding-right:10px;">
                        <?php echo  'お支払いページへ遷移'; ?>
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_07" colspan="3">
                        クレジットカード払いをご希望の方は<span class="c_red" style="font-size: 14px; font-weight: bold;"><?php echo ' '. f_df( $order['CreditLimitDate'], 'Y/m/d' ). ' '; ?></span>までにお手続きをお願いいたします。<br />期日を過ぎた場合は、他の支払い方法をご利用ください。
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                	<td class="tb_08"></td>
                    <td colspan="5">
                        <?php foreach (explode( ',', $order['CreditLogoUrl']) as $key => $val) { ?>
                            <img class="img_logo" src='<?php echo $this->baseUrl . "/../../images/" . $val; ?>' alt="" style="width: auto; height: 30px;">
                        <?php } ?>
                    </td>
                </tr>
<?php } ?>                
            </tbody>
        </table>
    </div>
    <?php if ($order['AppSettlementButton'] == '2') { ?>
    <div id="d_01" style="background-color: #F0F0F0; width: 96%">
            <table width="95%">
                <tbody>
                    <tr>
                        <td class="tb_08"></td>
                        <td class="tb_05">アプリ決済</td>
                        <td class="tb_06"></td>
                        <td class="tb_07">
                        <a id="paySelect_btn" float="center" href="<?php echo $order['SbpsUrl'];?>">&nbsp;選択&nbsp;</a>
                        </td>
                        <td class="tb_08"></td>
                    </tr>
                    <tr>
                    <td class="tb_08"></td>
                        <td class="tb_07" colspan="3" style="text-align: right; font-size: 10px; padding-right:10px;">
                            <?php echo  'お支払いページへ遷移'; ?>
                        </td>
                        <td class="tb_08"></td>
                    </tr>
                    <tr>
                        <td class="tb_08"></td>
<?php if ($order['Max_NumUseDay'] == 999) { ?>
                    	<td class="tb_07" colspan="3">
                            アプリ決済をご希望の方は選択ボタンをクリックしてください。
                        </td>
<?php }else{ ?>
                        <td class="tb_07" colspan="3">
                            アプリ決済をご希望の方は<span class="c_red" style="font-size: 14px; font-weight: bold;"><?php echo ' '. f_df( $order['AppLimitDate'], 'Y/m/d' ). ' '; ?></span>までにお手続きをお願いいたします。<br />期日を過ぎた場合は、他の支払方法をご利用下さい。
                        </td>
<?php } ?>
                        <td class="tb_08"></td>
                    </tr>

                    <tr>
                        <td class="tb_08"></td>
                        <td class="tb_07" colspan="3" style="font-size: 10px;">
                            ※ この決済は、<?= $order['SiteNameKj'] ?>での決済となります。
                        </td>
                        <td class="tb_08"></td>
                    </tr>

<?php if (!empty($order['SpecificTransUrl'])) { ?>
                    <tr>
                        <td class="tb_08"></td>
                        <td class="tb_07" colspan="3" style="font-size: 10px;">
                            この決済をご利用の場合は、下記リンクを必ずご確認下さい。
                        </td>
                        <td class="tb_08"></td>
                    </tr>
                    <tr>
                        <td class="tb_08"></td>
                        <td class="tb_07" colspan="3" style="font-size: 10px;">
                            <?php
                            if(strpos( $order['SpecificTransUrl'], "http://") !== false or strpos( $order['SpecificTransUrl'], "https://") !== false) {?>
                                <a href="<?= $order['SpecificTransUrl'] ?>" target="_blank">特定商取引に関する法律に基づく表示</a>
                            <?php } else {?>
                                <a href="//<?= $order['SpecificTransUrl'] ?>" target="_blank">特定商取引に関する法律に基づく表示</a>
                            <?php }
                            ?>
                        </td>
                        <td class="tb_08"></td>
                    </tr>
<?php } ?>

                    <tr>
                    	<td class="tb_08"></td>
                        <td colspan="5">
                            <?php foreach (explode( ',', $order['AppLogoUrl']) as $key => $val) { ?>
                                <img class="img_logo" src='<?php echo $this->baseUrl . "/../../images/" . $val; ?>' alt="" style="width: 60px; height: 30px;">
                            <?php } ?>
                        </td>
                    </tr>
                </tbody>
            </table>
    </div>
    <?php } ?>
    <div id="d_01" style="background-color: #F0F0F0; width: 96%;">
        <table  width="95%">
            <tbody>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_05">コンビニ</td>
                    <td class="tb_06"></td>
                    <td class="tb_07">
                        <a id="payHelp_btn" float="center" target="_blank" href="<?php echo $order['CVSUrl'];?>" style="white-space: nowrap; font-size: 10px;">&nbsp;ご利用方法&nbsp;</a>
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_07" colspan="3" style="font-size: 13px;">最寄りのコンビニ店頭レジ、及び 以下のアプリ請求書払いよりお支払いいただけます。</td>
                </tr>
                <tr>
                    	<td class="tb_08"></td>
                        <td colspan="5">
                            <?php foreach (explode( ',', $order['CombiniLogoUrl']) as $key => $val) { ?>
                                <img class="img_logo" src='<?php echo $this->baseUrl . "/../../images/" . $val; ?>' alt="" style="width: auto; height: 30px;">
                            <?php } ?>
                        </td>
                    </tr>
            </tbody>
        </table>
    </div>
    <div id="d_01" style="background-color: #F0F0F0; width: 96%;">
        <table  width="95%">
            <tbody>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_05">郵便</td>
                    <td class="tb_06"></td>
                    <td class="tb_07" >
                        <a id="payHelp_btn" float="center" target="_blank" href="<?php echo $order['PostUrl'];?>" style="white-space: nowrap; font-size: 10px;">&nbsp;ご利用方法&nbsp;</a>
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_07" colspan="3" style="font-size: 13px;">最寄りのゆうちょ銀行・郵便局よりお振込みいただけます。</td>
                </tr>
            </tbody>
        </table>
    </div>
    <div id="d_01" style="background-color: #F0F0F0; width: 96%;">
        <table  width="95%">
            <tbody>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_05">銀行</td>
                    <td class="tb_06"></td>
                    <td class="tb_07">
                        <a id="payHelp_btn" float="center" target="_blank" href="<?php echo $order['BankUrl'];?>" style="white-space: nowrap; font-size: 10px;">&nbsp;ご利用方法&nbsp;</a>
                    </td>
                    <td class="tb_08"></td>
                </tr>
                <tr>
                    <td class="tb_08"></td>
                    <td class="tb_07" colspan="3" style="font-size: 13px;">銀行窓口・ATM・ネットバンクにてお支払いいただけます。</td>
                </tr>
            </tbody>
        </table>
    </div>

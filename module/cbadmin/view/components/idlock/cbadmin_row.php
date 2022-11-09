<?php
$row = $this->row;
$dt = f_df($row['LogTime'], 'Y-m-d');
?>
<td class="l_data id-col">
    <a href="authlog/byid/loginid/<?php echo f_e($row['LoginId']); ?>/date/<?php echo f_e($dt); ?>" target="_blank" title="このログインIDのログを表示">
        <?php echo f_e($row['LoginId']); ?>
    </a>
</td>
<td class="l_data name-col"><?php echo f_e($row['Name']); ?></td>
<td class="l_data permit-col"><?php echo f_e($row['ExtraInfo']); ?></td>
<td class="l_data hash-col">
    <a href="authlog/byhash/hash/<?php echo f_e($row['ClientHash']); ?>/date/<?php echo f_e($dt); ?>" target="_blank" title="クライアント識別子 '<?php echo f_e($row['ClientHash']); ?>' のログを表示">
        <?php echo f_e(substr($this->row['ClientHash'], 0, 15).'…'); ?>
    </a>
</td>
<td class="c_data datetime-col"><?php echo f_df($row['LogTime'], 'y-m-d H:i'); ?></td>
<td class="c_data datetime-col release-time"><?php echo f_df($row['ReleaseTime'], 'y-m-d H:i'); ?></td>
<td class="c_data action-col">
    <a class="release-action" href="javascript:void(0)" onclick="return doRelease(<?php echo f_e($row['Seq']); ?>)">ロック解除</a>
</td>

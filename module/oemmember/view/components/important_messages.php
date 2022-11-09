<?php
use Zend\Json\Json;
use models\Logic\LogicImportantMessages;

$unreaded_imp_messages = array();
$all_imp_messages = array();
if(!preg_match('/^login\//', $this->currentAction)) {
    $msg_logic = new LogicImportantMessages();
    $imp_messages = $msg_logic->getMessages();
    $unreaded_imp_messages = $imp_messages['unreaded'];
    $all_imp_messages = array_merge($unreaded_imp_messages, $imp_messages['readed']);
}
?>
<script type="text/javascript">
Event.observe(window, 'load', function() {
<?php if($this->currentAction == 'index/index' && !empty($all_imp_messages)) { ?>
    var
        messages = <?php echo Json::encode($all_imp_messages); ?>,
        before = document.getElementsByClassName('main_contents')[0] || document.body.getElementsByTagName('*')[0],
        container = before ? before.parentElement : document.body,
        ele = Object.extend(document.createElement('div'), {
            innerHTML : messages.map(function(msg) { return msg.escapeHTML(); }).join('<br>'),
            className : 'important-message'
        });
    Object.extend(ele.style, {
    });
    container.insertBefore(ele, before );
<?php } else { ?>
<?php if(!empty($unreaded_imp_messages)) { ?>
    alert('<?php echo f_e(join(PHP_EOL, $unreaded_imp_messages)); ?>');
<?php } ?>
<?php } ?>
});
</script>

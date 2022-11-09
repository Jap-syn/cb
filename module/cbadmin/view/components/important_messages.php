<?php
use Zend\Json\Json;

$unreaded_imp_messages = array();
$all_imp_messages = array();
if(!preg_match('/^login\//', $this->currentAction)) {
    $msg_logic = new \models\Logic\LogicImportantMessages();
    $imp_messages = $msg_logic->getMessages();
    $unreaded_imp_messages = $imp_messages['unreaded'];
    $all_imp_messages = array_merge($unreaded_imp_messages, $imp_messages['readed']);
}
?>
<script type="text/javascript">
Event.observe(window, 'load', function() {
// count関数対策   
<?php if($this->currentAction == 'index/index' && !empty($all_imp_messages)) { ?>
    var
        messages = <?php echo Json::encode($all_imp_messages); ?>,
        before = ($('navigation') || document.body.getElementsByTagName('*')[0]).nextSibling,
        container = before ? before.parentElement : document.body,
        ele = Object.extend(document.createElement('div'), {
            innerHTML : messages.map(function(msg) { return msg.escapeHTML(); }).join('<br>')
        });
    Object.extend(ele.style, {
        backgroundColor : 'red',
        color : 'white',
        fontWeight: 'bold',
        margin : '4px 0',
        padding : '4px',
        fontSize : '16px'
    });
    container.insertBefore(ele, before);
<?php } else { ?>
// count関数対策
<?php if(!empty($unreaded_imp_messages)) { ?>
    alert('<?php echo f_e(join(PHP_EOL, $unreaded_imp_messages)); ?>');
<?php } ?>
<?php } ?>
});
</script>

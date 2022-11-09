<?php if(isset($this->validationResults) && ! $this->validationResults->isValid()) { ?>
<?php $vr = $this->validationResults; ?>
<ul id="validation_errors" class="error_info_container">
    <?php foreach($vr->getErrors() as $i => $err) { ?>
    <li class="error_message" onclick="show_error_field('form[<?php $ik = $vr->getInvalidKeys(); echo $ik[$i]; ?>]');"><?php echo f_e($err); ?></li>
    <?php } ?>
</ul>
<script type="text/javascript">
function show_error_field($field_name) {
    $A(document.getElementsByName($field_name)).each(function(ele) {
        var c = Application.UI.InputConverter.findConverter(ele);
        var target = c ? c.getFieldElements()[0] : ele;

        var conf = {
            x : 0,
            y : [target.clientTop, target.offsetTop, Position.cumulativeOffset(target)[1]].max(),
            callback : function() {
                try {
                    target.focus();
                } catch(e) {}
            }
        };
        var b = Application.UI.Browser;
        var scrollTarget = b.Engines.isWebKit ? document.body : null;
        new Application.UI.SmoothScroll(scrollTarget, conf);
        throw $break;
    });
}
</script>
<?php } ?>


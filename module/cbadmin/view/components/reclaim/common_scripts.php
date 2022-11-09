<?php if($this->ignoreDamageAmountForReclaim1) { ?>
<script type="text/javascript">
document.getElementsByClassName = function(className, parentElement) {
    var children = ($(parentElement) || document.body).getElementsByTagName('*');
    var results = [];
    for(var i = 0, l = children.length; i < l; i++) {
        var child = children[i];
        if(child.className.match(new RegExp('(^|\\s)' + className + '(\\s|$)'))) {
            results[results.length] = child;
        }
    }
    return results;
};
function updateDamageInterest() {
    var val = $('ClaimPattern').options[$('ClaimPattern').selectedIndex].value;
    document.getElementsByClassName('damage_interest_amount').each(function(ele) {
        Element[val == 2 ? 'hide' : 'show'](ele);
        var nextEle = ele.nextSibling;
        for(var i = 0; i < 5; i++) {
            try {
                Element[val == 2 ? 'show' : 'hide'](nextEle);
                break;
            } catch(e) {
                nextEle = nextEle.nextSibling;
            }
        }
        recalc(ele.id.replace(/^damageInterest/, ''));
    });
    Element[val == 2 ? 'show' : 'hide']('claim_pattern_msg');
}
Event.observe(window, 'load', function() {
    Event.observe($('ClaimPattern'), 'change', function(evt) {
        updateDamageInterest();
    }.bindAsEventListener($('ClaimPattern')));
    updateDamageInterest();
});
</script>
<?php } ?>
<style type="text/css">
#claim_pattern_msg {
    font-weight: bold;
    color: red;
}
</style>


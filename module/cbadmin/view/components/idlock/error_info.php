<?php if(count($this->errors)) { ?>
<ul class="error-list information">
<?php foreach($this->errors as $error) { ?>
  <li><?php echo f_e($error); ?></li>
<?php } ?>
</ul>
<?php } ?>
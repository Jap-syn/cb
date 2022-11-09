<?php $nav_base_url = $this->nav_base_url; ?>
<?php $link_suffix = isset($this->date) ? sprintf('/date/%s', $this->date) : ''; ?>
    <h4 class="category-title<?php if($this->is_bottom_nav) echo ' category-title-bottom'; ?>">
      <?php echo f_nf($this->total, '#,##0'); ?> 件
      <?php if($this->total) { ?>
      (<?php echo f_nf($this->start, '#,##0'); ?> ～ <?php echo f_nf($this->end, '#,##0'); ?> 件めを表示)
      <?php } ?>
      <div class="control-box">
        <div class="nav page-nav">
        <?php if($this->page > 1) { ?>
          <a href="<?php echo f_e($nav_base_url); ?>/page/<?php echo f_e(($this->page - 1).$link_suffix); ?>">&laquo; 前へ</a>
        <?php } else { ?>
          <span>&laquo; 前へ</span>
        <?php } ?>
          <span>｜</span>
        <?php if($this->end < $this->total) { ?>
          <a href="<?php echo f_e($nav_base_url); ?>/page/<?php echo f_e(($this->page + 1).$link_suffix); ?>">次へ &raquo;</a>
        <?php } else { ?>
          <span>次へ &raquo;</span>
        <?php } ?>
        </div>
      </div>
      <div class="clear-float"></div>
    </h4>

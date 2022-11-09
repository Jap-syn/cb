<?php
require('my_code_coverage.php');

$cc = new MyCodeCoverage();
$keyword = '';
$order = '';
$cov = array();

$orders = array(
  'file_path'         => 'file path',
  'ok_num'            => 'executed lines',
  'ng_num'            => 'lines not executed',
  'coverage_percents' => 'coverage percentage',
);

$cov_check = array(
  0   => '0%',
  1   => '1-30%',
  2   => '31-70%',
  3   => '71-99%',
  100 => '100%',
);

$targets = array(
  1  => '○',
  -1 => '×',
);

//if ($_POST['update'] && $_POST['target_cb']) {
if ( array_key_exists('update', $_POST) && array_key_exists('target_cb', $_POST) ) {
    $update_ids = array_keys($_POST['target_cb']);

  if ($_POST['update'] == 'target on') {
    $set_target = 1;
  } else {
    $set_target = 0;
  }
  $cc->changeTargets($update_ids, $set_target);
}

//if ($_POST['reset'] && $_POST['target_cb']) {
if ( array_key_exists('reset', $_POST) && array_key_exists('target_cb', $_POST)) {
    $update_ids = array_keys($_POST['target_cb']);
    $cc->resetCoverage($update_ids);
}

if (isset($_REQUEST['keyword'])) {
  $keyword = $_REQUEST['keyword'];
}

//if ($_REQUEST['order'] && isset($orders[$_REQUEST['order']])) {
if (array_key_exists('order', $_REQUEST) && isset($orders[$_REQUEST['order']])) {
    $order = $_REQUEST['order'];
}

if (isset($_REQUEST['target']) && $_REQUEST['target'] < 0) {
  $target = -1;
} else {
  $target = 1;
}

//if (isset($_REQUEST['cov'])) {
if (array_key_exists('cov', $_REQUEST)) {
    if (isset($_REQUEST['cov']) && count($_REQUEST['cov'] < 5)) {
        foreach ($_REQUEST['cov'] as $key => $val) {
          if (isset($cov_check[$key])) {
            $cov[$key] = $key;
          }
        }
  }
}

// ガバレッジ計測を行うか否かは、本ファイルと同フォルダ内に「iscoverage.txt」が存在するかどうかで判断する
$file = "iscoverage.txt";
$start_name = "▲▲▲計測を開始する▲▲▲";
$stop_name = "▼▼▼計測を停止する▼▼▼";
  if (array_key_exists('txtcaveragestart', $_REQUEST)){
    // 有効 もしくは 無効 ボタンを押した場合
    if ($_REQUEST['txtcaveragestart'] == $start_name){
        if (!file_exists($file)){
            // ファイルを作成
            touch($file);
        }
    }else{
        // ファイルを削除
        if (file_exists($file)){
            unlink($file);
        }
    }
  }

  $booCoverage = false;
if (file_exists($file))
{
  $booCoverage = true;
}

$txtcaveragestart_value = $booCoverage ? $stop_name : $start_name;



$data_list = $cc->getDataList($order, $keyword, $cov, $target);
$stat      = $cc->getStat();

?><html>
<style type="text/css">
<!--
body {
  padding-left: 10px;
}

h3 {
  margin-top: 30px;
  margin-bottom: 10px;
}

h4 {
  margin-top: 20px;
  margin-bottom: 5px;
}

table {
  border-collapse: collapse;
  border: solid 1px #aaa;
}
td, th {
  padding: 2px 10px;
}

th {
  background-color: #eee;
}

.stat td, .stat th {
  width: 120px;
}

.stat td {
  text-align: right;
}

.search th {
  width: 120px;
}

.search td {
  width: 200px;
}

.yellow {
  background-color: #ffc;
}

.red {
  background-color: #fcc;
}
.blue {
  background-color: #cff;
}
.CornflowerBlue {
  background-color: #6495ED;
}
.LightCoral {
  background-color: #F08080;
}

-->
</style>

<h2>coverage stat</h2>

<h3>計測状況</h3>
<?php
if ($booCoverage){
    $class ="CornflowerBlue";
}else{
    $class = "LightCoral";
}
?>
<form method="get">
<table>
<tr>
    <td>現在の状態：</td>
    <td class="<?php echo $class; ?>" ><?php echo $booCoverage ? '計測中' : '停止中'; ?></td>
</tr>
</table>
<input type="submit" name="txtcaveragestart"  value="<?php echo $txtcaveragestart_value; ?>" />
</form>

<h3>Total Statistics</h3>

<h4>Files</h4>
<table border class="stat">
  <tr>
    <th>executed files</th>
    <th>files not executed</th>
    <th>executed percentage</th>
  </tr>
  <tr>
    <td><?php echo intval($stat['executed_files']); ?></td>
    <td><?php echo intval($stat['not_executed_files']); ?></td>
    <td><?php echo sprintf("%01.2f", $stat['executed_files'] / $stat['total_files'] * 100); ?> %</td>
  </tr>
</table>

<h4>Lines(only executed files)</h4>
<table border class="stat">
  <tr>
    <th>executed lines (1)</th>
    <th>lines not executed (2)</th>
    <th>lines not used </th>
    <th>code coverage<br />(1) / (1)+(2) </th>
  </tr>
  <tr>
    <td><?php echo intval($stat['ok_sum']); ?></td>
    <td><?php echo intval($stat['ng_sum']); ?></td>
    <td><?php echo intval($stat['nouse_sum']); ?></td>
    <td><?php echo sprintf("%01.2f", $stat['ok_sum'] / ($stat['ok_sum'] + $stat['ng_sum']) * 100); ?>%</td>
  </tr>
</table>

<h3>Files Select</h3>
<form method="get">
<table border class="search">
  <tr>
    <th>keyword</th>
    <td><input type="text" name="keyword" value="<?php echo htmlentities($keyword, ENT_QUOTES, 'UTF-8'); ?>" style="width: 150px;"></td>
  </tr>
  <tr>
    <th>coverage</th>
    <td>
<?php foreach ($cov_check as $key => $val): ?>
      <input type="checkbox" name="cov[<?php echo $key; ?>]" value="1" id="cov<?php echo $key; ?>" <?php if (!isset($_REQUEST['cov']) || $_REQUEST["cov"][$key]):?> checked<?php endif; ?>>
      <label for="cov<?php echo $key; ?>"><?php echo $val; ?></label><br />
<?php endforeach; ?>
   </td>
  </tr>
  <tr>
    <th>target</th>
    <td>
      <select name="target">

<?php foreach ($targets as $key => $val): ?>
        <option value="<?php echo $key; ?>"<?php if ($key == $target): ?> selected<?php endif; ?>><?php echo $val;?></option>
<?php endforeach; ?>

      </select>
    </td>
  <tr>
    <th>order</th>
    <td>
      <select name="order">
<?php foreach ($orders as $key => $val): ?>
        <option value="<?php echo $key; ?>"<?php if ($key == $order): ?> selected<?php endif; ?>><?php echo $val; ?></option>
<?php endforeach; ?>
      </select>
    </td>
  <tr>
    <td colspan=2 style="text-align: center;"><input type="submit" value="search"></td>
  </tr>
</table>
</form>

<script type="text/javascript">
<!--
function changeAll(st) {
  var checkboxes = document.getElementsByTagName('input');
  var counter = 0;
  for (var i = 0; i < checkboxes.length; i++) {
    if (checkboxes[i].type == 'checkbox') {
      counter++;
      if (counter <= <?php echo count($cov_check); ?>) continue;
      if (st == 'select') {
        checkboxes[i].checked = true;
      } else {
        checkboxes[i].checked = false;
      }
    }
  }
}
--></script>

<?php if ($data_list): ?>
<form method="post">
<input type="button" name="check_all"  value="all select" onClick="changeAll('select');">
<input type="button" name="cancel_all" value="all unselect" onClick="changeAll('cancel');">
<input type="submit" name="update"     value="<?php if ($target > 0): ?>target off<?php else: ?>target on<?php endif; ?>">
<input type="submit" name="reset" value="reset coverage">
<table border>
  <tr>
    <th></th>
    <th>target</th>
    <th>file path</th>
    <th>executed lines</th>
    <th>lines not executed</th>
    <th>coverage</th>
  </tr>

  <?php foreach ($data_list as $key => $val):
  if ($val['ok_num'] + $val['ng_num']) {
    $cov_percent = sprintf("%01.2f", $val['ok_num'] / ($val['ok_num'] + $val['ng_num']) * 100);
  } else {
    $cov_percent = 0;
  }
  if ($cov_percent < 30)     $class = "red";
  elseif ($cov_percent < 90) $class = "yellow";
  elseif ($cov_percent >= 90) $class = "blue";
  else $class = '';
  ?>
  <tr>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><input type="checkbox" name="target_cb[<?php echo intval($val['id']); ?>]" value="1"></td>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><?php if ($val['is_target'] > 0): ?>○<?php else: ?>×<?php endif; ?></td>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><a target="_blank" href="view.php?id=<?php echo intval($val['id']); ?>"><?php echo htmlentities($val['file_path'], ENT_QUOTES, 'UTF-8'); ?></a></td>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><?php echo $val['ok_num']; ?></td>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><?php echo $val['ng_num']; ?></td>
    <td<?php if ($class): ?> class="<?php echo $class; ?>"<?php endif; ?>><?php echo $cov_percent; ?> %</td>
  </tr>
  <?php endforeach; ?>

</table>
</form>

<?php endif; ?>

</html>


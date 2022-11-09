<?php
require_once('my_code_coverage.php');
$cc = new MyCodeCoverage();

if ($_REQUEST['id']) {
  $data = $cc->getDetailById($_REQUEST['id']);
}
// $data = null;
// if (array_key_exists('id', $_REQUEST)) {
//     $data = $cc->getDetailById($_REQUEST['id']);
// }

?>
<html>
<style type="text/css">
<!--
body {
  padding-left: 10px;
  background-color: #dcdcdc;
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
  background-color: #ffffff;

}

.ok {
  background-color: #cff;
}

.ng {
  background-color: #fcc;
}

.nouse {
  background-color: #ddd;
}

.content {
  background-color: #adff2f;
}

-->
</style>

<!--  <h3><?php // echo $data==null ? '' : $data['db_data']['file_path']; ?></h3> -->
<h3><?php echo $data['db_data']['file_path']; ?></h3>

<table border>
  <tr class="content"><th>num</th><th>content</th></tr>

<?php foreach ($data as $num => $val): if ($num == 'db_data') continue; ?>
  <tr>
    <td<?php if (isset($val['ok'])): ?> class="ok"<?php elseif (isset($val['ng'])): ?> class="ng"<?php endif; ?>><?php echo intval($num); ?></td>
    <td<?php if (isset($val['ok'])): ?> class="ok"<?php elseif (isset($val['ng'])): ?> class="ng"<?php endif; ?>><?php echo str_replace(array(' ', "\t"), array('&nbsp;', '&nbsp;&nbsp'), htmlentities($val['content'], ENT_QUOTES, 'UTF-8')); ?></td>
  </tr>
<?php endforeach; ?>

</table>



</html>

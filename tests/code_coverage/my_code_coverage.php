<?php
class MyCodeCoverage {

  // mysql 接続情報
  const DSN = 'mysql:host=192.168.5.131;port=3306;dbname=coverage';
  const USER = "coverage";
  const PASSWORD = "coveragepass";
  public $db;
  public $have_recorded = false;

  public function __construct() {
      $options = array(
              PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
      );

      try {
      $this->db = new \PDO(MyCodeCoverage::DSN, MyCodeCoverage::USER, MyCodeCoverage::PASSWORD, $options);
    } catch (\Exception $e) {
      print $e->getMessage() ;
    }
  }

  //データベース初期化
  public function initialize($base_dir, $only_php = true) {
set_time_limit(0);
    $create_sql = "
      CREATE TABLE file_info (
        id int(10) unsigned primary key AUTO_INCREMENT,
        file_path varchar(1000),
        is_target integer default 0,
        ok_num integer default 0,
        ng_num integer default 0,
        nouse_num integer default 0,
        ok_rows text,
        ng_rows text,
        nouse_rows text,
        coverage_percents float,
        execute_count integer default 0,
        last_executed datetime
      )";
//20150117 ▼▼▼エラーになるため削除
    //$this->db->exec("DROP TABLE IF EXISTS file_info");
    $this->db->exec($create_sql);
//20150117 ▲▲▲エラーになるため削除
    if (!is_dir($base_dir)) {
      echo 'please specify correct base directory.';
      exit;
    }

//20150117 ▼▼▼PHP書式に変更
//     $cmd = "find {$base_dir} -type f";
//     if ($only_php) {
//       $cmd .= " | grep 'php$'";
//     }
//     exec($cmd, $result, $ret);
//20150117 ▲▲▲PHP書式に変更
//20150117 ▼▼▼PHP書式に変更
    // 指定されたディレクトリ以下のすべてのファイルを探す
    $base_dir = realpath($base_dir);
    $result = $this->list_files($base_dir);
//20150117 ▲▲▲PHP書式に変更


    $sql = "INSERT INTO file_info(file_path, is_target) VALUES(?, 1)";
    $st = $this->db->prepare($sql);

    foreach ($result as $file_path) {
      $st->execute(array($file_path));
    }
  }

//20150117 ▼▼▼PHP書式に変更
  public function list_files($dir) {
      $list = array();
      $files = scandir($dir);
      // ファイルがなかったらreturn
      if ($files == false){
          return $list;
      }
      foreach($files as $file){
          $fullpath = $dir . DIRECTORY_SEPARATOR . $file;
          if($file == '.' || $file == '..'){
              continue;
          } else if (is_file($fullpath)){
              // 拡張子を取得
              $info = new SplFileInfo($fullpath);
              // 拡張子がphpとphtmlだけ対象とする
              if ($info->getExtension() == "php" || $info->getExtension() == "phtml"){
                  $list[] = $fullpath;
              }
          } else if( is_dir($fullpath) ) {
              $list = array_merge($list, $this->list_files($fullpath));
          }
      }
      return $list;
  }
  //20150117 ▲▲▲PHP書式に変更

  //カバレッジ計測開始
  public function start() {
    xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE);
    //  xdebug_start_code_coverage();
  }

  //カバレッジ計測終了
  public function stop() {
    $result = xdebug_get_code_coverage();
    return $result;
  }

  //カバレッジ計測終了+DB登録
  public function stopAndRecord() {
    $result = $this->stop();
    $this->recordResults($result);
  }

  //カバレッジ計測結果のDB登録
  public function recordResults($result) {
    if ($this->have_recorded) {
      return;
    }

    $select_sql = "SELECT * FROM file_info WHERE file_path = ?";
    $sst = $this->db->prepare($select_sql);

    foreach ($result as $file_path => $cov_data) {
      $sst->execute(array($file_path));
      $ex_data = $sst->fetch(PDO::FETCH_ASSOC);

      if ($ex_data) {
        $new_data = $this->mergeData($cov_data, $ex_data);
        $this->updateById($new_data, $ex_data['id']);
      } else {
         $cov_data['file_path'] = $file_path;
         $this->insert($cov_data);
      }
    }
    $this->have_recorded = true;
  }

  //ファイル単位で、既存の計測データと新たに計測したデータのマージ
  public function mergeData($coverage_data, $existing_data) {

    $cols = array(
      1  => 'ok_rows',
      -1 => 'ng_rows',
      -2 => 'nouse_rows'
    );
    $row_arr = array(
      1  => array(),
      -1 => array(),
      -2 => array(),
    );

    foreach ($cols as $key => $col) {
      $row_arr[$key] = array();

      if ($existing_data[$col]) {
        $tmp = explode(',', $existing_data[$col]);
        foreach ($tmp as $row) {
          $row_arr[$key][$row] = $row;
        }
      }
    }

    //0行目と(最終行+1)行目がカウントされるので、除外する。
    array_pop($coverage_data);
    unset($coverage_data[0]);

    //$resは、1 => 実行した, -1 => 実行しなかった, -2 => 使われない
    foreach ($coverage_data as $row => $res) {
      //実行した行の時
      if ($res == 1) {
        $row_arr[$res][$row] = $row;
        unset($row_arr[-1][$row]);
        unset($row_arr[-2][$row]);

      //実行しなかった(されない)行の時
      } else {
        if ($existing_data['execute_count'] >= 1 && isset($row_arr[1][$row])) {
          continue;
        }
        $row_arr[$res][$row] = $row;
      }
    }
    foreach ($row_arr as $key => $arr) {
      ksort($row_arr[$key]);
    }

    $ret['ok_num']     = count($row_arr[1]);
    $ret['ng_num']     = count($row_arr[-1]);
    $ret['nouse_num']  = count($row_arr[-2]);
    $ret['ok_rows']    = implode(',', $row_arr[1]);
    $ret['ng_rows']    = implode(',', $row_arr[-1]);
    $ret['nouse_rows'] = implode(',', $row_arr[-2]);

    if ($ret['ok_num'] + $ret['ng_num']) {
      $ret['coverage_percents'] = (float)$ret['ok_num'] * 100 / ($ret['ok_num'] + $ret['ng_num']);
    } else {
      $ret['coverage_percents'] = -1;
    }
    $ret['execute_count'] = $existing_data['execute_count'] + 1;

    return $ret;
  }

  //ファイル単位の計測データ更新
  public function updateById($data, $id) {
    $columns = array(
      'ok_num', 'ng_num', 'nouse_num', 'ok_rows', 'ng_rows', 'coverage_percents', 'execute_count',
    );
    $last_executed = date('Y-m-d H:i:s');
    $update_sql = "UPDATE file_info SET last_executed = '{$last_executed}', ". implode(' = ?, ', $columns) ." = ? WHERE id = ?";

    foreach ($columns as $col) {
      $new_data[] = $data[$col];
    }
    $new_data[] = $id;
    return $this->db->prepare($update_sql)->execute($new_data);
  }

  //ファイル単位の計測データ新規作成
  public function insert($data) {
    $columns = array(
      'file_path', 'ok_num', 'ng_num', 'nouse_num', 'ok_rows', 'ng_rows', 'coverage_percents', 'execute_count',
    );
    $last_executed = date('Y-m-d H:i:s');
    $insert_sql = "INSERT INTO file_info (last_executed, ". implode(', ', $columns) .") VALUES ('{$last_executed}', ". str_repeat("?, ", count($columns) - 1) ."?)";
    foreach ($columns as $col) {
      $new_data[] = $data[$col];
    }
    return $this->db->prepare($insert_sql)->execute($new_data);
  }

  //計測データの集計対象フラグ更新
  public function changeTargets($ids, $is_target = 1) {
    if (!count($ids)) {
      return false;
    }
    if ($is_target) {
      $value = 1;
    } else {
      $value = 0;
    }
    $update_sql = "UPDATE file_info SET is_target = {$value} WHERE id IN (". str_repeat('?, ', count($ids) - 1) ."?)";
    return $this->db->prepare($update_sql)->execute($ids);
  }

  //計測データリセット
  public function resetCoverage($ids) {
    if (!count($ids)) {
      return false;
    }
    $update_sql = "
      UPDATE file_info
      SET ok_num = 0, ng_num = 0, nouse_num = 0, ok_rows = null, ng_rows = null, nouse_rows = null, coverage_percents = 0, execute_count = 0
      WHERE id IN (". str_repeat('?, ', count($ids) - 1) ."?)";
    return $this->db->prepare($update_sql)->execute($ids);
  }


  //ファイル毎のカバレッジ結果一覧取得
  public function getDataList($order = null, $keyword = null, $cov = null, $is_target = 1) {
    $wheres = array();
    $values = array();
    $cov_where_list = array(
      0   => 'coverage_percents <= 0 OR coverage_percents IS NULL',
      1   => 'coverage_percents > 0 AND coverage_percents <= 30',
      2   => 'coverage_percents > 30 AND coverage_percents <= 70',
      3   => 'coverage_percents > 70 AND coverage_percents < 100',
      100 => 'coverage_percents = 100',
    );

    switch ($order) {
      case "ok_num":
        $order = "ok_num DESC";
        break;
      case "ng_num":
        $order = "ng_num DESC";
        break;
      case "coverage_percents":
        $order = "coverage_percents DESC";
        break;
      case "file_path":
      default:
        $order = "file_path";
        break;
    }
    if ($is_target > 0) {
      $wheres[] = "is_target = 1";
    } else {
      $wheres[] = "is_target = 0";
    }
    if (strlen($keyword)) {
      $wheres[] = "file_path LIKE ?";
      $values[] = "%{$keyword}%";
    }
    if ($cov) {
      $or_wheres = array();

      foreach ($cov as $val) {
        $or_wheres[] = '('. $cov_where_list[$val] .')';
      }
      if (count($or_wheres) < count($cov_where_list)) {
        $wheres[] = '('. implode(' OR ', $or_wheres) .')';
      }
    }

    $select_sql = "SELECT * FROM file_info";
    if (count($wheres)) {
      $select_sql .= " WHERE ". implode(' AND ', $wheres);
    }
    $select_sql .= " ORDER BY {$order}";

    $st = $this->db->prepare($select_sql);
    $st->execute($values);

    return $st->fetchAll(PDO::FETCH_ASSOC);
  }

  //カバレッジ集計結果取得
  public function getStat() {
    $sql1 = "
      SELECT
        COUNT(*) AS total_files,
        SUM(ok_num) AS ok_sum,
        SUM(ng_num) AS ng_sum,
        SUM(nouse_num) AS nouse_sum
      FROM file_info
      WHERE is_target = 1
    ";
    $sql2 = "
      SELECT
        COUNT(*) AS not_executed_files
      FROM file_info
      WHERE is_target = 1 AND execute_count = 0
    ";
    $st1  = $this->db->prepare($sql1);
    $st1->execute();
    $ret1 = $st1->fetch(PDO::FETCH_ASSOC);
    $st2  = $this->db->prepare($sql2);
    $st2->execute();
    $ret2 = $st2->fetch(PDO::FETCH_ASSOC);

    $ret = array_merge($ret1, $ret2);
    $ret['executed_files'] = $ret['total_files'] - $ret['not_executed_files'];
    $ret['total_sum']      = $ret['ok_sum'] + $ret['ng_sum'] + $ret['nouse_sum'];
    return $ret;
  }

  /**
   *ファイル詳細表示用のデータ取得
   * @return
      array(
        'db_data' => ...,
        1 => array('content' => ..., [ok => 1, ng => 1]),
        [2 => array(...)],
        [3 => ...],
        ...
      )
   */
  public function getDetailById($id) {
    $st = $this->db->prepare("SELECT * FROM file_info WHERE id = ?");
    $st->execute(array($id));
    $data = $st->fetch(PDO::FETCH_ASSOC);

    if (!$data) return false;

    $ret['db_data'] = $data;
    $ok_rows = array();
    $ng_rows = array();

    if (strlen($data['ok_rows'])) {
      $tmp = explode(',', $data['ok_rows']);
      foreach ($tmp as $row) {
        $ok_rows[$row] = $row;
      }
    }
    if (strlen($data['ng_rows'])) {
      $tmp = explode(',', $data['ng_rows']);
      foreach ($tmp as $row) {
        $ng_rows[$row] = $row;
      }
    }

    $fp = fopen($data['file_path'], 'r');
    $num = 1;

    while (($row = fgets($fp))) {
      $ret[$num]['content'] = $row;

      if (isset($ok_rows[$num])) {
        $ret[$num]['ok'] = 1;
      } else if (isset($ng_rows[$num]) || !$data['execute_count']) {
        $ret[$num]['ng'] = 1;
      }

      $num++;
    }
    return $ret;
  }
}



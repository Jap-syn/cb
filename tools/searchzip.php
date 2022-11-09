<?php
chdir(dirname(__DIR__));

// Setup autoloading

require 'init_autoloader.php';

use Zend\Config\Reader\Ini;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

if (isset($_GET["zc"]))
{
    try
    {
        $configPath = __DIR__ . '/../module/cbadmin/config/config.ini';
        $data = array();
        if (file_exists($configPath))
        {
            $reader = new Ini();
            $data = $reader->fromFile($configPath);
        }

        $dbInfo = new Adapter($data['database']);

        $dbh = new \PDO(sprintf('mysql:host=%s;dbname=%s', $data['database']['hostname'], $data['database']['database']),
             $data['database']['username'], $data['database']['password']);

        $query = " SELECT MPOS.*, MPRE.PrefectureCode FROM M_PostalCode MPOS, M_Prefecture MPRE WHERE MPOS.PrefectureKanji = MPRE.PrefectureName AND MPOS.PostalCode7 = :PostalCode7 ";
        $stm = $dbInfo->query($query);

        $postalCode7 = mb_ereg_replace("[^0-9０-９]", "", $_GET["zc"]);
        $postalCode7 = mb_convert_kana($postalCode7, "n", "UTF-8");

        $prm = array(
           ':PostalCode7' => $postalCode7,
        );

        $row = $stm->execute($prm)->current();

        $dbh = null;

        echo json_encode($row);
    }
    catch (\PDOException $e)
    {
       die();
    }
    catch (\Exception $e)
    {
       die();
    }
}
else
{
?>
    function getAddress(zip)
    {
        //alert(zip);
        var url = '<?php echo $_SERVER['REQUEST_URI']; ?>?zc=' + zip;
        //alert(url);

        var ajax = new Ajax.Request(
            url,
            {
                method: 'GET',
                onComplete: setAddress
            }
        );
    }

    function setAddress(orgReq)
    {
        var jsonObj = eval('(' + orgReq.responseText + ')');

        //alert(orgReq.responseText);
        //alert(jsonObj["PrefectureKanji"]);

        $('PrefectureCode').selectedIndex = jsonObj["PrefectureCode"];
        //$('PrefectureName').value = jsonObj["PrefectureKanji"];
        $('City').value = jsonObj["CityKanji"];
        $('Town').value = jsonObj["TownKanji"];
    }
<?php
}
?>


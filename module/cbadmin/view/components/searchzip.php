function getAddress(zip)
{
    //alert(zip);
    var url = '<?php echo $this->baseUrl . '/Generalsvc/searchzip' ?>?zc=' + zip;
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

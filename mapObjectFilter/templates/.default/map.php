<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

function getColor($object_type_id = false){
    $colors = array(
        1 => "#542703",
        3 => "#083163",
        2 => "#2d6628",
        4 => "#f7ba0f"
    );
    return $colors[$object_type_id];
}

//print_r($arResult['filter']);

$handl_map_elements = CIBlockElement::GetList(
    array(),
    $arResult['filter'],
    false,
    false,
//    array('nTopCount' => 5000),

    array("ID", "NAME", "IBLOCK_ID", "PROPERTY_geo", "PROPERTY_TYPE")
);

while($map_element = $handl_map_elements->GetNext(true, false)){

    if (!$map_element['PROPERTY_GEO_VALUE']) continue;

    $geo_mark = explode(',', $map_element['PROPERTY_GEO_VALUE']);
    if($geo_mark[0] > $geo_mark[1]){
        $geo_mark = array($geo_mark[1], $geo_mark[0]);
    }else{
        $geo_mark = array($geo_mark[0], $geo_mark[1]);
    }
    $arObjects['geo'][] = array(
        "type" => "Feature",
        "id" => $map_element['ID'],
        "geometry" => array(
            "type" => "Point",
            "coordinates" => $geo_mark
        ),
        "options" => array(
            "iconColor" => getColor($map_element['PROPERTY_TYPE_ENUM_ID'])
        ),
        //"properties" => array(
        //	"balloonContent" => "<font size=3><b>" . $element['NAME'] . "</a></b></font>" .
        //            "<a target='_blank' href='" . $element['DETAIL_PAGE_URL'] . "'>Открыть</a>"
        //	)
    );
}

if(!empty($arObjects)){
    $arObjects['geo'] = array('type' => "FeatureCollection", 'features' => $arObjects['geo']);
}

if($arResult['postData']['getMarks'] == '1'){
    $arObjects['status'] = 'success';
    echo json_encode($arObjects);
}

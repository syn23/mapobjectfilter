<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Realty\Config\IDIBlock as ConfId;

include $_SERVER['DOCUMENT_ROOT'] . '/local/service_scripts/map_helper.php';

class ObjectFilter extends CBitrixComponent
{
    private $objectFilter = array();
    private $tmpObjectFilter = array();

//    private $objectsList = array();
    private $responceObjectsGeoList = array('geo' => array());

    private $objectsGeoList = array();
    private $objectsGeoCollection = array('geo' => array('type' => "FeatureCollection", 'features' => array()));

    public $colors = array(
        1 => "#542703",
        3 => "#083163",
        2 => "#2d6628",
        4 => "#f7ba0f"
    );

    private $dataFilter = array(
        'A', // квартиры
        'H', // частные дома
        'C', // комменрческая недвижимость
        'L', // земельные участки
    );

    public $handlerMapHelper = false;


    public function executeComponent()
    {
        global $APPLICATION;
        global $USER;

        $this->loadModule();
        $this->handlerMapHelper = new MapHelper();

        $this->request = \Bitrix\Main\Context::getCurrent()->getRequest();

        $post_data = $this->request->getPostList()->toArray();
        $get_data = $this->request->getQueryList()->toArray();

        // $this->arResult['geoCollection'] = array('type' => "FeatureCollection", 'features' => array());

        addFilterObjectRule($this->objectFilter);
        $this->objectFilter['!PROPERTY_GEO'] = false;
        $this->objectFilter['ACTIVE'] = 'Y';

        if($post_data['getMarksInPolygon']){
            $APPLICATION->RestartBuffer();

            $this->setObjectFilter($post_data);

            $this->cacheControl();

            $polygon = explode(',', $post_data['polygon']);

            // Получаем массив ID объектов, которые подходят под значение фильтра и могут выводиться на карту
//            $available_id_list = $this->getObjectsIDList();

            $objects_in_polygon = $this->handlerMapHelper->getObjectsInPolygonByPolygon($polygon, $this->arResult['available_id_list']);

            $features_from_objects_in_polygon['geo'] = $this->getFeaturesFromObjects($objects_in_polygon);

            require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
            header('Content-type: application/javascript');
            die(json_encode($features_from_objects_in_polygon));
        }

        if($get_data['bbox']){
            $APPLICATION->RestartBuffer();

            $this->setObjectFilter($get_data);
            $this->cacheControl();

            // Получаем массив ID объектов, которые подходят под значение фильтра и могут выводиться на карту
//            $available_id_list = $this->getObjectsIDList();

            $arr_bbox = explode(',', $_GET['bbox']);

            $objects_in_bbox = $this->handlerMapHelper->getObjectsInPolygonByBbox($arr_bbox, $this->arResult['available_id_list']);

            $features_from_objects_in_bbox = $this->getFeaturesFromObjects($objects_in_bbox);

            $response = $_GET['callback'] . '( ' .
                json_encode($features_from_objects_in_bbox)
            . ')';

            //echo $_GET['callback'] . '(' . json_encode($this->objectsGeoList) . ')';
            require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
            header('Content-type: application/javascript');
            die($response);
        }

        if($this->startResultCache(false, array($_GET['t'], $_GET['dt']), "/".SITE_ID.$this->GetRelativePath() . '/template'))
        {
            $this->arResult['OBJECT_PROP'] = $this->getProperties();
            $this->setResultCacheKeys(array('OBJECT_PROP'));

            $this->includeComponentTemplate();
        }
    }

    protected function loadModule()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        // \Bitrix\Main\Loader::IncludeModule("highloadblock");
    }

    protected function getProperties()
    {
        $output = array();
        $dbRes = \CIBlock::GetProperties(ConfId::object);
        while($res_arr = $dbRes->Fetch()){
            $output[$res_arr['CODE']] = $res_arr;
            $dbProp = CIBlockProperty::GetPropertyEnum($res_arr['ID']);
            while($res_prop = $dbProp->Fetch()){
                if($res_prop['VALUE'] == 'Суточно')
                    continue;
                $output[$res_arr['CODE']]['VALUE'][] = $res_prop;
            }
        }

        $selectRegion = getRegionCookie();
        $dbReg = CIBlockSection::GetList(Array("SORT" => "ASC"),
            array("IBLOCK_ID" => ConfId::regionNew, 'DEPTH_LEVEL' => 1),
            false,
            array('ID', 'NAME', 'UF_UZ_NAME', 'UF_CENTER_COORDS'));
        while($row = $dbReg->fetch()){
            if($selectRegion == $row['ID']){
                $row['selectRegion'] = true;
            }
            $row['UF_XML_ID'] = $row['ID'];
            $row['UF_NAME'] = $row['NAME'];
            $output['REGION']['VALUE'][] = $row;

            $this->arResult['regions_coords'][$row['ID']] = explode(',', $row['UF_CENTER_COORDS']);
        }

        /*
        $hlbl = ConfId::metro;
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity = $entity->getDataClass();
        $list = $entity::getList();
        $output['METRO']['VALUE'] = array();
        while($row = $list->fetch()){
            $output['METRO']['VALUE'][] = $row;
        }
        */

        $dbZastroyshiki = \Bitrix\Iblock\ElementTable::getList(array(
            'filter' => array('IBLOCK_ID' => ConfId::builders),
            'select' => array('ID', 'NAME')
        ));
        while($res = $dbZastroyshiki->fetch()){
            $output['Zastroyshiki']['LIST'][] = $res;
        }

        $dbComplex = \Bitrix\Iblock\ElementTable::getList(array(
            'filter' => array('IBLOCK_ID' => $output['COMPLEX']['LINK_IBLOCK_ID']),
            'select' => array('ID', 'NAME')
        ));
        while($res = $dbComplex->fetch()){
            $output['COMPLEX']['LIST'][] = $res;
        }

        $output['PRICE'] = $this->getMinMaxPrice();
        $output['AREA'] = $this->getMinMaxArea();
        return $output;
    }

    protected function getMinMaxPrice()
    {
        $usd_rate = getCurrencyUsdRate();
        
        $type_list = [
            'A' => 1, // Цены для квартир
            'H' => 2, // Цены для домов
            'C' => 3, // Цены для коммерч. недвижки
            'L' => 4  // Цены для учаситков
        ];

        foreach ($type_list as $type_key => $type_id) {

            $output[$type_key] = array('MIN' => 0, 'MAX' => 99999999);
            $arFilter = array('IBLOCK_ID' => ConfId::object, '!PROPERTY_PRICE' => false, 'PROPERTY_TYPE' => $type_id);
            addFilterObjectRule($arFilter);
            $arSelect = array('ID', 'PROPERTY_PRICE');
            $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE" => "DESC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
            if($ob_price = $db_price->Fetch()){
                $output[$type_key]['MAX'] = ceil((int)$ob_price['PROPERTY_PRICE_VALUE'] * $usd_rate);
            }
            $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE" => "ASC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
            if($ob_price = $db_price->Fetch()){
                $output[$type_key]['MIN'] = floor((int)$ob_price['PROPERTY_PRICE_VALUE'] * $usd_rate);
            }

            unset($arFilter['!PROPERTY_PRICE']);
            $arFilter['!PROPERTY_PRICE_SUM'] = false;

            $arSelect = array('ID', 'PROPERTY_PRICE_SUM');
            $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE_SUM" => "DESC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
            if($ob_price = $db_price->Fetch()){
                $sum_price = (int)$ob_price['PROPERTY_PRICE_SUM_VALUE'];
                if($sum_price > $output[$type_key]['MAX']){
                    $output[$type_key]['MAX'] = $sum_price;
                }
            }
            $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE_SUM" => "ASC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
            if($ob_price = $db_price->Fetch()){
                $sum_price = (int)$ob_price['PROPERTY_PRICE_SUM_VALUE'];
                if($sum_price < $output[$type_key]['MIN']){
                    $output[$type_key]['MIN'] = $sum_price;
                }
                //$output['MIN'] = (int)$ob_price['PROPERTY_PRICE_SUM_VALUE'];
            }
        }

        return $output;
    }

    protected function getMinMaxArea()
    {
        $output = array('MIN' => 0, 'MAX' => 99999999);
        $arFilter = array('IBLOCK_ID' => ConfId::object, '!PROPERTY_AREA' => false);
        addFilterObjectRule($arFilter);
        $arSelect = array('ID', 'PROPERTY_AREA');
        $db_price = CIBlockElement::GetList(Array("PROPERTY_AREA" => "DESC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
        if($ob_price = $db_price->Fetch()){
            $output['MAX'] = (int)$ob_price['PROPERTY_AREA_VALUE'];
        }
        $db_price = CIBlockElement::GetList(Array("PROPERTY_AREA" => "ASC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
        if($ob_price = $db_price->Fetch()){
            $output['MIN'] = (int)$ob_price['PROPERTY_AREA_VALUE'];
        }
        return $output;
    }

    private function ajaxRequest($postData)
    {
        $this->arResult['OBJECT_PROP'] = $this->getProperties();
        $this->arResult['post'] = $postData;
        switch($postData['type']){
            case '1':
                $this->includeComponentTemplate('apartment');
                break;
            case '2':
                $this->includeComponentTemplate('house');
                break;
            case '3':
                $this->includeComponentTemplate('commercial');
                break;
            case '4':
                $this->includeComponentTemplate('land-plot');
                break;
            default:
                $this->includeComponentTemplate('apartment');
        }
    }

    function getColor($object_type_id = false){
        return $this->colors[$object_type_id];
    }

    function getObjectsIDList(){
        $result = [];
        $handl_map_elements = CIBlockElement::GetList(
            array(),
            $this->objectFilter,
            false,
//                array('nTopCount' => 5000),
            false,
            array('ID', 'IBLOCK_ID')
        );

        while($map_element = $handl_map_elements->GetNext(true, false)){
            $result[] = $map_element['ID'];
        }
        return $result;
    }

    // Получаем список объектов для загрузки на клиент
    // На клиенте из списка формируем объект для отображения на карте
    protected function getObjectsList()
    {
        $handl_map_elements = CIBlockElement::GetList(
            array(),
            $this->objectFilter,
            false,
            //    array('nTopCount' => 5000),
            false,
            array("ID", "NAME", "IBLOCK_ID", "PROPERTY_geo", "PROPERTY_TYPE")
        );

        while($map_element = $handl_map_elements->GetNext(true, false)){

            $geo_mark = explode(',', $map_element['PROPERTY_GEO_VALUE']);
            if($geo_mark[0] > $geo_mark[1]){
                $geo_mark = array($geo_mark[1], $geo_mark[0]);
            }else{
                $geo_mark = array($geo_mark[0], $geo_mark[1]);
            }

            $map_element['GEO_MARK'] = $geo_mark;
            /*
            $map_element['GEO_MARK_TEST'] = array($geo_mark[0]+0.0001, $geo_mark[1]+0.01);
            $map_element['GEO_MARK_TEST2'] = array($geo_mark[0]+0.0002, $geo_mark[1]+0.02);
            */
//            $this->objectsList[] = $map_element;

            $this->responceObjectsGeoList['geo'][] = array(
                'i' => $map_element['ID'],
                'c' => implode(',', $map_element['GEO_MARK']),
                't' => $map_element['PROPERTY_TYPE_ENUM_ID']
            );
/*
            $this->responceObjectsGeoList['geo'][] = array(
                'i' => $map_element['ID'] + 100000,
                'c' => implode(',', $map_element['GEO_MARK_TEST']),
                't' => $map_element['PROPERTY_TYPE_ENUM_ID']
            );

            $this->responceObjectsGeoList['geo'][] = array(
                'i' => $map_element['ID'] + 200000,
                'c' => implode(',', $map_element['GEO_MARK_TEST2']),
                't' => $map_element['PROPERTY_TYPE_ENUM_ID']
            );
*/
            $this->objectsGeoList[] = array(
                "type" => "Feature",
                "id" => $map_element['ID'],
                "geometry" => array(
                    "type" => "Point",
                    "coordinates" => $map_element['GEO_MARK']
                ),
                "options" => array(
                    "iconColor" => $this->getColor($map_element['PROPERTY_TYPE_ENUM_ID'])
                ),
                //"properties" => array(
                //	"balloonContent" => "<font size=3><b>" . $element['NAME'] . "</a></b></font>" .
                //            "<a target='_blank' href='" . $element['DETAIL_PAGE_URL'] . "'>Открыть</a>"
                //	)
            );

        }
    }

    // Формируем массив объектов, пригодных к загрузке в loadingObjectManager на клиенте
    protected function getFeaturesFromObjects($objects){
        $result = array('type' => "FeatureCollection", 'features' => array());

        //print_r($objects);
        foreach($objects as $obj){
            $result['features'][] = array(
                "type" => "Feature",
                "id" => $obj['id'],
                "geometry" => array(
                    "type" => "Point",
                    "coordinates" => [$obj['lat'], $obj['lon']]
                ),
                "options" => array(
                    "iconColor" => $this->getColor($obj['type'])
                ),
                //"properties" => array(
                //	"balloonContent" => "<font size=3><b>" . $element['NAME'] . "</a></b></font>" .
                //            "<a target='_blank' href='" . $element['DETAIL_PAGE_URL'] . "'>Открыть</a>"
                //	)
            );
        }

        return $result;
    }

    protected function getObjectsGeoCollection(){
        $this->objectsGeoCollection['geo']['features'] = $this->objectsGeoList;
    }

    private function checkFilterObject()
    {
        global $filterObject;
        addFilterObjectRule($filterObject);
        $this->loadModule();
        $arSections = $this->arParams['arSections'];
        if($arSections[0] != 'filter' && $arSections[0] != null){
            $dbSections = \CIBlockSection::GetList(array(), array("IBLOCK_ID" => Realty\Config\IDIBlock::menuObject, "CODE" => $arSections), '', array('ID', 'IBLOCK_ID', 'UF_FILTER'));

            while($sect = $dbSections->Fetch()){

                foreach($sect['UF_FILTER'] as $i){
                    $f = explode('=', $i);
                    $filterObject[$f[0]] = $f[1];
                }
            }
            $this->arResult['filter'] = $filterObject;
        }else{
            $filterObject['ID'] = array();

            $filterObject = $this->convertForFilter($this->objectFilter, $filterObject);

            if(empty($filterObject['ID'])) unset($filterObject['ID']);
        }
    }

    private function setObjectFilter($request_data = []){
        foreach($this->dataFilter as $dataObj){
            if($request_data['ref'][$dataObj]){
                unset($request_data['ref'][$dataObj]['PROPERTY_']);
                $this->tmpObjectFilter[] = $this->convertForFilter($request_data['ref'][$dataObj]);
            }
        }
        if(count($this->tmpObjectFilter)){
            if(count($this->tmpObjectFilter) >= 2){
                $this->tmpObjectFilter['LOGIC'] = 'OR';
                $this->objectFilter[] = $this->tmpObjectFilter;
            }elseif(count($this->tmpObjectFilter) == 1){
                $this->objectFilter = array_merge($this->objectFilter,$this->tmpObjectFilter[0]);
            }
        }
    }

    private function cacheControl(){

        $this->arResult['available_id_list'] = $this->getObjectsIDList();

       if ($this->StartResultCache(60, serialize($this->objectFilter))){
           // Запрос данных и заполнение $arResult
           $this->arResult['available_id_list'] = $this->getObjectsIDList();
           $this->EndResultCache();
       }
    }

    /**
     * Конвертируем передаваемые параметры в массив для использования в getlist
     *
     * @param array $objectFilter
     * @param array $beforeData если нужно добавить параметры к существующему набору
     * @return array
     */
    protected function convertForFilter(array $objectFilter, array $beforeData = array())
    {
        $output = $beforeData;

        if(isset($objectFilter['search'])){
            $query = $objectFilter['search'];
            unset($objectFilter['search']);
            if(CModule::IncludeModule('search')){
                $obSearch = new CSearch;
                $arSearchQuery = array(
                    'QUERY' => trim($query),
                    "SITE_ID" => 's1'
                );

                $obSearch->Search($arSearchQuery);
                while($arSearch = $obSearch->GetNext()){
                    $arSearchResult[] = $arSearch['ITEM_ID'];
                };

                if($arSearchResult != null)
                    $filterOffice['ID'] = array_merge($output['ID'], $arSearchResult);
            };
        }else{
            unset($objectFilter['search']);
        }

        foreach($objectFilter as $code => $value){
            if(!in_array($code,['userID','FAVORITE','ID'])){
                $code = 'PROPERTY_' . $code;
            }

            if(is_array($value)){
                foreach($value as $value_item_key => $value_item){
                    if($value_item == '')
                        unset($value[$value_item_key]);
                }
            }
            if($value == '' || (is_array($value) && !count($value))) continue;
            if($code == 'PROPERTY_REGIONS'){ //получаем ID Всех обласей, районов в регионе
                $parentSections = array();
                $rs = CIBlockSection::GetList(
                    array(),
                    array('ID' => $value, "IBLOCK_ID" => ConfId::regionNew)
                );

                while($ar = $rs->GetNext()){
                    $dbRes = CIBlockSection::GetList(array(),
                        array(
                            "IBLOCK_ID" => ConfId::regionNew,
                            '>LEFT_MARGIN' => $ar['LEFT_MARGIN'],
                            '<RIGHT_MARGIN' => $ar['RIGHT_MARGIN'],
                        ),
                        false,
                        array('ID'));
                    while($sec = $dbRes->Fetch()){
                        $parentSections[] = $sec['ID'];
                    }
                }
                $value = $parentSections;

            }

            if(is_array($value)){
                switch($code){
                    case 'PROPERTY_AREA':
                        $output["><" . $code] = array($value['MIN'], $value['MAX']);
                        break;
                    case 'PROPERTY_PRICE':
                        $usd_rate = getCurrencyUsdRate();
                        $output[] = [
                            'LOGIC' => 'OR',
                            ["><PROPERTY_PRICE_SUM" => array($value['MIN'], $value['MAX']), "!PROPERTY_PRICE_SUM" => false],
                            ["><PROPERTY_PRICE" => array(floor($value['MIN']/$usd_rate), ceil($value['MAX']/$usd_rate)), "!PROPERTY_PRICE" => false]
                        ];
                        break;
                    case 'userID':
                        $output['CREATED_BY'] = $value;
                        break;
                    case 'PROPERTY_FLOOR_1':
                        if($value['IS_ALL']) break;
                        $output["><" . $code] = array($value['MIN'], $value['MAX']);
                        break;
                    case 'PROPERTY_BUILDERS':
                        $usersID = array();
                        $dbZastroyshiki = \CIBlockElement::getList(array(),
                            array('IBLOCK_ID' => ConfId::builders, 'ID' => $value, '!PROPERTY_USER' => false),
                            false,
                            false,
                            array('IBLOCK_ID', 'ID', 'PROPERTY_USER')
                        );
                        while($res = $dbZastroyshiki->Fetch()){
                            $usersID[] = $res['PROPERTY_USER_VALUE'];
                        }
                        $output['CREATED_BY'] = $usersID;
                        break;
                    default:
                        $output[$code] = $value;
                        break;
                }
            }else{
                switch($code){
                    case 'PROPERTY_PHOTO':
                        $output['!PROPERTY_PHOTO'] = false;
                        break;
                    case 'FAVORITE':
                        $output['ID'] = array_merge($output['ID'], getFavoriteObjectID());
                        break;
                    case 'ID':
                        $output['ID'] = array_merge($output['ID'], explode(',', $value));
                        break;
                    case 'userID':
                        $output['CREATED_BY'] = $value;
                        break;
                    default:
                        $output[$code] = $value;
                        break;
                }

            }
        }
//print_r($output);exit;
        return $output;
    }
}
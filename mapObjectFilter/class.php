<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

define('MAX_POINTS', 10000);
define('OFFSET', 268435456);
define('RADIUS', 85445659.4471); /* $offset / pi() */

use Realty\Config\IDIBlock as ConfId;

include $_SERVER['DOCUMENT_ROOT'] . '/local/service_scripts/map_helper.php';
include $_SERVER['DOCUMENT_ROOT'] . '/local/service_scripts/TileSystem.php';

//require_once $_SERVER['DOCUMENT_ROOT'] . '/local/classes/vendor/autoload.php';

class ObjectFilter extends CBitrixComponent
{
    private $objectFilter = array();
    private $tmpObjectFilter = array();

//    private $objectsList = array();
    private $responceObjectsGeoList = array('geo' => array());

    private $objectsGeoList = array();
    private $objectsGeoCollection = array('geo' => array('type' => "FeatureCollection", 'features' => array()));

    private $coords_objects = [];
    private $info_objects = [];
    private $csvPaths = '';

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
        $this->loadModule();
        $this->handlerMapHelper = new MapHelper();

        $this->request = \Bitrix\Main\Context::getCurrent()->getRequest();

        $post_data = $this->request->getPostList()->toArray();
        $get_data = $this->request->getQueryList()->toArray();

        $this->arResult['geoCollection'] = array('type' => "FeatureCollection", 'features' => array());

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

            $features_from_objects_in_polygon['geo'] = $this->getPointFeatures($objects_in_polygon);

            require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
            header('Content-type: application/javascript');
            die(json_encode($features_from_objects_in_polygon));
        }

        if($get_data['bbox']){
            $APPLICATION->RestartBuffer();

            $arr_bbox = explode(',', $_GET['bbox']);

//            $epsilon =5000 / (2 ** min(max(0, $_GET['z']), 20));
//
//            print_r($epsilon);
//            exit;
            $this->arResult['features_from_objects_in_bbox'] = [];
//            echo convert(memory_get_usage(true)) . "\n"; // 36640

            $this->setObjectFilter($get_data);

            if($this->StartResultCache(60 * 60 * 2, serialize($this->objectFilter))){
                // Запрос данных и заполнение $arResult

                // Получаем массив ID объектов, которые подходят под значение фильтра и могут выводиться на карту
                $this->arResult['available_id_list'] = $this->getObjectsIDList();
                $this->EndResultCache();
            }

//            if ($this->StartResultCache(60*60*2, serialize([$this->objectFilter, $arr_bbox, $_GET['z']]))){
            if(1){

                switch($_GET['z']){
                    case 1:
                        $epsilon = 5.0000;
                        break;
                    case 2:
                        $epsilon = 1.0000;
                        break;
                    case 3:
                        $epsilon = 0.4000;
                        break;
                    case 4:
                        $epsilon = 0.2000;
                        break;
                    case 5:
                        $epsilon = 0.1200;
                        break;
                    case 6:
                        $epsilon = 0.0880;
                        break;
                    case 7:
                        $epsilon = 0.0420;
                        break;
                    case 8:
                        $epsilon = 0.0140;
                        break;
                    case 9:
                        $epsilon = 0.0120;
                        break;
                    case 10:
                        $epsilon = 0.03;
                        break;
                    case 11:
                        $epsilon = 0.025;
                        break;
                    case 12:
                        $epsilon = 0.02;
                        break;
                    case 13:
                        $epsilon = 0.01;
                        break;
                    case 14:
                        $epsilon = 0.004;
                        break;
                    case 15:
                        $epsilon = 0.002;
                        break;
                    case 16:
                        $epsilon = 0.0008;
                        break;
                    case 17:
                        $epsilon = 0.0006;
                        break;
                    case 18:
                        $epsilon = 0.0004;
                        break;
                    case 19:
                        $epsilon = 0.0002;
                        break;
                }

//            $epsilon = 1.5;
//            $kms_per_radian = 6371.0088;
//            $epsilon = $epsilon / $kms_per_radian;
//                print_r($epsilon - 0);exit;

                $hash = md5(implode(',', $this->arResult['available_id_list']));
                $csv_points_name = $this->csvPaths . '/' . $hash . '_p.csv';
                $csv_pclusters = $this->csvPaths . '/' . $hash . '_pc.csv';
                $csv_centroids = $this->csvPaths . '/' . $hash . '_cn.csv';

                define('debug_csv', fopen('/var/www/domik.loc/www/local/components/innovative/mapObjectFilter/cluster_tmp/debug.csv', 'w'));
//                $debug_csv = fopen('/var/www/domik.loc/www/local/components/innovative/mapObjectFilter/cluster_tmp/debug.csv', 'w');

                $objects = $this->handlerMapHelper->getObjectsInPolygonByBbox($arr_bbox, $this->arResult['available_id_list'], $csv_points_name);

//                $clustered = cluster($objects, 20, $_GET['z']);
//                print_r($clustered);
//                exit;
//print_r($objects['grouped']);
//exit;
                $zoom = $_GET['z'];
                $is_arr = (is_array($csv_points_name))?'multiple':'single';
                fputcsv(debug_csv, ['zoom: ' . $zoom, ' mode: ' . $is_arr, ' eps: ' . $epsilon]);

                // Если в переменной $csv_points_name лежит массив - значит из БД выбрано много записей.
                // Кластеризация большого кол-ва записей приводит к сильному замедлению работы.
                // Потому весь список объектов разбит на отдельные файлы, каждый из которых будет отдельно кластеризован
                if(is_array($csv_points_name)){ // multiple

                    $clusters = []; // массив кластеров
                    $single_points = []; // массив одиночных точек
                    $single_clusters = []; // массив одиночных точек

                    // Список путей к файлам с центроидами кластеров для объединения
                    // Используется для слияния всех файлов с центроидами в один файл для финальной кластеризации
                    $parts_centroid_files = [];

                    // Список со всеми центроидами из всех частей выборки
                    // Используется при переборке результатов финальной кластеризации нескольких файлов
                    $centroids_common_list = [];

                    // Кластеризуем объекты в каждом файле
                    // Результаты сортируем по кластерам
                    foreach($csv_points_name['parts'] as $csv_part_key => $csv_part){
                        $parts_centroid_files[] = $csv_part['centroids'];

                        $comand = 'Rscript /home/roman/R/test.r ' . $epsilon . ' ' . $csv_part['points'] . ' ' . $csv_part['centroids'] . ' ' . $csv_part['cpoints'] . ' --no-save';
//                        $comand = 'mlpack_dbscan -S -t "ball" --input_file ' . $csv_part['points'] . ' --epsilon ' . $epsilon . ' --min_size 1 -a ' . $csv_part['cpoints'] . ' -C ' . $csv_part['centroids'];

                        shell_exec($comand);

                        $part_pclusters = explode(PHP_EOL, trim(file_get_contents($csv_part['cpoints'])));
                        $part_centroids = explode(PHP_EOL, trim(file_get_contents($csv_part['centroids'])));

//                        unlink($csv_part['points']); // Удаляем файл с точками текущей части
//                        unlink($csv_part['cpoints']); // Удаляем файл с кластеризованными точками текущей части

                        $part_clusters = []; // Кластеры в текущем файле

                        // $points_by_parts[$csv_part_key] =
//                        foreach($part_centroids as $centroid_key => $centroid){
//                            $centroid_coords = explode(',', $centroid);
//
//                            $points_by_parts[$csv_part_key]['clusters'][] = [
//                                'items_count' => 0,
//                                'lat'         => $centroid_coords[0] - 0,
//                                'lon'         => $centroid_coords[1] - 0,
//                                'items'       => [],
//                            ];
//                        }

                        // Перебираем кластеризованные точки файла, разбираем по кластерам
                        // $point_num - номер точки в файле, $cluster_num - номер кластера в файле
                        foreach($part_pclusters as $point_num => $cluster_num){

                            $object_key = ($csv_part_key) * MAX_POINTS + $point_num;
                            $object = $objects['points'][$object_key];

                            $grouped_by_coords_key = $object['lat'] . '_' . $object['lon'];

                            if(empty($cluster_num)){
                                // проверяем есть ли еще точки для этих координат
                                if(is_array($objects['grouped'][$grouped_by_coords_key]) && count($objects['grouped'][$grouped_by_coords_key])){
                                    // Если есть - создаем новый кластер для этой точки
                                    $tmp_clusters = [
                                        'lat'         => $object['lat'] - 0,
                                        'lon'         => $object['lon'] - 0,
                                        'items'       => [],
                                        'items_count' => 0,
                                    ];

                                    foreach($objects['grouped'][$grouped_by_coords_key] as $grouped_object){
                                        $tmp_clusters['items'][] = $grouped_object;
                                        $tmp_clusters['items_count']++;
                                        $tmp_clusters['weights'][$grouped_object['type']]++;
                                    }

                                    $single_clusters[] = $tmp_clusters;
                                }else{ // Выводим точку как единичный маркер
//                                    $single_points[] = $objects['points'][$point_num];
                                    $single_points[] = $object;
                                }
                            }else{
                                $part_clusters[$cluster_num]['items'][] = $object;
                                $part_clusters[$cluster_num]['items_count']++;
                                $part_clusters[$cluster_num]['weights'][$object['type']]++;

                                if(is_array($objects['grouped'][$grouped_by_coords_key]) && count($objects['grouped'][$grouped_by_coords_key])){

                                    foreach($objects['grouped'][$grouped_by_coords_key] as $grouped_object){
                                        $part_clusters[$cluster_num]['items'][] = $grouped_object;
                                        $part_clusters[$cluster_num]['items_count']++;
                                        $part_clusters[$cluster_num]['weights'][$grouped_object['type']]++;
                                    }
                                }

                                if(!$part_clusters[$cluster_num]['lat'] && !$part_clusters[$cluster_num]['lon']){
                                    $cluster_coords = explode(',', $part_centroids[$cluster_num-1]);
                                    $part_clusters[$cluster_num]['lat'] = $cluster_coords[0];
                                    $part_clusters[$cluster_num]['lon'] = $cluster_coords[1];
                                }
                            }

                        }

                        $centroids_common_list = array_merge($centroids_common_list, $part_clusters);

//                    $points_by_parts[$csv_part_key]['clusters_count'] = count($part_centroids);
                    }

                    //print_r('cat ' . implode(' ', $parts_centroid_files) . ' > ' . $csv_points_name['common']['clusters']);

                    // объединяем все файлы с центроидами
                    shell_exec('cat ' . implode(' ', $parts_centroid_files) . ' > ' . $csv_points_name['common']['clusters']);

                    foreach($parts_centroid_files as $csv_parts_centroid){
//                        unlink($csv_parts_centroid);
                    }
//                    print_r($csv_points_name['common']['clusters']);
//                    exit;

                    // Производим финальную кластеризацию, но с дистанцией между объектами в 2 раза меньше чем при первоначальной кластеризации по частям
                    // Это нужно для скейки близкорасположеных кластеров, которые могли появиться из-за разбивания выгрузки на неск. частей
                    $comand = 'Rscript /home/roman/R/test.r ' . $epsilon / 2 . ' ' . $csv_points_name['common']['clusters'] . ' ' . $csv_points_name['common']['clusters_centroids'] . ' ' . $csv_points_name['common']['clusters_cpoints'] . ' --no-save';
//                    $comand = 'mlpack_dbscan -S -t "ball" --input_file ' . $csv_points_name['common']['clusters'] . ' --epsilon ' . $epsilon . ' --min_size 1 -a ' . $csv_points_name['common']['clusters_cpoints'] . ' -C ' . $csv_points_name['common']['clusters_centroids'];

//                print_r($comand); exit;
                    shell_exec($comand);

                    $common_pclusters = explode(PHP_EOL, trim(file_get_contents($csv_points_name['common']['clusters_cpoints'])));
                    $common_centroids = explode(PHP_EOL, trim(file_get_contents($csv_points_name['common']['clusters_centroids'])));

//                    unlink($csv_points_name['common']['clusters']);
//                    unlink($csv_points_name['common']['clusters_cpoints']);
//                    unlink($csv_points_name['common']['clusters_centroids']);

                    $clusters = [];

                    foreach($common_centroids as $centroid_key => $centroid){
                        $centroid_coords = explode(',', $centroid);

                        $clusters[] = [
                            'items_count' => 0,
                            'lat'         => $centroid_coords[0] - 0,
                            'lon'         => $centroid_coords[1] - 0,
                            'items'       => [],
                        ];
                    }

                    foreach($common_pclusters as $common_cluster_num => $cluster_num){

                        if(empty($cluster_num)){
                            $clusters[] = $centroids_common_list[$common_cluster_num];
                        }else{

                            $clusters[$cluster_num]['items'] = array_merge($clusters[$cluster_num]['items'], $centroids_common_list[$common_cluster_num]['items']);
                            $clusters[$cluster_num]['items_count'] += $centroids_common_list[$common_cluster_num]['items_count'];

                            foreach($centroids_common_list[$common_cluster_num]['weights'] as $type_id => $weight){
                                if($clusters[$cluster_num]['weights'][$type_id]){
                                    $clusters[$cluster_num]['weights'][$type_id] += $weight;
                                }else{
                                    $clusters[$cluster_num]['weights'][$type_id] = $weight;
                                }
                            }

                        }
                    }

                    $clusters = array_merge($clusters, $single_clusters);
//
//                    $count_objects = count($single_points);
//                    foreach($clusters as $cluster){
//
//                        $count_objects += count($cluster['items']);
//
//                    }
//                    echo '$count_objects: ';
//                    echo $count_objects;
//                    exit;

                    $this->arResult['features_from_objects_in_bbox'] = $this->getClusterFeatures($clusters, $arr_bbox);
//                    $this->arResult['features_from_objects_in_bbox'] = $this->getClusterFeatures($centroids_common_list, $arr_bbox);
//                    print_r($single_points);
//                    exit;

                    if(count($single_points)){
                        $single_points_features = $this->getPointFeatures($single_points)['features'];
                        $this->arResult['features_from_objects_in_bbox']['features'] = array_merge($this->arResult['features_from_objects_in_bbox']['features'], $single_points_features);
                    }

                }else{  // single

                    if(file_exists($csv_points_name)){

                        $comand = 'Rscript /home/roman/R/test.r ' . $epsilon . ' ' . $csv_points_name . ' ' . $csv_centroids . ' ' . $csv_pclusters . ' --no-save';
//                        print_r($comand);
//                        $output = shell_exec($comand);
                        shell_exec($comand);

                        // Кластеризуем с помощью серверной утилиты
//                        $comand = 'mlpack_dbscan -S --input_file ' . $csv_points_name . ' --epsilon ' . $epsilon . ' --min_size 1 -a ' . $csv_pclusters . ' -C ' . $csv_centroids;
//                print_r($comand); exit;

                        //shell_exec($comand);
//                echo convert(memory_get_usage(true)) . "\n"; // 36640

                        if(file_exists($csv_pclusters) && file_exists($csv_centroids)){
//                            $pclusters = explode(',', trim(file_get_contents($csv_pclusters)));
//                            $centroids = explode(PHP_EOL, trim(file_get_contents($csv_centroids)));

                            $pclusters = explode(PHP_EOL, trim(file_get_contents($csv_pclusters)));
                            $centroids = explode(PHP_EOL, trim(file_get_contents($csv_centroids)));

//                            $pclusters = explode(PHP_EOL, trim(file_get_contents($this->csvPaths . '/' . 'point_by_cluster.csv')));
//                            $centroids = explode(PHP_EOL, trim(file_get_contents($this->csvPaths . '/' . 'centroids.csv')));

                            unlink($csv_points_name);
                            unlink($csv_pclusters);
                            unlink($csv_centroids);
//exit;
                            $clusters = [];

                            foreach($centroids as $centroid){
                                $centroid_coords = explode(',', $centroid);

                                $clusters[] = [
                                    'lat'         => $centroid_coords[0] - 0,
                                    'lon'         => $centroid_coords[1] - 0,
                                    'items'       => [],
                                    'items_count' => 0,
                                ];
                            }

                            $single_points = [];

                            foreach($pclusters as $point_num => $cluster_num){
                                $object = $objects['points'][$point_num];
                                $grouped_by_coords_key = $object['lat'] . '_' . $object['lon'];

                                // Если номер кластера пуст - точка не попадает ни в один из кластеров
                                if(empty($cluster_num)){

                                    // проверяем есть ли еще точки для этих координат
                                    if(is_array($objects['grouped'][$grouped_by_coords_key]) && count($objects['grouped'][$grouped_by_coords_key])){

                                        // Если есть - создаем новый кластер для этой точки

                                        $tmp_clusters = [
                                            'lat'         => $object['lat'] - 0,
                                            'lon'         => $object['lon'] - 0,
                                            'items'       => [],
                                            'items_count' => 0,
                                        ];

                                        foreach($objects['grouped'][$grouped_by_coords_key] as $grouped_object){
                                            $tmp_clusters['items'][] = $grouped_object;
                                            $tmp_clusters['items_count']++;
                                            $tmp_clusters['weights'][$grouped_object['type']]++;
                                        }

                                        $clusters[] = $tmp_clusters;
                                    }else{ // Выводим точку как единичный маркер
                                        $single_points[] = $objects['points'][$point_num];
                                    }
                                }else{
                                    $clusters[$cluster_num]['items'][] = $object;
                                    $clusters[$cluster_num]['items_count']++;
                                    $clusters[$cluster_num]['weights'][$object['type']]++;

                                    if(is_array($objects['grouped'][$grouped_by_coords_key]) && count($objects['grouped'][$grouped_by_coords_key])){
                                        foreach($objects['grouped'][$grouped_by_coords_key] as $grouped_object){
                                            $clusters[$cluster_num]['items'][] = $grouped_object;
                                            $clusters[$cluster_num]['items_count']++;
                                            $clusters[$cluster_num]['weights'][$grouped_object['type']]++;
                                        }
                                    }
                                }
                            }

                            $this->arResult['features_from_objects_in_bbox'] = $this->getClusterFeatures($clusters, $arr_bbox);

                            if(count($single_points)){
                                $single_points_features = $this->getPointFeatures($single_points)['features'];
                                $this->arResult['features_from_objects_in_bbox'] = array_merge($this->arResult['features_from_objects_in_bbox'], $single_points_features);
                            }
                        }

                    }
                }
                $this->EndResultCache();
            }else{

            }

//            $this->cacheControl();

//            print_r($this->csvPaths);

//            $available_id_list = $this->getObjectsIDList();

            try{
                fclose(debug_csv);
            }catch(Exception $e){

            }

            $response = $_GET['callback'] . '( ' .
                json_encode($this->arResult['features_from_objects_in_bbox'])
                . ')';

            require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
            header('Content-type: application/javascript');
            die($response);
        }

        $this->arResult['OBJECT_PROP'] = $this->getProperties();

        $this->includeComponentTemplate();

    }

    protected function loadModule()
    {
        \Bitrix\Main\Loader::includeModule('iblock');
        \Bitrix\Main\Loader::IncludeModule("highloadblock");

        $this->csvPaths = $_SERVER['DOCUMENT_ROOT'] . $this->getPath() . '/cluster_tmp';

        if(!is_dir($this->csvPaths)){
            mkdir($this->csvPaths);
        }
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

//        $selectRegion = getRegionCookie();
        $dbReg = CIBlockSection::GetList(Array("SORT" => "ASC"),
            array("IBLOCK_ID" => ConfId::regionNew, 'DEPTH_LEVEL' => 1),
            false,
            array('ID', 'NAME', 'UF_UZ_NAME'));
        while($row = $dbReg->fetch()){
            if($selectRegion == $row['ID']){
                $row['selectRegion'] = true;
            }
            $row['UF_XML_ID'] = $row['ID'];
            $row['UF_NAME'] = $row['NAME'];
            $output['REGION']['VALUE'][] = $row;
        }
        $hlbl = ConfId::metro;
        $hlblock = Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();
        $entity = Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
        $entity = $entity->getDataClass();
        $list = $entity::getList();
        $output['METRO']['VALUE'] = array();
        while($row = $list->fetch()){
            $output['METRO']['VALUE'][] = $row;
        }

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
        $output = array('MIN' => 0, 'MAX' => 99999999);
        $arFilter = array('IBLOCK_ID' => ConfId::object, '!PROPERTY_PRICE' => false);
        addFilterObjectRule($arFilter);
        $arSelect = array('ID', 'PROPERTY_PRICE');
        $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE" => "DESC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
        if($ob_price = $db_price->Fetch()){
            $output['MAX'] = (int)$ob_price['PROPERTY_PRICE_VALUE'];
        }
        $db_price = CIBlockElement::GetList(Array("PROPERTY_PRICE" => "ASC"), $arFilter, false, array("nPageSize" => 1), $arSelect);
        if($ob_price = $db_price->Fetch()){
            $output['MIN'] = (int)$ob_price['PROPERTY_PRICE_VALUE'];
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

    function getColor($object_type_id = false)
    {
        return $this->colors[$object_type_id];
    }

    function getObjectsIDList()
    {
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
                "type"     => "Feature",
                "id"       => $map_element['ID'],
                "geometry" => array(
                    "type"        => "Point",
                    "coordinates" => $map_element['GEO_MARK']
                ),
                "options"  => array(
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
    protected function getPointFeatures($objects)
    {
        $result = array('type' => "FeatureCollection", 'features' => array());

        //print_r($objects);
        foreach($objects as $obj){
            $result['features'][] = array(
                "type"     => "Feature",
                "id"       => $obj['id'],
                "geometry" => array(
                    "type"        => "Point",
                    "coordinates" => [$obj['lat'], $obj['lon']]
                ),
                "options"  => array(
                    "iconColor" => $this->getColor($obj['type'])
                )
            );
        }

        return $result;
    }

    // Формируем массив объектов, пригодных к загрузке в (loading/remote)ObjectManager на клиенте
    protected function getClusterFeatures($clusters, $arr_bbox = [])
    {
        $result = array('type' => "FeatureCollection", 'features' => array());

        //print_r($objects);
        foreach($clusters as $cluster_key => $cluster){

            if($cluster['items_count'] === 1){
                $result['features'][] = $this->getPointFeatures($cluster['items'])['features'][0];
            }else{
                $first = $cluster['items'][0];

                $weights = [];

                foreach($cluster['weights'] as $weight_key => $weight){
                    //$cluster['weights'][$weight_key] = ['weight' => $weight, 'color' => $this->getColor($weight_key)];
                    $weights[] = ['weight' => $weight, 'color' => $this->getColor($weight_key)];
                }

                $result['features'][] = array(
                    "geometry"   => array(
                        "type"        => "Point",
                        "coordinates" => [$cluster['lat'], $cluster['lon']]
                    ),
                    "type"       => "Cluster",
                    "id"         => $first['id'] . time(),
                    "bbox"       => [array_chunk($arr_bbox, 2)],
                    "properties" => array(
                        "data" => $weights
                    ),
                    "number"     => $cluster['items_count'],
                    //                    "features"   => $this->getPointFeatures($cluster['items'])['features']
                );
            }
        }

        return $result;
    }

    // Формируем массив объектов, пригодных к загрузке в loadingObjectManager на клиенте
    // legasy
    protected function getSingleObjectsFromCluster($clusters)
    {
        $result = [];

        //print_r($objects);
        foreach($clusters as $cluster_key => $cluster){
            if(count($cluster) == 1){
                $result[] = $cluster[0];
            }
        }

        return $result;
    }

    protected function getObjectsGeoCollection()
    {
        $this->objectsGeoCollection['geo']['features'] = $this->objectsGeoList;
    }

    private function checkFilterObject()
    {
        global $filterObject;
        addFilterObjectRule($filterObject);
        $this->loadModule();
        $arSections = $this->arParams['arSections'];
        if($arSections[0] != 'filter' && $arSections[0] != null){
            $dbSections = \CIBlockSection::GetList(array(), array(
                "IBLOCK_ID" => Realty\Config\IDIBlock::menuObject,
                "CODE"      => $arSections
            ), '', array('ID', 'IBLOCK_ID', 'UF_FILTER'));

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

    private function setObjectFilter($request_data = [])
    {
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
                $this->objectFilter = array_merge($this->objectFilter, $this->tmpObjectFilter[0]);
            }
        }
    }

    private function cacheControl()
    {

        $this->arResult['available_id_list'] = $this->getObjectsIDList();

//        if ($this->StartResultCache(60, serialize($this->objectFilter))){
//            // Запрос данных и заполнение $arResult
//            $this->arResult['available_id_list'] = $this->getObjectsIDList();
//            $this->EndResultCache();
//        }
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
                    'QUERY'   => trim($query),
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
            if(!in_array($code, ['userID', 'FAVORITE', 'ID'])){
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
                            "IBLOCK_ID"     => ConfId::regionNew,
                            '>LEFT_MARGIN'  => $ar['LEFT_MARGIN'],
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
                    case 'PROPERTY_PRICE':
                        $output["><" . $code] = array($value['MIN'], $value['MAX']);
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
        return $output;
    }
}

function convert($size)
{
    $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function lonToX($lon)
{
    return round(OFFSET + RADIUS * $lon * pi() / 180);
}

function latToY($lat)
{
    return round(OFFSET - RADIUS *
        log((1 + sin($lat * pi() / 180)) /
            (1 - sin($lat * pi() / 180))) / 2);
}

function pixelDistance($lat1, $lon1, $lat2, $lon2, $zoom)
{
    $x1 = lonToX($lon1);
    $y1 = latToY($lat1);

    $x2 = lonToX($lon2);
    $y2 = latToY($lat2);

    return sqrt(pow(($x1 - $x2), 2) + pow(($y1 - $y2), 2)) >> (21 - $zoom);
}

function cluster($markers, $distance, $zoom)
{
    $clustered = array();
    /* Loop until all markers have been compared. */
    while(count($markers)){
        $marker = array_pop($markers);
        $cluster = array();
        /* Compare against all markers which are left. */
        foreach($markers as $key => $target){
            $pixels = pixelDistance($marker['lat'], $marker['lon'],
                $target['lat'], $target['lon'],
                $zoom);
            /* If two markers are closer than given distance remove */
            /* target marker from array and add it to cluster.      */
            if($distance > $pixels){
                /*
                printf("Distance between %s,%s and %s,%s is %d pixels.\n",
                    $marker['lat'], $marker['lon'],
                    $target['lat'], $target['lon'],
                    $pixels);
                */
                unset($markers[$key]);
                $cluster[] = $target;
            }
        }

        /* If a marker has been added to cluster, add also the one  */
        /* we were comparing to and remove the original from array. */
        if(count($cluster) > 0){
            $cluster[] = $marker;
            $clustered[] = $cluster;
        }else{
            $clustered[] = $marker;
        }
    }
    return $clustered;
}
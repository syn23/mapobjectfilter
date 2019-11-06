<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

use Realty\Config\IDIBlock as ConfId;

CModule::IncludeModule("iblock");

Class MapHelper{

    public $objects = [];
    public $geoTableName = 'geo_objects';
    public $connectionHandler = false;

    // При создании объекта можем инициализировать хендлер для выполнения запросов к БД
    function __construct($initConnection = false){
        if($initConnection)
            $this->initConnectionHandler();
    }

    function initConnectionHandler(){
        $this->connectionHandler = Bitrix\Main\Application::getConnection();
        return true;
    }

    // Рассчитываем номера тайла по x и y исходя из широты и долготы
    function calcTileXY($latitude, $longitude) {

        $latitude = $latitude * M_PI/180; //перевод в радианы
        $longitude = $longitude * M_PI/180; //перевод в радианы

        $Rn = 6378137; // Экваториальный радиус
        $e = 0.0818191908426; // Эксцентриситет
        $esinLat = $e * sin($latitude);

        $tan_temp = tan(M_PI / 4 + $latitude / 2);
        $pow_temp = pow(tan(M_PI / 4 + asin($esinLat) / 2), $e);
        $U = $tan_temp / $pow_temp;

        $merkat_lat = $Rn * log($U);//меркаторовские координаты
        $merkat_lon = ($Rn * $longitude);//меркаторовские координаты

        $equatorLength = 40075016.685578488; //Длина экватора
        $worldSize = pow(2, 31); // Размер мира в пикселах

        $a = $worldSize / $equatorLength;
        $b = $equatorLength /2;

        $tile_X = floor((($b + $merkat_lon) * $a)/256);//номер тайла
        $tile_Y = floor((($b - $merkat_lat) * $a)/256);//номер тайла

        return ['x' => $tile_X, 'y' => $tile_Y];
    }

    // Добавляем объекты в гео таблицу
    function addToGeoTable($items = []){
        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $query_parts = [];
        foreach($items as $item){
            $query_parts[] = "(
                " . $item['ID'] . ", 
                " . $item['type'] . ", 
                " . $item['deal_type'] . ", 
                '" . $item['lat'] . "', 
                '" . $item['lon'] . "',
                POINT(" . $this->truncate_number($item['lat'],7) . ", " . $this->truncate_number($item['lon'],7) . ")
            )";
        }
        if(count($query_parts)){
            $query = "INSERT INTO " . $this->geoTableName . " 
                (element_id, type, deal_type, lat, lon, geo) 
                VALUES " . implode(',', $query_parts);

            $this->connectionHandler->query($query);
        }
    }

    // Обновляет объекты в гео таблице
    function updateGeoTable($items = []){
        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        foreach($items as $item){
            $query = "UPDATE " . $this->geoTableName . "
                SET 
                type = " . $item['type'] . ",
                deal_type = " . $item['deal_type'] . ",
                lat = '" . $item['lat'] . "',
                lon = '" . $item['lon'] . "',
                geo = POINT(" . $this->truncate_number($item['lat'],7) . ", " . $this->truncate_number($item['lon'],7) . ")  
                WHERE element_id = " . $item['ID'];

            $this->connectionHandler->query($query);
        }
    }

    // Удялаем объекты из гео таблицы
    function removeFromGeoTable($items = []){
        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $id_list_to_remove = [];

        foreach($items as $item){
            $id_list_to_remove[] = $item['ID'];
        }

        if(count($id_list_to_remove)){
            $query = "DELETE FROM " . $this->geoTableName . "
                WHERE element_id IN (" . implode(',', $id_list_to_remove) . ")";

            $this->connectionHandler->query($query);
        }
    }

    // Обрезает кол-во знаком после запятой до кол-ва, указанного в $precision
    function truncate_number( $number, $precision = 2) {
        // Zero causes issues, and no need to truncate
        if ( 0 == (int)$number ) {
            return $number;
        }
        // Are we negative?
        $negative = $number / abs($number);
        // Cast the number to a positive to solve rounding
        $number = abs($number);
        // Calculate precision number for dividing / multiplying
        $precision = pow(10, $precision);
        // Run the math, re-applying the negative value to ensure returns correctly negative / positive
        return floor( $number * $precision ) / $precision * $negative;
    }

    // Получаем массив всех добавленных в гео таблицу объектов
    function getObjectsAddedToGeoTable(){
        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $result = [];

        $query = "SELECT ELEMENT_ID FROM " . $this->geoTableName;
        $query_result = $this->connectionHandler->query($query);
        while ($record = $query_result->fetch()){
            $result[$record['ELEMENT_ID']] = $record['ELEMENT_ID'];
        }

        return $result;
    }

    // Получаем массив добавленные в гео таблицу объектов
    function checkObjectInGeoTableByElementId($element_id = false){

        if(!$element_id){
            throw new Exception('$element_id is empty, please pass it.');
        }

        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $query = "SELECT element_id, count(*) as count FROM " . $this->geoTableName . " WHERE element_id = " . $element_id;
        $query_result = $this->connectionHandler->query($query);
        while ($record = $query_result->fetch()){
            return $record['count'];
        }

        return false;
    }

    // Выбираем из гео таблицы объекты внутри тайла - LEGACY
    function getObjectsInTile($tile_bbox_coords = []){
        if(count($tile_bbox_coords) != 4)
            return false;

        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $start_tile = $this->calcTileXY($tile_bbox_coords[1], $tile_bbox_coords[0]);
        $end_tile = $this->calcTileXY($tile_bbox_coords[2], $tile_bbox_coords[3]);

        $result = [];

        $query = 'SELECT * FROM ' . $this->geoTableName . ' 
        WHERE 
        tile_x BETWEEN ' . $start_tile['x'] . ' AND ' . $end_tile['x'] . ' 
        AND 
        tile_y BETWEEN ' . $start_tile['y'] . ' AND ' . $end_tile['y'];

        print_r($query);

        $query_result = $this->connectionHandler->query($query);
        while ($record = $query_result->fetch()){
            $result[$record['ELEMENT_ID']] = $record;
        }

        return $result;
    }

    function getObjectsFromIblock($filter = [],$arNavStartParams = false){
        $arr_elements = [];

        $filter['PROPERTY_IS_VERIFIED_MODERATOR'] = 76;
        $filter['!PROPERTY_geo'] = false;
        $filter['ACTIVE'] = 'Y';
        $filter['IBLOCK_ID'] = ConfId::object;

        $handl_map_elements = CIBlockElement::GetList(
            array(),
            $filter,
            false,
            $arNavStartParams,
            array("ID", "NAME", "IBLOCK_ID", "PROPERTY_geo", "PROPERTY_TYPE", "PROPERTY_DEAL")
        );

        while($map_element = $handl_map_elements->GetNext(true, false)){

            $geo_mark = explode(',', $map_element['PROPERTY_GEO_VALUE']);

            if($geo_mark[0] > $geo_mark[1]){
                $geo_mark = array($geo_mark[1], $geo_mark[0]);
            }else{
                $geo_mark = array($geo_mark[0], $geo_mark[1]);
            }

            $map_element['GEO_MARK'] = $geo_mark;

            $elem = [
                'ID'        => $map_element['ID'],
                'type'        => $map_element['PROPERTY_TYPE_ENUM_ID'],
                'deal_type'        => $map_element['PROPERTY_DEAL_ENUM_ID'],
                'lat'       => $geo_mark[0],
                'lon'       => $geo_mark[1]
            ];

            $arr_elements[] = $elem;
        }

        return $arr_elements;
    }

    function getObjectsInPolygonByPolygon($polygon_coords = [], $available_id = false){
        if(!count($polygon_coords))
            return false;

        // Дублируем первый элемент в конец массива,
        // это нужно для того что бы полигон заканчивался в той точке откуда начинался
        if($polygon_coords[0] !== $polygon_coords[count($polygon_coords)-1])
            array_push($polygon_coords,$polygon_coords[0]);

        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $result = [];

        if(is_array($available_id) && !count($available_id)){
            return $result;
        }

        // Создаем полигон из координат полигона
        $polygon_by_polygon = implode(',', $polygon_coords);

        $query =  'SELECT element_id as id, type, lat, lon  FROM ' . $this->geoTableName . ' 
            WHERE ST_Contains(GeomFromText("POLYGON((' . $polygon_by_polygon . '))"), geo)';

        if(is_array($available_id) && count($available_id)){
            $query .= ' AND element_id IN (' . implode(',', $available_id) . ')';
        }

        $query_result = $this->connectionHandler->query($query);
        while ($record = $query_result->fetch()){
            $result[$record['id']] = $record;
        }

        return $result;
    }

    function getObjectsInPolygonByBbox($bbox_coords = [], $available_id = []){
        if(count($bbox_coords) != 4)
            return false;

        if(!$this->connectionHandler)
            $this->initConnectionHandler();

        $result = [];

        if(is_array($available_id) && !count($available_id)){
            return $result;
        }
        // Создаем полигон из координат bbox
        $polygon_by_bbox = [];
        $polygon_by_bbox[] = $bbox_coords[0] . ' ' . $bbox_coords[1];
        $polygon_by_bbox[] = $bbox_coords[0] . ' ' . $bbox_coords[3];
        $polygon_by_bbox[] = $bbox_coords[2] . ' ' . $bbox_coords[3];
        $polygon_by_bbox[] = $bbox_coords[2] . ' ' . $bbox_coords[1];
        $polygon_by_bbox[] = $bbox_coords[0] . ' ' . $bbox_coords[1];

        $polygon_by_bbox = implode(',', $polygon_by_bbox);

        $query =  'SELECT element_id as id, type, lat, lon  FROM ' . $this->geoTableName . ' 
            WHERE ST_Contains(GeomFromText("POLYGON((' . $polygon_by_bbox . '))"), geo)';

        if(count($available_id)){
            $query .= ' AND element_id IN (' . implode(',', $available_id) . ')';
        }

        $query_result = $this->connectionHandler->query($query);
        while ($record = $query_result->fetch()){
            $result[$record['id']] = $record;
        }

        return $result;
    }

    function getObjectsInPolygon(){

        // пример рабочего запроса
        /*
         SELECT * FROM geo_objects WHERE MBRContains( GeomFromText('POLYGON((
42.41419359394269 60.008556905744186,
41.89932357172882 61.425793233869186,
41.736281398027764 62.063000265119186,
41.298641879145976 62.44752174949419,
40.91248936248557 62.623302999494186,
40.706541353600024 62.623302999494186,
40.56066151397276 62.52442604636919,
40.3890381732348 62.28272682761919,
40.32038883693962 62.00806862449419,
40.260320667681334 60.821545186994186,
40.42336284138239 59.722912374494186,
40.577823848046556 59.338390890119186,
40.82667769211659 59.206554952619186,
41.367291215441156 59.184582296369186,
42.379868925795094 59.415295186994186,
42.55149226653305 59.678967061994186,
42.56865460060685 59.843761983869186,
42.354125424684405 60.008556905744186,
42.41419359394269 60.008556905744186) )') ,geo)

         */

    }

}
<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

/*
$filterData = $arResult['postData'];
$filterProp = array();
if($filterData['propFilter']){
    $filterProp = CUtil::PhpToJSObject(json_decode($filterData['propFilter'],true));
}else{
    $prop = array();
    foreach ($arResult['filter'] as $key=>$val){
        if(is_array($val)){
            $prop['filter['.$key.'][]'] = $val;
        }else
            $prop['filter['.$key.']'] = $val;
    }

    $filterProp = CUtil::PhpToJSObject($prop);
}
*/
?>
<script>
    var mapfilter = undefined;
    $(document).ready(function () {
        mapfilterHandler = new mapFilter(
            <?/*
            {
                type: <?=$arResult['filter']['PROPERTY_TYPE'] ? $arResult['filter']['PROPERTY_TYPE'] : '1'?>,
                isAjax: true<?//=$arParams['IS_AJAX_FILTER'] ? 'true' : 'false'?>
            },
            <?=$filterProp?>*/?>
        );
    });
</script>

<section class="map_filter">

    <div class="map_filter--base">
        <?include 'base_types_filter.php'?>
        <form class="map_filter_hidden<?=($_GET['t'] == 'f')?' ready':''?>" data-wrap-filter data-type="apartment" method="post">
            <?include 'apartment.php'?>
        </form>
        <form class="map_filter_hidden<?=($_GET['t'] == 'h')?' ready':''?>" data-wrap-filter data-type="house" method="post">
        <?include 'house.php'?>
        </form>
        <form class="map_filter_hidden<?=($_GET['t'] == 'c')?' ready':''?>" data-wrap-filter data-type="commercial" method="post">
            <?include 'commercial.php'?>
        </form>
        <form class="map_filter_hidden<?=($_GET['t'] == 'l')?' ready':''?>" data-wrap-filter data-type="land_plot" method="post">
            <?include 'land-plot.php'?>
        </form>
    </div>

    <script>
        <?/*
        var objectsMap = new JCMapObjects_dev(<?=CUtil::PhpToJSObject($arResult['geoCollection'])?>);
        */?>
        var objectsMap = new mapFilterYandexMap(<?=CUtil::PhpToJSObject($arResult['regions_coords'])?>);
        $(document).ready(function () {
            objectsMap.init();
        });
    </script>

    <div class="main_map--wrap">
        <div class="map_filter--loader-wrap">
            <div class="map_filter--loader">
                <div class="sk-circle preloader">
                    <div class="sk-circle1 sk-child"></div>
                    <div class="sk-circle2 sk-child"></div>
                    <div class="sk-circle3 sk-child"></div>
                    <div class="sk-circle4 sk-child"></div>
                    <div class="sk-circle5 sk-child"></div>
                    <div class="sk-circle6 sk-child"></div>
                    <div class="sk-circle7 sk-child"></div>
                    <div class="sk-circle8 sk-child"></div>
                    <div class="sk-circle9 sk-child"></div>
                    <div class="sk-circle10 sk-child"></div>
                    <div class="sk-circle11 sk-child"></div>
                    <div class="sk-circle12 sk-child"></div>
                </div>
            </div>
        </div>
        <div id="main_map_fade"></div>
        <div id="main_map" style="width: 100%; height: 1140px;"></div>
        <canvas id="draw-canvas" style="position: absolute; left: 0; top: 0; display: none;"></canvas>
    </div>

</section>

<div class="object_page">
    <div class="container">
        <div>
            <div class="row catalog_mini_list map_filter--catalog_mini_list"></div>
        </div>
    </div>
</div>
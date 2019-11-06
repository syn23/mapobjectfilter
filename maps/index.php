<?
//ini_set("memory_limit", "1024M");
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use \Bitrix\Main\Page\Asset;
Asset::getInstance()->addString('<script src="//api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=76aa929a-c9c9-4641-971a-cb1fa1c3a7d1" type="text/javascript"></script>');

Asset::getInstance()->addJs(SITE_TEMPLATE_PATH . '/js/simplify.js');
Asset::getInstance()->addJs("/local/components/innovative/mapObjectFilter/templates/.default/mapFilter.js");
Asset::getInstance()->addJs("/local/components/innovative/mapObjectFilter/templates/.default/mapFilterYandexMap.js");

$APPLICATION->SetTitle("Поиск по карте - Domik.uz");
?>

<? $APPLICATION->IncludeComponent(
    "innovative:mapObjectFilter",
    "",
    Array(
        "CACHE_TIME" => "36000000",
    )
);
?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
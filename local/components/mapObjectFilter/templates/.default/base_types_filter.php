<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>

<button class="map_filter--base_btn map_filter--btn_apartment<?=($_GET['t'] == 'f')?' active':''?>" data-filter="apartment" title="Квартиры">Квартиры</button>
<button class="map_filter--base_btn map_filter--btn_house<?=($_GET['t'] == 'h')?' active':''?>" data-filter="house" title="Дома">Дома</button>
<button class="map_filter--base_btn map_filter--btn_land_plot<?=($_GET['t'] == 'l')?' active':''?>" data-filter="land_plot" title="Участки">Участки</button>
<button class="map_filter--base_btn map_filter--btn_commercial<?=($_GET['t'] == 'c')?' active':''?>" data-filter="commercial" title="Коммерческая недвижимость">Коммерческая недвижимость</button>
<div class="map_filter--expanded_filter">
    <a href="javascript:void(0);" onclick="ga('gtag_UA_148357065_1.send', 'event', 'filter', 'click'); yaCounter53762848.reachGoal('filter'); return true;">Расширенный фильтр</a>
</div>
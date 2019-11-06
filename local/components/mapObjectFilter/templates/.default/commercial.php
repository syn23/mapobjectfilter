<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="map_filter_hidden--wrap">
    <div class="filterBase filterExtra active">
        <?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item item--toggleFilter">
                <a href="#" class="standart_link" onclick="mainfliter.toggleExtraField(this); return false;">Скрыть фильтр</a>
            </div>
        </div>
        */?>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Тип объявления:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach ($arResult['OBJECT_PROP']['DEAL']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="radio"<?=((!$_GET['t'] || $_GET['t'] == 'c') && $_GET['dt'] == $value["ID"])?' checked':''?> name="ref[C][DEAL]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Тип недвижимости:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[C][]" value="">
                        Все
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Офис
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Здание
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Склад
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Торговое помещение
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Производственное помещение
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Производственное территория
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][]" value="0">
                        Готовый бизнес
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Назначение:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[C][OBJECT_USE][]" value="">
                        Все
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['OBJECT_USE']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[C][OBJECT_USE][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>
*/?>
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Площадь:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range" data-postfix=" м2" data-min="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                        <input type="text" data-value="min" name="re[C][AREA][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[C][AREA][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Цена:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range" data-min="<?= $arResult['OBJECT_PROP']['PRICE']['C']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['PRICE']['C']['MAX'] ?>">
                        <input type="text" data-value="min" name="ref[C][PRICE][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['C']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[C][PRICE][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['C']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Коммуникации:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][OBJECT_COMMUNICATIONS][]" value="0">
                        Газ
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][OBJECT_COMMUNICATIONS][]" value="0">
                        Электричество
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][OBJECT_COMMUNICATIONS][]" value="0">
                        Водопровод
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][OBJECT_COMMUNICATIONS][]" value="0">
                        Канализация
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Состояние:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach ($arResult['OBJECT_PROP']['REPAIR']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[C][REPAIR][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Фото:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox"><input type="checkbox" name="ref[C][PHOTO]" value="true">
                    с фото</label>
                </div>
            </div>
        </div>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Регион:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" name="ref[C][REGIONS][]" value="">
                        Не важно
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[C][REGIONS][]" value="<?= $value["UF_XML_ID"] ?>">
                            <?= $value["UF_NAME"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>
*/?>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Ориентир:</a></div>
                <div class="map_filter_hidden--data">
                    <input class="map_filter_hidden--text-input" type="text" name="ref[C][search]" placeholder="город,адрес, метро, район, шоссе или ЖК">
                </div>
            </div>
        </div>
*/?>
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Дополнительно:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach ($arResult['OBJECT_PROP']['ADDITIONALLY']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[C][ADDITIONALLY][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div>
                <div class="map_filter_hidden--item map_filter_hidden--btns">
                    <input type="hidden" name="ref[C][TYPE]" value="3">
                    <button type="submit">Показать</button>
                    <a href="javascript:void(0)" class="reset">Сбросить</a>
                </div>
            </div>
        </div>
    </div>

</div>
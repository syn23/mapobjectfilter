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
                            <input type="radio"<?=((!$_GET['t'] || $_GET['t'] == 'h') && $_GET['dt'] == $value["ID"])?' checked':''?> name="ref[H][DEAL]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Расположение:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[H][OBJECT_LOCATION]" value="">
                        Все
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['OBJECT_LOCATION']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[H][OBJECT_LOCATION][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Кол-во комнат:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value){ ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[H][ROOMS][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Площадь:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range" data-postfix=" м2" data-min="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                        <input type="text" data-value="min" name="ref[H][AREA][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[H][AREA][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Цена:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range" data-min="<?= $arResult['OBJECT_PROP']['PRICE']['H']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['PRICE']['H']['MAX'] ?>">
                        <input type="text" data-value="min" name="ref[H][PRICE][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['H']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[H][PRICE][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['H']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Этажность:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="radio" checked name="ref[H][FLOOR_1][]" value="">
                        Не имеет значения
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[H][FLOOR_1][]" value="1">
                        1
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[H][FLOOR_1][]" value="2">
                        2
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[H][FLOOR_1][]" value="3">
                        3
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Материал здания:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[H][TYPE_OF_HOUSE]" value="">
                        Все
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['TYPE_OF_HOUSE']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[H][TYPE_OF_HOUSE][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Состояние:</a></div>
                <div class="map_filter_hidden--data">
                     <? foreach ($arResult['OBJECT_PROP']['REPAIR']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[H][REPAIR][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Коммуникации:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" name="ref[H][OBJECT_COMMUNICATIONS][]" value="155">
                        Газ
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[H][OBJECT_COMMUNICATIONS][]" value="156">
                        Электричество
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[H][OBJECT_COMMUNICATIONS][]" value="157">
                        Водопровод
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[H][OBJECT_COMMUNICATIONS][]" value="158">
                        Канализация
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Фото:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox"><input type="checkbox" name="ref[H][PHOTO]" value="true">
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
                        <input type="checkbox" name="ref[H][REGIONS]" value="">
                        Не важно
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[H][REGIONS][]" value="<?= $value["UF_XML_ID"] ?>">
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
                    <input class="map_filter_hidden--text-input" type="text" name="ref[H][search]" placeholder="город,адрес, метро, район, шоссе или ЖК">
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
                            <input type="checkbox" name="ref[H][ADDITIONALLY][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div>
                <div class="map_filter_hidden--item map_filter_hidden--btns">
                    <input type="hidden" name="ref[H][TYPE]" value="2">
                    <button type="submit">Показать</button>
                    <a href="javascript:void(0)" class="reset">Сбросить</a>
                </div>
            </div>
        </div>
    </div>

</div>
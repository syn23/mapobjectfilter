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
                            <input type="radio"<?=((!$_GET['t'] || $_GET['t'] == 'f') && $_GET['dt'] == $value["ID"])?' checked':''?> name="ref[A][DEAL]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Тип квартиры:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[A][TYPE_APARTMENT][]" value="">
                        Все
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[A][TYPE_APARTMENT][]" value="149">
                        Новостройки
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[A][TYPE_APARTMENT][]" value="148">
                        Вторичный рынок
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Площадь:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range" data-postfix=" м2"  data-min="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                        <input type="text" data-value="min" name="ref[A][AREA][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[A][AREA][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['AREA']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Цена:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap">
                    <div class="range filter-price" data-min="<?= $arResult['OBJECT_PROP']['PRICE']['A']['MIN'] ?>"
                         data-max="<?= $arResult['OBJECT_PROP']['PRICE']['A']['MAX'] ?>">
                        <input type="text" data-value="min" name="ref[A][PRICE][MIN]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['A']['MIN'] ?>">
                        <input type="text" data-value="max" name="ref[A][PRICE][MAX]"
                               value="<?= $arResult['OBJECT_PROP']['PRICE']['A']['MAX'] ?>">
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Кол-во комнат:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value){ ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[A][ROOMS][]" value="<?=$value["ID"]?>">
                            <?=$value["VALUE"]?>
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
                            <input type="checkbox" name="ref[A][REPAIR][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Материал здания:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[A][TYPE_OF_HOUSE][]" value="">
                        Все
                    </label>
                    <? foreach($arResult['OBJECT_PROP']['TYPE_OF_HOUSE']['VALUE'] as $value){ ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[A][TYPE_OF_HOUSE][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">ЖК:</a></div>
                <div class="map_filter_hidden--data">
                    <div class="select-field">
                        <select name="ref[A][COMPLEX][]" data-title="ЖК" data-searchField multiple>
                            <? foreach ($arResult['OBJECT_PROP']['COMPLEX']['LIST'] as $value) { ?>
                                <option value="<?= $value["ID"] ?>"><?= $value["NAME"] ?></option>
                            <? } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Застройщик:</a></div>
                <div class="map_filter_hidden--data">
                    <div class="select-field">
                        <select name="ref[A][BUILDERS][]" data-title="Застройщик" data-searchField multiple>
                            <? foreach ($arResult['OBJECT_PROP']['Zastroyshiki']['LIST'] as $value) { ?>
                                <option value="<?= $value["ID"] ?>"><?= $value["NAME"] ?></option>
                            <? } ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Продавец:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" data-all name="ref[A][TYPE_SALE][]" value="">
                        Все
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[A][TYPE_SALE][]" value="55">
                        Собственник
                    </label>
                    <label class="checkbox">
                        <input type="checkbox" name="ref[A][TYPE_SALE][]" value="56">
                        Агентство
                    </label>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Этаж:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="radio" name="ref[A][FLOOR]" value="1">
                        1-й
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[A][FLOOR]" value="last">
                        Последний
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[A][FLOOR]" value="!1">
                        Все кроме 1-го
                    </label>
                    <label class="checkbox">
                        <input type="radio" name="ref[A][FLOOR]" value="!last">
                        Все кроме последнего
                    </label>
                </div>
            </div>
        </div>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Этажность:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" checked name="ref[A][FLOOR_1][IS_ALL]" value="IS_ALL">
                        Все
                    </label>
                    <div class="nowrap">
                        <input class="spinner-range" data-spiner-group="Floor_1" data-spiner-type="min" type="text" name="ref[A][FLOOR_1][MIN]" value="1">
                        -
                        <input class="spinner-range" data-spiner-group="Floor_1" data-spiner-type="max" type="text" name="ref[A][FLOOR_1][MAX]" value="9">
                    </div>
                </div>
            </div>
        </div>
*/?>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Этажность:</a></div>
                <div class="map_filter_hidden--data map_filter_hidden--range-wrap map_filter_hidden--floors">
                    <label class="checkbox">
                        <input class="floors-switcher" type="checkbox" checked name="ref[A][FLOOR_1][IS_ALL]" value="IS_ALL">
                        Не важно
                    </label>
                    <div class="map_filter_hidden--floors-inner">
                        <div class="range" data-min="1"
                         data-max="20">
                            <input type="text" data-value="min" name="ref[A][FLOOR_1][MIN]"
                                   value="1">
                            <input type="text" data-value="max" name="ref[A][FLOOR_1][MAX]"
                                   value="20">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Планировка:</a></div>
                <div class="map_filter_hidden--data">
                    <? foreach($arResult['OBJECT_PROP']['APARTMENT_LAYOUT']['VALUE'] as $value){ ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[A][APARTMENT_LAYOUT][]" value="<?=$value["ID"]?>">
                            <?=$value["VALUE"]?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Фото:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox"><input type="checkbox" name="ref[A][PHOTO]" value="true">
                        с фото
                    </label>
                </div>
            </div>
        </div>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Регион:</a></div>
                <div class="map_filter_hidden--data">
                    <label class="checkbox">
                        <input type="checkbox" name="ref[A][REGIONS]" value="">
                        Не важно
                    </label>
                    <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                        <label class="checkbox">
                            <input type="checkbox" name="ref[A][REGIONS][]" value="<?= $value["UF_XML_ID"] ?>">
                            <?= $value["UF_NAME"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>
*/?>
<?/*
        <div class="map_filter_hidden--row">
            <div class="map_filter_hidden--item long_input">
                <div class="map_filter_hidden--title"><a class="collapsed" href="javascript:void(0);">Ориентир:</a></div>
                <div class="map_filter_hidden--data">
                    <input class="map_filter_hidden--text-input" type="text" name="ref[A][search]" placeholder="город,адрес, метро, район, шоссе или ЖК">
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
                            <input type="checkbox" name="ref[A][ADDITIONALLY][]" value="<?= $value["ID"] ?>">
                            <?= $value["VALUE"] ?>
                        </label>
                    <? } ?>
                </div>
            </div>
        </div>

        <div class="map_filter_hidden--row">
            <div>
                <div class="map_filter_hidden--item map_filter_hidden--btns">
                    <input type="hidden" name="ref[A][TYPE]" value="1">
                    <button type="submit">Показать</button>
                    <a href="javascript:void(0)" class="reset">Сбросить</a>
                </div>
            </div>
        </div>
    </div>

</div>
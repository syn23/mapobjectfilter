<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
?>
<div class="wrap-filter">
    <div class="form_row">
        <div class="item">
            <div class="title">Тип объявления:</div>

            <? foreach ($arResult['OBJECT_PROP']['DEAL']['VALUE'] as $value) { ?>
                <label>
                    <input type="radio" name="ref[PROPERTY_DEAL]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Объект:</div>
            <div class="flex-line">
                <? foreach ($arResult['OBJECT_PROP']['TYPE']['VALUE'] as $value) { ?>
                    <label class="checkbox">
                        <input type="radio" name="ref[PROPERTY_TYPE]" value="<?= $value["ID"] ?>">
                        <?= $value["VALUE"] ?>
                    </label>
                <? } ?>
            </div>
        </div>
    </div>
    <div class="form_row ">
        <div class="item">
            <div class="title">Тип недвижимости:</div>
            <div class="flex-col">
                <? foreach ($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value) { ?>
                    <label class="checkbox">
                        <input type="radio" name="ref[PROPERTY_TYPE]" value="<?= $value["ID"] ?>">
                        <?= $value["VALUE"] ?>
                    </label>
                <? } ?>
            </div>
        </div>
        <div class="item">
            <div class="title">Продавец:</div>
            <div class="flex-col">
            <? foreach ($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value) { ?>
                <label class="checkbox">
                    <input type="radio" name="ref[PROPERTY_TYPE]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
            </div>
        </div>
        <div class="item">
            <div class="title">Застройщик</div>
            <select name="ref[PROPERTY_REGIONS]">
                <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                    <option value="<?= $value["UF_XML_ID"] ?>" <?=$value['selectRegion']?'selected':''?>><?= $value["UF_NAME"] ?></option>
                <? } ?>
            </select>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Кол-во комнат:</div>
            <div class="flex-line">
            <? foreach ($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value) { ?>
                <label class="checkbox">
                    <input type="radio" name="ref[PROPERTY_ROOMS]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Этаж:</div>
            <div class="flex-line">
            <? foreach ($arResult['OBJECT_PROP']['ROOMS']['VALUE'] as $value) { ?>
                <label class="checkbox">
                    <input type="radio" name="ref[PROPERTY_ROOMS]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Этажность:</div>
            <div class="flex-line">
            <? foreach ($arResult['OBJECT_PROP']['FLOOR_1']['VALUE'] as $value) { ?>
                <label class="checkbox">
                    <input type="radio" name="ref[PROPERTY_FLOOR_1]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Цена:</div>
            <div class="item item_range">
                <div class="range" data-min="<?=$arResult['OBJECT_PROP']['PRICE']['MIN']?>" data-max="<?=$arResult['OBJECT_PROP']['PRICE']['MAX']?>">
                    <input type="hidden" data-value="min" name="ref[PROPERTY_PRICE][MIN]" value="<?=$arResult['OBJECT_PROP']['PRICE']['MIN']?>">
                    <input type="hidden" data-value="max" name="ref[PROPERTY_PRICE][MAX]" value="<?=$arResult['OBJECT_PROP']['PRICE']['MAX']?>">
                </div>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Площадь:</div>
            <div class="item item_range">
                <div class="range" data-min="<?=$arResult['OBJECT_PROP']['AREA']['MIN']?>" data-max="<?=$arResult['OBJECT_PROP']['AREA']['MAX']?>">
                    <input type="hidden" data-value="min" name="ref[PROPERTY_AREA][MIN]" value="<?=$arResult['OBJECT_PROP']['AREA']['MIN']?>">
                    <input type="hidden" data-value="max" name="ref[PROPERTY_AREA][MAX]" value="<?=$arResult['OBJECT_PROP']['AREA']['MAX']?>">
                </div>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Материал здания:</div>
            <div class="flex-col">
            <? foreach ($arResult['OBJECT_PROP']['TYPE_OF_HOUSE']['VALUE'] as $value) { ?>
                <label class="checkbox">
                    <input type="radio" name="ref[PROPERTY_TYPE_OF_HOUSE]" value="<?= $value["ID"] ?>">
                    <?= $value["VALUE"] ?>
                </label>
            <? } ?>
            </div>
        </div>
        <div class="item flex_check_2-col">
            <div class="title">Состояние:</div>
            <div class="flex_check_2-col">
                <? foreach ($arResult['OBJECT_PROP']['REPAIR']['VALUE'] as $value) { ?>
                    <label class="checkbox">
                        <input type="radio" name="ref[PROPERTY_REPAIR]" value="<?= $value["ID"] ?>">
                        <?= $value["VALUE"] ?>
                    </label>
                <? } ?>
            </div>
        </div>
    </div>
    <div class="form_row">
        <div class="item">
            <label class="checkbox"><input type="checkbox" name="ref[PROPERTY_PHOTO]" value="true"><span></span> с фото</label>
        </div>
        <div class="item">
            <select name="ref[PROPERTY_REGIONS]">
                <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                    <option value="<?= $value["UF_XML_ID"] ?>" <?=$value['selectRegion']?'selected':''?>><?= $value["UF_NAME"] ?></option>
                <? } ?>
            </select>
        </div>
        <div class="item long_input"><input type="text" name="ref[search]" placeholder="город,адрес, метро, район, шоссе или ЖК"></div>
    </div>
    <div class="form_row">
        <div class="item">
            <div class="title">Дополнительно:</div>
            <div class="flex_check_3-col">
                <? foreach ($arResult['OBJECT_PROP']['ADDITIONALLY']['VALUE'] as $value) { ?>
                    <label class="checkbox">
                        <input type="radio" name="ref[PROPERTY_ADDITIONALLY]" value="<?= $value["ID"] ?>">
                        <?= $value["VALUE"] ?>
                    </label>
                <? } ?>
            </div>
        </div>
    </div>





    <div class="form_row">
        <div class="item">
            <?
            $arRegSelect = array();
            if(is_array($filterObject['PROPERTY_REGIONS'])){
                $arRegSelect = $filterObject['PROPERTY_REGIONS'];
            }else{
                $arRegSelect[] = $filterObject['PROPERTY_REGIONS'];
            }?>
            <select name="ref[PROPERTY_REGIONS][]" data-title="Регион" multiple>
                <? foreach ($arResult['OBJECT_PROP']['REGION']['VALUE'] as $value) { ?>
                    <option value="<?= $value["UF_XML_ID"] ?>" <?=in_array($value["UF_XML_ID"],$arRegSelect)?'selected':''?>><?= $value["UF_NAME"] ?></option>
                <? } ?>
            </select>
        </div>
        <div class="item">
            <select name="ref[PROPERTY_METRO][]" data-title="Метро" multiple>
                <? foreach ($arResult['OBJECT_PROP']['METRO']['VALUE'] as $value) { ?>
                    <option value="<?= $value["UF_XML_ID"] ?>" <?=$filterObject['PROPERTY_METRO']==$value["UF_XML_ID"]?'selected':''?>><?= $value["UF_NAME"] ?></option>
                <? } ?>
            </select>
        </div>
        <div class="item item_icons">
            <img usemap="#image-map" src="<?= SITE_TEMPLATE_PATH ?>/img/icons_form.png" alt="">
            <map name="image-map">
                <area target="" alt="" title="" href="javascript:void(0);" onclick="showPopup('metro');" coords="37,26,0,1" shape="rect">
                <area target="" alt="" title="" href="javascript:void(0);" onclick="showPopup('map');" coords="59,0,87,26" shape="rect">
                <area target="" alt="" title="" href="javascript:void(0);" onclick="showPopup('rect');" coords="112,0,140,25" shape="rect">
            </map>
        </div>

        <div class="item fav_item">
            <label class="checkbox" class="fav_label">
                <input type="checkbox" name="ref[FAVORITE]" value="true">
                <span><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24" height="26" viewBox="0 0 24 26"><defs><path id="6o1aa" d="M162.082 1040.002s-19.023-18.586-9.328-24.665c5.592-3.506 9.233.982 9.233.982s3.64-4.488 9.233-.982c9.695 6.079-9.138 24.665-9.138 24.665"/></defs><g><g transform="translate(-150 -1014)"><use xlink:href="#6o1aa"/></g></g></svg></span>
            </label>
        </div>
        <div class="item">
            <button class="btn btn_border">показать объекты</button>
        </div>
    </div>

</div>
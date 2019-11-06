(function (window) {
    'use strict';

    if (window.mapFilter)
        return;

    window.mapFilter = function (arParams, prop) {
        console.log('arParams: ', arParams);
        console.log('prop: ', prop);

        this.params = arParams;
        this.prop = prop == undefined? {} : prop;
        this.$root = undefined;
        this.type = arParams.type;
        this.isAjax = arParams.isAjax;
        this.isExtraField = false;
        this.filterClick = false;

        this.init();
    }

    window.mapFilter.prototype = {
        init: function(){
            this.$root = $('[data-wrap-filter]');
            this.$root.find('label.checkbox').append('<span></span>');
            this.addEventChangeFilter();
            var $this = this;

            this.$root.submit(function (e) {
                e.preventDefault();
                $(this).addClass('ready');
                $this.sendFilter();
                return false;
            });
        },

        sendFilter: function(){
            var $this = this;

            if(objectsMap.polygon){
                objectsMap.map.geoObjects.remove(objectsMap.loadingObjectManager);
                objectsMap.loadMarksForPolygon();
            }else{
                objectsMap.initLoadingObjectManager();
            }

            return true;
        },

        getFilterUrlPart: function(){
            var $this = this,
                ready_forms = $this.$root.filter('.ready');

            if(ready_forms.length){
                return ready_forms.serialize();
            }else{
                return $this.$root.serialize();
            }
        },

        toggleExtraField: function (element) {
            $(element).parents('.map_filter_hidden').removeClass('active');
        },

        send: function (postData, getData, callback) {
            $.ajax({
                method: "POST",
                url: window.location.pathname + '?other_type=true&isAjax=true&' + getData,
                data: postData,
                beforeSend: function () {
                },
                success: function (data) {
                    callback(data);
                }
            });
        },
        changeFilter: function (data) {
            this.$root.html(data);
            this.$root.find('label.checkbox').append('<span></span>');
            this.addEventChangeFilter();
            initPlugin();

        },
        addEventChangeFilter: function () {
            var $this = this;
            var $typeObects;

            // Развернуть строку фильтра
            this.$root.find('.map_filter_hidden--title a').on('click', function(e){
                e.preventDefault();
                var $this = $(this),
                    $target = $this.parent().next(),
                    $parent = $this.parents('.map_filter_hidden');

                if($this.hasClass('collapsed')){
                    $('.map_filter_hidden--data', $parent).removeClass('show');
                    $('.map_filter_hidden--title a', $parent).addClass('collapsed');

                    $this.removeClass('collapsed');
                    $target.addClass('show')//.css('maxHeight', $target[0].scrollHeight + 'px');
                }else{
                    $this.addClass('collapsed');
                    $target.removeClass('show')//.css('maxHeight', '');
                }
            });

            // Убрать Фильтр из активных
            this.$root.find('.reset').on('click', function(e){
                e.preventDefault();
                var target_form = $(this).parents('form');
                target_form.removeClass('ready');
                $this.sendFilter();
            });

            // Развернуть фильтр
            $('.map_filter--expanded_filter a').on('click', function(event){
                var filter = $this.filterClick,
                    $target = $this.$root.filter('[data-type="' + filter + '"]');

                if($target.is('.active')){
                    $target.removeClass('active');
                }else{
                    $this.$root.removeClass('active');
                    $target.addClass('active');
                }
            });

            // При клике на чекбокс "Все" активируем все чекбоксы свойства
            $('input[data-all]').on('click', function(event){
                var $this = $(this);

                if($this.is(':checked')){
                    var $parent = $this.parents('.map_filter_hidden--data');
                    $parent.find('input[type="checkbox"]:not(:checked)').prop('checked', true);
                }
            });

            // Клик на кнопку типа объекта (Квартира, дом, коммерч. неджвижка, участок)
            $('.map_filter--base_btn').on('click', function(e){
                e.preventDefault();

                var filter = $(this).data('filter'),
                    $target = $this.$root.filter('[data-type="' + filter + '"]');

                if($(this).is('.active')){
                    $(this).removeClass('active');
                    $target.removeClass('ready active');

                    if($this.filterClick == filter)
                        $this.filterClick = ($('.map_filter--base_btn.active:first').length)
                                                ? $('.map_filter--base_btn.active:first').data('filter')
                                                : undefined;
                }else{
                    $this.filterClick = filter;
                    $(this).addClass('active');
                    $target.addClass('ready');
                }

                $this.sendFilter();

            });
        }

    } // end window.mapFilter.prototype
})(window);
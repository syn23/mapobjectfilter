(function (window) {
    'use strict';

    if (window.mapFilterYandexMap)
        return;

    window.mapFilterYandexMap = function (arParams) {
        this.objectManager = false;
        this.objectManagerParams = {
            clusterize: true,
            openBalloonOnClick:false,
            clusterGridSize: 128,
            clusterDisableClickZoom: true,
            clusterIconLayout: "default#pieChart",
            clusterNumbers: [100,1000],
            // clusterIconContentLayout: this.myIconContentLayout
        };
        this.loadingObjectManager = false;
        this.loadingObjectManagerParams = {
            // Опции объектов задаются с префиксом geoObject.
            geoObjectOpenBalloonOnClick: false,
            // Включаем кластеризацию.
            clusterize: true,
            // Зададим опции кластерам.
            // Опции кластеров задаются с префиксом cluster.
            clusterHasBalloon: false,
            // splitRequests: false

            clusterOpenBalloonOnClick:false,
            // clusterGridSize: 128,
            clusterGridSize: 128,
            clusterDisableClickZoom: true,
            clusterIconLayout: "default#pieChart",
            clusterNumbers: [100,1000],
            // clusterIconContentLayout: this.myIconContentLayout
        };
        this.remoteObjectManager = false;
        this.remoteObjectManagerParams = {
            // Опции объектов задаются с префиксом geoObject.
            // geoObjectOpenBalloonOnClick: false,
            // Включаем кластеризацию.
            // clusterize: true,
            // Зададим опции кластерам.
            // Опции кластеров задаются с префиксом cluster.
            clusterHasBalloon: false,
            clusterHasHint: false,
            // splitRequests: false

            // clusterOpenBalloonOnClick:false,
            // clusterGridSize: 128,
            // clusterGridSize: 128,
            // clusterDisableClickZoom: true,
            clusterIconLayout: "default#pieChart",
            // clusterNumbers: [100,1000],
            // clusterIconContentLayout: this.myIconContentLayout
        };
        this.objectsManagerUrl = location.pathname; // шаблон пути для LoadingObjectManager
        this.map = false;
        this.panel = false;
        this.selectObjectID = [];
        this.property = arParams;
        this.polygon = false;
        this.polygonCoords = false;
        this.polygonOptions = {
            strokeColor: '#0000ff',
            fillColor: '#8080ff',
            interactivityModel: 'default#transparent',
            strokeWidth: 4,
            opacity: 0.7,
            zIndex: 2410
        };
        this.canvasOptions = {
            strokeStyle: '#0000ff',
            lineWidth: 4,
            opacity: 0.7
        };
        this.simplify= {
            tolerance: 5,
            highQuality: true
        };
        this.colors = {
            1: "#542703",
            3: "#083163",
            2: "#2d6628",
            4: "#f7ba0f"
        };

        this.init();
    };

    window.mapFilterYandexMap.prototype = {

        init: function () {
            var $this = this;
            ymaps.ready().then(function () {
                $this.initMap();
            });
        },

        initMap: function () {
            var $this = this;

            var rulerControl = new ymaps.control.RulerControl({
                options: {
                    float: 'none',
                    position: {
                        top: '10px',
                        right: '10px'
                    }
                }
            });

            var zoomControl = new ymaps.control.ZoomControl({
                options: {
                    float: 'none',
                    size: 'small',
                    position: {
                        top: '85px',
                        right: '10px'
                    }
                }
            });

            var geolocationControl = new ymaps.control.GeolocationControl({
                options: {
                    float: 'none',
                    position: {
                        top: '185px',
                        right: '10px'
                    }
                }
            });

            var myMap = this.map = new ymaps.Map('main_map', {
                    center: [41.32239192286888,69.23665438106981],
                    // center: [39.66070848000000,66.95560996000000], // Самарканд
                    zoom: 13,
                    controls: [rulerControl, zoomControl, geolocationControl]
                }
            );

            myMap.behaviors.disable('scrollZoom');

            var isMobile = {
                Android: function() {
                    return navigator.userAgent.match(/Android/i);
                },
                BlackBerry: function() {
                    return navigator.userAgent.match(/BlackBerry/i);
                },
                iOS: function() {
                    return navigator.userAgent.match(/iPhone|iPad|iPod/i);
                },
                Opera: function() {
                    return navigator.userAgent.match(/Opera Mini/i);
                },
                Windows: function() {
                    return navigator.userAgent.match(/IEMobile/i);
                },
                any: function() {
                    return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
                }
            };

            if(isMobile.any()){
                //myMap.behaviors.disable('drag');
            }

            // Включаем зум при клике
            myMap.events.add('click', function (e) {
                // myMap.behaviors.enable('scrollZoom');
            });

            var drawButtonLayout = ymaps.templateLayoutFactory.createClass([
                '<div class="drawButton{% if state.selected %} drawButtonSelected{% endif %}" ',
                'title="{{ data.title }}">',
                '{% if !state.selected %}' +
                    '{{data.content}}' +
                '{% else %}',
                    '{{data.contentSelected}}' +
                '{% endif %}',
                '</div>'
            ].join(''));

            var drawButton = new ymaps.control.Button({
                data: {
                    content: "Нарисовать область",
                    contentSelected: "Удалить область",
                    title: "Показать объекты в указанной области",
                },
                options: {
                    layout: drawButtonLayout,
                    float: 'none',
                    position: {
                        top: '10px',
                        right: '50px'
                    }
                }
            });

            myMap.controls.add(drawButton);

            function drawLineOverMap(map) {
                var canvas = document.querySelector('#draw-canvas');
                var ctx2d = canvas.getContext('2d');
                var drawing = false;
                var coordinates = [];

                // Задаем размеры канвасу как у карты.
                var rect = map.container.getParentElement().getBoundingClientRect();
                canvas.style.width = rect.width + 'px';
                canvas.style.height = rect.height + 'px';
                canvas.width = rect.width;
                canvas.height = rect.height;

                // Применяем стили.
                ctx2d.strokeStyle = $this.canvasOptions.strokeStyle;
                ctx2d.lineWidth = $this.canvasOptions.lineWidth;
                canvas.style.opacity = $this.canvasOptions.opacity;

                ctx2d.clearRect(0, 0, canvas.width, canvas.height);

                // Показываем канвас. Он будет сверху карты из-за position: absolute.
                canvas.style.display = 'block';

                canvas.onmousedown = function (e) {
                    // При нажатии мыши запоминаем, что мы начали рисовать и координаты.
                    drawing = true;
                    coordinates.push({x: e.offsetX, y: e.offsetY});
                };

                canvas.onmousemove = function (e) {
                    // При движении мыши запоминаем координаты и рисуем линию.
                    if (drawing) {
                        var last = coordinates[coordinates.length - 1];
                        ctx2d.beginPath();
                        ctx2d.moveTo(last.x, last.y);
                        ctx2d.lineTo(e.offsetX, e.offsetY);
                        ctx2d.stroke();

                        coordinates.push({x: e.offsetX, y: e.offsetY});
                    }
                };

                return new Promise(function (resolve) {
                    // При отпускании мыши запоминаем координаты и скрываем канвас.
                    canvas.onmouseup = function (e) {
                        coordinates.push({x: e.offsetX, y: e.offsetY});
                        canvas.style.display = 'none';
                        drawing = false;

                        // Уменьшаем кол-во точек для построения полигона
                        coordinates = simplify(coordinates, $this.simplify.tolerance, $this.simplify.highQuality);

                        coordinates = coordinates.map(function (x) {
                            return [x.x / canvas.width, x.y / canvas.height];
                        });

                        resolve(coordinates);
                    };
                });
            }

            drawButton.events.add('click', function (e) {
                if(drawButton.isSelected()){
                    // Удаляем старый полигон.
                    if ($this.polygon) {
                        $this.map.geoObjects.remove($this.polygon);
                        $this.polygon = false;
                        $this.polygonCoords = false;
                    }

                    $this.map.geoObjects.remove($this.objectManager);
                    $this.initLoadingObjectManager();
                }else{
                    drawLineOverMap($this.map).then(function(coordinates) {
                        // Переводим координаты из 0..1 в географические.
                        var bounds = $this.map.getBounds();
                        $this.polygonCoords = coordinates.map(function(x) {
                            return [
                                // Широта (latitude).
                                // Y переворачивается, т.к. на canvas'е он направлен вниз.
                                bounds[0][0] + (1 - x[1]) * (bounds[1][0] - bounds[0][0]),
                                // Долгота (longitude).
                                bounds[0][1] + x[0] * (bounds[1][1] - bounds[0][1]),
                            ];
                        });

                        // Создаем новый полигон
                        $this.polygon = new ymaps.Polygon([$this.polygonCoords], {}, $this.polygonOptions);
                        $this.map.geoObjects.add($this.polygon);

                        $this.loadMarksForPolygon();
                    });
                }
            });

            // $this.initLoadingObjectManager();
            $this.initRemoteObjectManager();
        },

        loadMarksForPolygon: function(){
            var $this = this;
            // Загружаем метки для выделенной области

            var $forms = mapfilterHandler.$root.filter('.ready'),
                data = ($forms.length) ? $forms.serialize() : mapfilterHandler.$root.serialize();

            var post_coordinates = $this.polygonCoords.map(function(point){
                return point.join(' ');
            });

            data += '&polygon=' + post_coordinates.join(',');

            $.ajax({
                method: "POST",
                url: location.pathname,
                data: data + '&isAjax=1&getMarksInPolygon=1',
                dataType: "json",
                beforeSend: function () {
                    $('.map_filter--loader-wrap').show();
                },
                success: function (data) {
                    if(data.geo !== undefined){
                        $this.map.geoObjects.remove($this.objectManager);
                        $this.map.geoObjects.remove($this.loadingObjectManager);

                        if(data.geo.features.length){
                            $this.initObjectManager(data.geo);
                        }
                    }
                },
                complete: function () {
                    $('.map_filter--loader-wrap').hide();
                },
            });
        },

        initObjectManager: function(geo){
            var $this = this;

            $this.objectManager = new ymaps.ObjectManager($this.objectManagerParams);
            $this.objectManager.add(geo);

            // Событие клика на кластер или метку
            $this.objectManager.objects.events.add('click', function (e) {
                $this.selectObjectID.push($this.objectManager.objects.getById(e.get('objectId')).id);
                $this.showCatalogList();
            });
            $this.objectManager.clusters.events.add('click', function (e) {
                var objects = $this.objectManager.clusters.getById(e.get('objectId')).properties.geoObjects;
                $this.selectObjectID = objects.map(function (el) {
                    return el.id
                });
                $this.showCatalogList();
            });

            $this.map.geoObjects.add($this.objectManager);
        },

        initLoadingObjectManager: function(){
            var $this = this;
            var current_filter = mapfilterHandler.getFilterUrlPart();

            if($this.loadingObjectManager){
                // Удаляем старый менеджер объектов (loadingObjectManager)
                $this.map.geoObjects.remove($this.loadingObjectManager);
            }

            $this.loadingObjectManager = new ymaps.LoadingObjectManager(
                $this.objectsManagerUrl + '?' + current_filter + '&z=%z&bbox=%b',
                $this.loadingObjectManagerParams
            );

            $this.loadingObjectManager.clusters.events.add('click', function (e) {
                var objects = $this.loadingObjectManager.clusters.getById(e.get('objectId')).properties.geoObjects;
                $this.selectObjectID = objects.map(function (el) {
                    return el.id
                });
                $this.showCatalogList();
            });

            $this.loadingObjectManager.objects.events.add('click', function (e) {
                $this.selectObjectID = [$this.loadingObjectManager.objects.getById(e.get('objectId')).id];
                $this.showCatalogList();
            });

            // Чтобы менеджер начал загружать объекты с сервера, необходимо добавить менеджер на карту.
            $this.map.geoObjects.add($this.loadingObjectManager);

            // Событие окончания загрузки loadingObjectManager
            var onDataLoadOrig = $this.loadingObjectManager._dataLoadController.onDataLoad;
            $this.loadingObjectManager._dataLoadController.onDataLoad = function(e, t) {
                onDataLoadOrig.call($this.loadingObjectManager._dataLoadController, e, t);
                console.log('end loadingObjMan');
            };
        },

        initRemoteObjectManager: function(){
            var $this = this;
            var current_filter = mapfilterHandler.getFilterUrlPart();

            if($this.remoteObjectManager){
                // Удаляем старый менеджер объектов (loadingObjectManager)
                $this.map.geoObjects.remove($this.remoteObjectManager);
            }

            $this.remoteObjectManager = new ymaps.RemoteObjectManager(
                $this.objectsManagerUrl + '?' + current_filter + '&z=%z&bbox=%b',
                $this.remoteObjectManagerParams
            );

            $this.remoteObjectManager.clusters.events.add('click', function (e) {
                var objects = $this.remoteObjectManager.clusters.getById(e.get('objectId')).properties.geoObjects;
                $this.selectObjectID = objects.map(function (el) {
                    return el.id
                });
                $this.showCatalogList();
            });

            $this.remoteObjectManager.objects.events.add('click', function (e) {
                $this.selectObjectID = [$this.remoteObjectManager.objects.getById(e.get('objectId')).id];
                $this.showCatalogList();
            });

            // Чтобы менеджер начал загружать объекты с сервера, необходимо добавить менеджер на карту.
            $this.map.geoObjects.add($this.remoteObjectManager);

            // Событие окончания загрузки loadingObjectManager
            var onDataLoadOrig = $this.remoteObjectManager._dataLoadController.onDataLoad;
            $this.remoteObjectManager._dataLoadController.onDataLoad = function(e, t) {
                onDataLoadOrig.call($this.remoteObjectManager._dataLoadController, e, t);
                console.log('end loadingObjMan');
            };
        },

        // Удаляет менеджер объектов яндекс карты и инициализирует новый,
        // в URL менеджера добавляются GET-параметры из фильтра
        reloadObjectManager: function(){
            var $this = this,
                url = $this.objectsManagerUrl + '?' + mapfilterHandler.getFilterUrlPart() + '&z=%z&bbox=%b';

            // Удаляем старый менеджер объектов (loadingObjectManager)
            $this.map.geoObjects.remove($this.loadingObjectManager);

            console.log('start reload');
            $this.loadingObjectManager = new ymaps.LoadingObjectManager(
                url,
                $this.loadingObjectManagerParams
            );
            // console.log(loadingObjectManager);

            // Чтобы менеджер начал загружать объекты с сервера, необходимо добавить менеджер на карту.
            $this.map.geoObjects.add($this.loadingObjectManager);

            // Задаем новый URL для loadingObjectManager и перезагружаем данные
            // objectsMap.loadingObjectManager.setUrlTemplate(url);
            // objectsMap.loadingObjectManager.reloadData();
        },

        showCatalogList: function () {
            var $this = this;
            $.ajax({
                method: "POST",
                url: "/handler/catalogList.php",
                data: { ID: $this.selectObjectID },
                success: function (data) {
                    // console.log(data);
                    var html = $(data).find('.catalog_mini_list').html();
                    $('.map_filter--catalog_mini_list').html(html);

                    var $target = $('.map_filter--catalog_mini_list');
                    $('html, body').animate({
                            scrollTop: $target.offset().top
                        },
                        1300
                    );
                }
            });
        },
    }
})(window);
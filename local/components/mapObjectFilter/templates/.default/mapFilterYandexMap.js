(function (window) {
    'use strict';

    if (window.mapFilterYandexMap)
        return;

    window.mapFilterYandexMap = function (regions) {
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
            //clusterIconLayout: "default#pieChart",
            clusterNumbers: [100,1000],
            // clusterIconContentLayout: this.myIconContentLayout
        };
        this.loadingObjectManagerUrl = location.pathname; // шаблон пути для LoadingObjectManager
        this.map = false;
        this.panel = false;
        this.selectObjectID = [];
        this.regions = regions || {};
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
        this.disableDrag = false;

        // this.init();
    };

    window.mapFilterYandexMap.prototype = {

        init: function () {
            var $this = this;
            ymaps.ready().then(function () {
                $this.initMap();
                $this.map_loader = $('.map_filter--loader-wrap');
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

            var searchControl = new ymaps.control.SearchControl({
                options: {
                    float: 'none',
                    position: {
                        top: '10px',
                        left: '260px'
                    },
                    // Будет производиться поиск по топонимам и организациям.
                    provider: 'yandex#search'
               }
            });

            var myMap = this.map = new ymaps.Map('main_map', {
                    center: $this.getMapCenter(),
                    zoom: 13,
                    controls: [rulerControl, zoomControl, geolocationControl, searchControl]
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
                myMap.behaviors.disable('drag');
                myMap.events.add('dblclick', function (e) {
                    e.preventDefault(); // При двойном щелчке зума не будет.
                    if($this.disableDrag){
                        myMap.behaviors.disable('drag');
                    }else{
                        myMap.behaviors.enable('drag');
                    }

                    $this.disableDrag = !$this.disableDrag;
                });
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

            setTimeout(function(){
                $this.initLoadingObjectManager();
            }, 1000);
        },

        getMapCenter: function(){
            let $this = this;
            let coords = [41.32239192286888,69.23665438106981];
            let r = Cookies.get('uregion') || 28;

            if(r in $this.regions && Array.isArray($this.regions[r]) && $this.regions[r].length === 2){
                coords = $this.regions[r].map(function (val) {
                    return parseFloat(val)
                });
            }

            return coords;
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

            $this.map_loader.show();

            if($this.loadingObjectManager){
                // Удаляем старый менеджер объектов (loadingObjectManager)
                $this.map.geoObjects.remove($this.loadingObjectManager);
            }

            $this.loadingObjectManager = new ymaps.LoadingObjectManager(
                $this.loadingObjectManagerUrl + '?' + current_filter + '&z=%z&bbox=%b',
                $this.loadingObjectManagerParams
            );

            $this.loadingObjectManager.clusters.events.add('click', function (e) {
                $this.map_loader.show();
                var objects = $this.loadingObjectManager.clusters.getById(e.get('objectId')).properties.geoObjects;
                $this.selectObjectID = objects.map(function (el) {
                    return el.id
                });
                $this.showCatalogList();
            });

            $this.loadingObjectManager.objects.events.add('click', function (e) {
                $this.map_loader.show();
                $this.selectObjectID = [$this.loadingObjectManager.objects.getById(e.get('objectId')).id];
                $this.showCatalogList();
            });

            // Чтобы менеджер начал загружать объекты с сервера, необходимо добавить менеджер на карту.
            $this.map.geoObjects.add($this.loadingObjectManager);

            // Событие окончания загрузки loadingObjectManager
            var onDataLoadOrig = $this.loadingObjectManager._dataLoadController.onDataLoad;
            $this.loadingObjectManager._dataLoadController.onDataLoad = function(e, t) {
                onDataLoadOrig.call($this.loadingObjectManager._dataLoadController, e, t);
                $this.map_loader.hide();
                console.log('loading end');
            };
        },

        // Удаляет менеджер объектов яндекс карты и инициализирует новый,
        // в URL менеджера добавляются GET-параметры из фильтра
        reloadObjectManager: function(){
            var $this = this,
                url = $this.loadingObjectManagerUrl + '?' + mapfilterHandler.getFilterUrlPart() + '&z=%z&bbox=%b';

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
                    initApeal();
                    var $target_pos = $('.map_filter--catalog_mini_list').offset().top;
                    document.addEventListener("scroll", returnFalse);
                    setTimeout(function(){
                        $('html, body').animate({
                                scrollTop: $target_pos
                            },
                            30
                        );
                        $this.map_loader.hide();
                        document.removeEventListener("scroll", returnFalse);
                    },500);
                }
            });
        },
    }
})(window);

function returnFalse(e){
     e = e||event;
     e.preventDefault ? e.preventDefault() : (e.returnValue = false);
}
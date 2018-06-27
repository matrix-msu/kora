var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Index = function() {

    $('.single-select').chosen({
        width: '100%',
    });

    $('.multi-select').chosen({
        width: '100%',
    });

    function initializeSelectAddition() {
        $('.chosen-search-input').on('keyup', function(e) {
            var container = $(this).parents('.chosen-container').first();

            if (e.which === 13 && container.find('li.no-results').length > 0) {
                var option = $("<option>").val(this.value).text(this.value);

                var select = container.siblings('.modify-select').first();

                select.append(option);
                select.find(option).prop('selected', true);
                select.trigger("chosen:updated");
            }
        });
    }

    function initializeOptionDropdowns() {
        $('.option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            var type = $(this).attr('id');
            if(type === 'page-count-dropdown') {
                var order = getURLParameter('order');
                window.location = window.location.pathname + "?page-count=" + $(this).val() + (order ? "&order=" + order : '');
            } else if (type === 'order-dropdown') {
                var pageCount = getURLParameter('page-count');
                window.location = window.location.pathname + "?order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
            }
        });

        $('.results-option-dropdown-js').chosen({
            disable_search_threshold: 10,
            width: 'auto'
        }).change(function() {
            var type = $(this).attr('id');

            var subBaseUrl = "";
            var keywords = $('.keywords-get-js').val();
            subBaseUrl += "?keywords=" + encodeURIComponent(keywords);
            var method = $('.method-get-js').val();
            subBaseUrl += "&method=" + method;
            if($('.forms-get-js').length) {
                var forms = $('.forms-get-js').val();
                for(var f=0;f<forms.length;f++) {
                    subBaseUrl += "&forms%5B%5D=" + forms[f];
                }
            }
            if($('.projects-get-js').length) {
                var projs = $('.projects-get-js').val();
                for(var p=0;p<projs.length;p++) {
                    subBaseUrl += "&projects%5B%5D=" + projs[p];
                }
            }

            if(type === 'page-count-dropdown') {
                var order = getURLParameter('order');
                window.location = window.location.pathname + subBaseUrl + "&page-count=" + $(this).val() + (order ? "&order=" + order : '');
            } else if (type === 'order-dropdown') {
                var pageCount = getURLParameter('page-count');
                window.location = window.location.pathname + subBaseUrl + "&order=" + $(this).val() + (pageCount ? "&page-count=" + pageCount : '');
            }
        });
    }

    function initializePaginationShortcut() {
        $('.page-link.active').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var maxInput = $this.siblings().last().text()
            $this.html('<input class="page-input" type="number" min="1" max="'+ maxInput +'">');
            var $input = $('.page-input');
            $input.focus();
            $input.on('blur keydown', function(e) {
                if (e.key !== "Enter" && e.key !== "Tab") return;
                if ($input[0].checkValidity()) {
                    var url = window.location.toString();
                    if (url.includes('page=')) {
                        window.location = url.replace(/page=\d*/, "page="+$input.val());
                    } else {
                        var queryVar = url.includes('?') ? '&' : '?';
                        window.location = url + queryVar + "page=" + $input.val();
                    }
                }
            });
        })
    }

    function initializeSearchInteractions() {
        $('.close-advanced-js').hide();

        $('.submit-search-js').click(function(e) {
            e.preventDefault();

            keyVal = $('.keywords-get-js');
            formVal = $('.forms-get-js');

            if(formVal.length && formVal.val()==null) {
                formVal.siblings('.error-message').text('Select something to search through');
            } else {
                $('.keyword-search-js').submit();
            }
        });

        $('.keywords-get-js').on('keyup', function(e) {
            if (e.which === 13) {
                keyVal = $('.keywords-get-js');
                formVal = $('.forms-get-js');

                if(formVal.length && formVal.val()==null) {
                    formVal.siblings('.error-message').text('Select something to search through');
                } else {
                    $('.keyword-search-js').submit();
                }
            }
        });

        $('.open-advanced-js').click(function(e) {
            e.preventDefault();

            $('.advanced-search-drawer-js').effect('slide', {
                direction: 'up',
                mode: 'show',
                duration: 240
            });
            $('.close-advanced-js').show();
            $('.open-advanced-js').hide();
        });

        $('.close-advanced-js').click(function(e) {
            e.preventDefault();

            $('.advanced-search-drawer-js').effect('slide', {
                direction: 'up',
                mode: 'hide',
                duration: 240
            });
            $('.open-advanced-js').show();
            $('.close-advanced-js').hide();
        });
    }

    function initializeToggle() {
        // Initialize card toggling
        $('.record-toggle-js').click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var $header = $this.parent().parent();
            var $token = $header.parent();
            var $content = $header.next();

            $this.children('.icon').toggleClass('active');
            $token.toggleClass('active');
            if ($token.hasClass('active')) {
                $header.addClass('active');
                $token.animate({
                    height: $token.height() + $content.outerHeight(true) + 'px'
                }, 230);
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $token.animate({
                    height: '58px'
                }, 230, function() {
                    $header.hasClass('active') ? $header.removeClass('active') : null;
                    $content.hasClass('active') ? $content.removeClass('active') : null;
                });
                $content.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }

        });

        $('.expand-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card:not(.active) .record-toggle-js').click();
        });

        $('.collapse-fields-js').on('click', function(e) {
            e.preventDefault();
            $('.card.active .record-toggle-js').click();
        });
    }

    function initializeDeleteRecord() {
        Kora.Modal.initialize();

        $('.delete-record-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.delete-record-modal-js');

            var url = deleteRecordURL+'/'+$(this).attr('rid');
            $('.delete-record-form-js').attr('action', url);

            Kora.Modal.open($modal);
        });
    }

    function initializeTypedFieldDisplays() {
        //GALLERY
        $('.gallery-field-display').slick({
            dots: true,
            infinite: true,
            speed: 500,
            fade: true,
            cssEase: 'linear'
        });

        //GEOLOCATOR
        $('.geolocator-map-js').each(function() {
            var mapID = $(this).attr('map-id');

            var firstLoc = $(this).children('.geolocator-location-js').first();
            var mapRecord = L.map('map'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
            mapRecord.scrollWheelZoom.disable();
            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecord);

            $(this).children('.geolocator-location-js').each(function() {
                var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(mapRecord);
                marker.bindPopup($(this).attr('loc-desc'));
            });
        });

        //PLAYLIST
        $('.jp-audio-js').each(function() {
            var audioID = $(this).attr('audio-id');
            var audioLink = $(this).attr('audio-link');
            var swfpath = $(this).attr('swf-path');

            var cssSelector = {
                jPlayer: "#jquery_jplayer_"+audioID,
                cssSelectorAncestor: "#jp_container_"+audioID
            };
            var playlist = [];
            $(this).children('.jp-audio-file-js').each(function() {
                var audioName = $(this).attr('audio-name');
                var audioType = $(this).attr('audio-type');

                if(audioType=="audio/mpeg")
                    var audioVal = {title: audioName, mp3: audioLink+audioName};
                else if(audioType=="audio/ogg")
                    var audioVal = {title: audioName, oga: audioLink+audioName};
                else if(audioType=="audio/x-wav")
                    var audioVal = {title: audioName, wav: audioLink+audioName};

                playlist.push(audioVal);
            });
            var options = {
                swfPath: swfpath,
                supplied: "mp3, oga, wav"
            };
            var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);
        });

        //SCHEDULE
        $('.schedule-cal-js').each(function() {
            var eve = [];
            //Get the date where the calendar should focus
            var receivedDefault = false;
            var defDate = '';
            $(this).children('.schedule-event-js').each(function() {
                var eventTitle = $(this).attr('event-title');
                var eventStart = $(this).attr('event-start');
                if(!receivedDefault) {
                    receivedDefault = true;
                    defDate = eventStart;
                }
                var eventEnd = $(this).attr('event-end');
                var eventAllDay = $(this).attr('event-all-day');

                eve.push({title:eventTitle,start:eventStart,end:eventEnd,allDay:eventAllDay});
            });

            jQuery('.schedule-cal-js').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                events: eve,
                defaultDate: defDate
            });
        });

        //VIDEO
        $('.jp-video-js').each(function() {
            var videoID = $(this).attr('video-id');
            var videoLink = $(this).attr('video-link');
            var swfpath = $(this).attr('swf-path');

            var cssSelector = {
                jPlayer: "#jquery_jplayer_"+videoID,
                cssSelectorAncestor: "#jp_container_"+videoID
            };
            var playlist = [];
            $(this).children('.jp-video-file-js').each(function() {
                var videoName = $(this).attr('video-name');
                var videoType = $(this).attr('video-type');

                if(videoType=="video/mp4")
                    var videoVal = {title: videoName, m4v: videoLink+videoName};
                else if(videoType=="video/ogg")
                    var videoVal = {title: videoName, ogv: videoLink+videoName};

                playlist.push(videoVal);
            });
            var options = {
                swfPath: swfpath,
                supplied: "m4v, ogv"
            };
            var myPlaylist = new jPlayerPlaylist(cssSelector, playlist, options);
        });

        //3D-MODEL
        $('.model-player-div-js').each(function() {
            var modelID = $(this).attr('model-id');
            var modelLink = $(this).attr('model-link');

            var modelColor = $(this).attr('model-color');
            var bg1Color = $(this).attr('bg1-color');
            var bg2Color = $(this).attr('bg2-color');

            var viewer = new JSC3D.Viewer(document.getElementById('cv'+modelID));
            viewer.setParameter('SceneUrl', modelLink);
            viewer.setParameter('InitRotationX', 0);
            viewer.setParameter('InitRotationY', 0);
            viewer.setParameter('InitRotationZ', 0);
            viewer.setParameter('ModelColor', modelColor);
            viewer.setParameter('BackgroundColor1', bg1Color);
            viewer.setParameter('BackgroundColor2', bg2Color);
            viewer.setParameter('RenderMode', 'texturesmooth');
            viewer.setParameter('MipMapping', 'on');
            viewer.setParameter('Renderer', 'webgl');
            viewer.init();
            viewer.update();

            var canvas = document.getElementById('cvfs'+modelID);

            //TODO:: We need to rebuild this?
            // function fullscreen() {
            //     var el = document.getElementById('cv'+modelID);
            //
            //     el.width  = window.innerWidth;
            //     el.height = window.innerHeight;
            //
            //     if(el.webkitRequestFullScreen)
            //         el.webkitRequestFullScreen();
            //     else
            //         el.mozRequestFullScreen();
            // }
            //
            // function exitFullscreen() {
            //     if(!document.fullscreenElement && !document.webkitIsFullScreen && !document.mozFullScreen && !document.msFullscreenElement) {
            //         var el = document.getElementById('cv'+modelID);
            //
            //         el.width  = 750;
            //         el.height = 400;
            //     }
            // }
            //
            // canvas.addEventListener("click",fullscreen);
            // document.addEventListener('fullscreenchange', exitFullscreen);
            // document.addEventListener('webkitfullscreenchange', exitFullscreen);
            // document.addEventListener('mozfullscreenchange', exitFullscreen);
            // document.addEventListener('MSFullscreenChange', exitFullscreen);
        });
    }

    function initializeScrollTo() {
        if($('.scroll-to-here-js').length) {
            $('html, body').animate({
                scrollTop: $(".scroll-to-here-js").offset().top
            }, 1000);
        }
    }

    function initializeSearchValidation() {
        $('.forms-get-js').on('chosen:hiding_dropdown', function(e) {
            value = $(this).val();

            if(value==null) {
                $(this).siblings('.error-message').text('Select something to search through');
            } else {
                $(this).siblings('.error-message').text('');
            }
        });
    }

    initializeSelectAddition();
    initializeOptionDropdowns();
    initializePaginationShortcut();
    initializeSearchInteractions();
    initializeToggle();
    initializeDeleteRecord();
    initializeTypedFieldDisplays();
    initializeScrollTo();
    initializeSearchValidation();
    Kora.Records.Modal();
}

var Kora = Kora || {};
Kora.Records = Kora.Records || {};

Kora.Records.Show = function() {

    // $('.single-select').chosen({
    //     width: '100%',
    // });
    //
    // $('.multi-select').chosen({
    //     width: '100%',
    // });

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
    }

    function initializeDeleteRecord() {
        $('.delete-record-js').click(function (e) {
            e.preventDefault();

            var $modal = $('.delete-record-modal-js');

            Kora.Modal.open($modal);
        });
    }

    function initializeAssociatorCardToggle () {
        $('.assoc-card-toggle-js').click(function (e) {
            e.preventDefault();

            let $card = $(this).parent().parent().parent();
            let $cardBody = $card.find('.body');

            $(this).children().toggleClass('active');

            if ($(this).children().hasClass('active')) {
                //$card.css('height', '');
                $card.animate({
                    height: $card.height() + $cardBody.outerHeight() + 'px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'show',
                    duration: 240
                });
            } else {
                $card.animate({
                    height: '49px'
                }, 230);
                $cardBody.effect('slide', {
                    direction: 'up',
                    mode: 'hide',
                    duration: 240
                });
            }
        });
    }

    function initializeTypedFieldDisplays() {
        //GALLERY
        $('.gallery-field-display-js').each(function() {
            var $this = $(this);
            var $slides = $this.find('.slide-js');
            var $dotsContainer = $this.next().find('.dots-js');
            var slideCount = $slides.length;
            var currentSlide = 0;
            var galHeight = 300, galWidth = 500, galAspectRatio = galWidth / galHeight;

            // Set dots
            for (var i = 0; i < slideCount; i++) {
                $dotsContainer.append('<div class="dot dot-js'+(i == currentSlide ? ' active' : '')+'" data-slide-num="'+i+'"></div>')
            }

            var $dots = $dotsContainer.find('.dot-js');

            // Select slide using dots
            $dots.click(function() {
                var $dot = $(this);
                currentSlide = $dot.data('slide-num');

                $dots.removeClass('active');
                $dot.addClass('active');

                setImagePositions();
            });

            // Need to wait for images to load before getting heights and widths
            $(window).load(function() {
                // Size and Position slides based on gallery aspect ratio
                setGalAspectRatio();

                for (var i = 0; i < slideCount; i++) {
                    var $slide = $($slides[i]);
                    var $slideImg = $slide.find('.slide-img-js');
                    var slideImgHeight = $slideImg.height();
                    var slideImgWidth = $slideImg.width();
                    var imgAspectRatio = slideImgWidth / slideImgHeight;

                    // Set fixed img aspect ratio
                    $slideImg.attr('data-aspect-ratio', imgAspectRatio);

                    setImagePosition($slide, i);
                    setImageSize($slideImg, imgAspectRatio);
                }

                // When resizing, recalculate gallery aspect ratio and size and position slides
                $(window).resize(function () {
                    setGalAspectRatio();

                    for (var i = 0; i < slideCount; i++) {
                        var $slide = $($slides[i]);
                        var $slideImg = $slide.find('.slide-img-js');
                        var imgAspectRatio = $slideImg.data('aspect-ratio');

                        // Set image position
                        $slide.css('left', ((i - currentSlide) * galWidth) + "px");

                        setImageSize($slideImg, imgAspectRatio);
                    }
                });

                // Next button
                $this.parent().find('.next-button-js').click(function () {
                    currentSlide += 1;
                    if (currentSlide >= slideCount) {
                        currentSlide = 0;
                    }

                    setImagePositions();
                });

                // Previous button
                $this.parent().find('.prev-button-js').click(function () {
                    currentSlide -= 1;
                    if (currentSlide < 0) {
                        currentSlide = slideCount - 1;
                    }

                    setImagePositions();
                });

                function setGalAspectRatio() {
                    galHeight = $this.height();
                    galWidth = $this.width();
                    galAspectRatio = galWidth / galHeight;
                }

                function setImageSize($slideImg, imgAspectRatio) {
                    if (imgAspectRatio > galAspectRatio) {
                        // Image is wider than gallery container
                        $slideImg.css('height', 'auto');
                        $slideImg.css('width', '100%');
                    } else {
                        // Image is tall or same aspect ratio as gallery container
                        $slideImg.css('height', '100%');
                        $slideImg.css('width', 'auto');
                    }
                }
            });

            $('.gallery-sidebar-js .full-screen-button-js').click(function(e) {
                e.preventDefault();
                var $galleryModal = $(this).parent().parent().parent().next();
                Kora.Modal.close();
                Kora.Modal.open($galleryModal);
            });

            // Set horizontal positioning for single slide
            function setImagePosition($slide, index) {
                $dots.removeClass('active');
                $($dots[currentSlide]).addClass('active');
                var pos = ((index - currentSlide) * galWidth) + "px";
                $slide.animate({left: pos}, 100, 'swing');
            }

            // Set horizontal positioning for all slides
            function setImagePositions() {
                for (var i = 0; i < slideCount; i++) {
                    var $slide = $($slides[i]);
                    setImagePosition($slide, i);
                }
            }
        });

        //GEOLOCATOR
        // $('.geolocator-map-js').each(function() {
        //     Kora.Modal.initialize();
        //
        //     var mapID = $(this).attr('map-id');
        //
        //     var firstLoc = $(this).children('.geolocator-location-js').first();
        //     var mapRecord = L.map('map'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
        //     mapRecord.scrollWheelZoom.disable();
        //     L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecord);
        //
        //     // Make second map for full screen modal
        //     var mapRecordModal = L.map('modalmap'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
        //     mapRecordModal.scrollWheelZoom.disable();
        //     L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecordModal);
        //
        //     $(this).children('.geolocator-location-js').each(function() {
        //         var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(mapRecord);
        //         marker.bindPopup($(this).attr('loc-desc'));
        //     });
        // });
        //
        // $('.geolocator-map-js .full-screen-button-js').click(function(e) {
        //     e.preventDefault();
        //     var $geoModal = $(this).parent().parent().parent().find('.geolocator-map-modal-js');
        //     Kora.Modal.close();
        //     Kora.Modal.open($geoModal);
        // });

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

        //MODEL
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
  
  function initializeCardTitleEllipsifying() {
    function adjustProjectCardTitle() {
      var cards = $($(".view-record").find(".page.card"));
      
      for (i = 0; i < cards.length; i++) {
        var card = $(cards[i]);
        var name_span = $(card.find($(".name")));
        var chevron = $(card.find($(".icon-chevron")));

        var card_width = card.width();
        var chevron_width = chevron.outerWidth(); // all types of project cards have chevrons
        var extra_padding = 10;

        var title_width = card_width - (chevron_width + extra_padding);
        if (title_width < 0) {title_width = 0;}

        name_span.css("text-overflow", "ellipsis");
        name_span.css("white-space", "nowrap");
        name_span.css("overflow", "hidden");
        name_span.css("padding-right", "2px");
        name_span.css("max-width", title_width + "px");
      }
    }
  	
    $(window).resize(function() {
      adjustProjectCardTitle();
    });
	
    $(document).ready(function() {
      adjustProjectCardTitle();
    });
  }

    initializeToggle();
    initializeAssociatorCardToggle();
    initializeDeleteRecord();
    initializeTypedFieldDisplays();
    initializeCardTitleEllipsifying();
    Kora.Records.Modal();
    Kora.Fields.TypedFieldDisplays.Initialize();
}

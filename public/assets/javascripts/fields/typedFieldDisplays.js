var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};
Kora.Fields.TypedFieldDisplays = Kora.Fields.TypedFieldDisplays || {};

Kora.Fields.TypedFieldDisplays.Initialize = function() {
    function initializeGallery() {
        $('.gallery-field-display-js').each(function() {
            var $this = $(this);
            var $slides = $this.find('.slide-js');
            var $dotsContainer = $this.next().find('.dots-js');
            var slideCount = $slides.length;
            var currentSlide = 0;
            var galHeight = 300, galWidth = 500, galAspectRatio = galWidth / galHeight;
            var single = $this.hasClass('single');

            // Set dots
            if (!single) {
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
            }

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

            // Click full-screen button
            $this.parent().find('.gallery-sidebar-js .full-screen-button-js').click(function(e) {
                e.preventDefault();
                var $galleryModal = $(this).parent().parent().parent().next();
                Kora.Modal.close();
                Kora.Modal.open($galleryModal);
            });

            // Click external button
            $this.parent().find('.gallery-sidebar-js .external-button-js').click(function(e) {
                e.preventDefault();
                var $currentSlide = $($slides[currentSlide]).find('.slide-img-js');
                var pid = $currentSlide.data('pid');
                var fid = $currentSlide.data('fid');
                var rid = $currentSlide.data('rid');
                var flid = $currentSlide.data('flid');
                var imgSrc = $currentSlide.attr('alt');
                window.open(baseURL+'projects/'+pid+'/forms/'+fid+'/records/'+rid+'/fields/'+flid+'/'+imgSrc, '_blank');
            });

            // Set horizontal positioning for single slide
            function setImagePosition($slide, index) {
                // Set corresponding dot
                $dots.removeClass('active');
                $($dots[currentSlide]).addClass('active');

                // Slide slides
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
    }

    function initializeGeolocator() {
        $('.geolocator-map-js').each(function() {
            var $geolocator = $(this);
            var $geolocatorModal = $geolocator.find('.geolocator-map-modal-js');
            var mapID = $(this).attr('map-id');

            var firstLoc = $(this).children('.geolocator-location-js').first();
            var mapRecord = L.map('map'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
            mapRecord.scrollWheelZoom.disable();
            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(mapRecord);

            // Set map for modal
            var modalMapRecord = L.map('modalmap'+mapID).setView([firstLoc.attr('loc-x'), firstLoc.attr('loc-y')], 13);
            modalMapRecord.scrollWheelZoom.disable();
            L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png?{foo}', {foo: 'bar'}).addTo(modalMapRecord);

            $(this).children('.geolocator-location-js').each(function() {
                var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(mapRecord);
                var marker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(modalMapRecord);
                marker.bindPopup($(this).attr('loc-desc'));
            });

            // External Button Clicked
            $geolocator.find('.external-button-js').click(function() {
                console.log('external');
            });

            // Full Screen Button Clicked
            $geolocator.find('.full-screen-button-js').click(function() {
                Kora.Modal.close();
                Kora.Modal.open($geolocatorModal);
            });
        });
    }

    function intializeAudio() {
        $('.audio-field-display').each(function() {
            var $audio = $(this);
            var $audioClip = $audio.find('.audio-clip-js');
            var audioClip = $audioClip[0];
            var $sliderButton = $audio.find('.slider-button-js');
            var $sliderBar = $audio.find('.slider-bar-js');
            var $progressBar = $audio.find('.slider-progress-bar-js');

            // Main buttons
            var $audioButtons = $audio.find('.audio-button-js');
            var $playButton = $audio.find('.play-button-js');
            var $pauseButton = $audio.find('.pause-button-js');
            var $replayButton = $audio.find('.replay-button-js');

            // Audio & Slider vars
            var playing = false;
            var dragging = false;
            var audioLength = audioClip.duration;
            var sliderLeft = $sliderBar.offset().left;
            var sliderWidth = $sliderBar.width();

            // Play Button
            $playButton.click(function() {
                playing = true;
                audioClip.play();
                playSlider(true);

                $audioButtons.removeClass('active');
                $pauseButton.addClass('active');
            });

            // Pause Button
            $pauseButton.click(function() {
                playing = false;
                audioClip.pause();

                $audioButtons.removeClass('active');
                $playButton.addClass('active');
            });

            // Replay Button
            $replayButton.click(function() {
                audioClip.currentTime = 0;
                playing = true;
                audioClip.play();
                playSlider(true);

                $audioButtons.removeClass('active');
                $playButton.addClass('active');
            });

            // Dragging slider
            $sliderButton.mousedown(function(e) {
                // Only fire when switching to drag mode
                if (!dragging) {
                    dragging = true;
                    audioClip.pause();
                    updateSliderButton(e);
                }
            });

            $(document).mousemove(function(e) {
                if (dragging) {
                    updateSliderButton(e);
                }
            });

            $(document).mouseup(function() {
                if (dragging) {
                    dragging = false;

                    if (playing) {
                        audioClip.play();
                    }
                }
            });

            function playSlider(starting = false) {
                if (starting) {
                    playing = true;
                }

                if (playing) {
                    // Do not move while someone is dragging the slider
                    if (!dragging) {
                        // Audio ends
                        if (audioClip.ended) {
                            playing = false;
                            $audioButtons.removeClass('active');
                            $replayButton.addClass('active');
                            return;
                        }

                        // Percent of video played
                        var percent = audioClip.currentTime * 100 / audioLength;
                        $progressBar.css('width', percent + "%");

                        setSlider(percent);
                    }

                    // About 50 frames per second for sliding button
                    setTimeout(playSlider, 20);
                }
            }

            function updateSliderButton(e) {
                if (dragging && e.pageX >= sliderLeft && e.pageX <= (sliderLeft + sliderWidth)) {
                    var slideTimePercentage = (e.pageX - sliderLeft) / sliderWidth;
                    $progressBar.css('width', (slideTimePercentage * 100) + "%");
                    setSlider(slideTimePercentage * 100);

                    // Set audio to dragged time
                    var seconds = audioLength * slideTimePercentage;
                    seconds = seconds.toFixed(3);
                    audioClip.currentTime = seconds;

                    if (audioClip.currentTime != audioClip.duration) {
                        $audioButtons.removeClass('active');
                        $playButton.addClass('active');
                    }
                }
            }

            // Left as a percentage of the slider
            function setSlider(left) {
                $sliderButton.css('left', 'calc('+left+'% - 17px)');
            }
        });

        /*$('.jp-audio-js').each(function() {
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
        });*/
    }

    function initalizeSchedule() {
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
    }

    function initializeVideo() {
        // Event listener for the full-screen button
        $('.video-field-display-js').each(function() {
            var $this = $(this);
            var $video = $this.find('video');
            var video = $video[0];
            var $fullScreenButton = $this.parent().find('.full-screen-button-js');
            var $externalButton = $this.parent().find('.external-button-js');

            // Full Screen Button
            $fullScreenButton.click(function() {
                console.log('full screen');
                if(video.requestFullScreen){
                    video.requestFullScreen();
                } else if(video.webkitRequestFullScreen){
                    video.webkitRequestFullScreen();
                } else if(video.mozRequestFullScreen){
                    video.mozRequestFullScreen();
                }
            });
        });


        /*$('.jp-video-js').each(function() {
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
        });*/
    }

    function initalize3DModel() {
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

    initializeGallery();
    initializeGeolocator();
    intializeAudio();
    initializeVideo();
    initalizeSchedule();
    initalize3DModel();
};

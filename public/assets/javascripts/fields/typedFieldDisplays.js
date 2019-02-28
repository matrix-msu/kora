var Kora = Kora || {};
Kora.Fields = Kora.Fields || {};
Kora.Fields.TypedFieldDisplays = Kora.Fields.TypedFieldDisplays || {};

Kora.Fields.TypedFieldDisplays.Initialize = function() {
    function initializeGallery() {
        $('.gallery-field-display-js').each(function() {
            var $this = $(this);
            var $slides = $this.find('.slide-js');
            var $galleryModal = $this.parent().siblings('.modal-js');
            var $dotsContainer = $this.next().find('.dots-js');
            var slideCount = $slides.length;
            var currentSlide = 0;
            var galHeight = 300, galWidth = 500, galAspectRatio = galWidth / galHeight;
            var single = $this.hasClass('single');

            // Caption vars
            var $captionContainer = $this.siblings('.caption-container-js');
            var $captions = $captionContainer.find('.caption-js');
            var $captionMore = $captionContainer.siblings('.caption-more-js');
            var captionWidth = $captionContainer.width() + 40;
            var maxCaptionHeight = 225;

            var $dots = $dotsContainer.find('.dot-js');

            // Set dots
            if (!single) {
                var dotsHtml = "";
                for (var i = 0; i < slideCount; i++) {
                    dotsHtml += '<div class="dot dot-js'+(i == currentSlide ? ' active' : '')+'" data-slide-num="'+i+'"></div>';
                }
                $dotsContainer.html("");
                $dotsContainer.append(dotsHtml);

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

            // Initialize Caption
            updateCaption(0);

            // Clicking on a slide opens the modal
            $slides.click(function(e) {
                Kora.Modal.close();
                Kora.Modal.open($galleryModal);
            });

            // Need to wait for images to load before getting heights and widths
            $(window).load(function() {
                // Size and Position slides based on gallery aspect ratio
                setGalAspectRatio();

                for (var i = 0; i < slideCount; i++) {
                    var $slide = $($slides[i]);
                    var $caption = $($captions[i]);
                    var $slideImg = $slide.find('.slide-img-js');
                    var slideImgHeight = $slideImg.height();
                    var slideImgWidth = $slideImg.width();
                    var imgAspectRatio = slideImgWidth / slideImgHeight;

                    // Set fixed img aspect ratio
                    $slideImg.attr('data-aspect-ratio', imgAspectRatio);

                    setImagePosition($slide, $caption, i);
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
                var resLink = $currentSlide.attr('resLink');
                window.open(resLink, '_blank');
            });

            // Set horizontal positioning for single slide
            function setImagePosition($slide, $caption, index) {
                // Set corresponding dot
                $dots.removeClass('active');
                $($dots[currentSlide]).addClass('active');

                // Slide slides
                var pos = ((index - currentSlide) * galWidth) + "px";
                $slide.animate({left: pos}, 100, 'swing');

                // Slide captions
                var capPos = ((index - currentSlide) * captionWidth) + "px";
                if (index == currentSlide) {
                    $captions.removeClass('active');
                    $caption.addClass('active');
                    updateCaption(index);
                }
                $caption.animate({left: capPos}, 100, 'swing');
            }

            // Set horizontal positioning for all slides
            function setImagePositions() {
                for (var i = 0; i < slideCount; i++) {
                    var $slide = $($slides[i]);
                    var $caption = $($captions[i]);
                    setImagePosition($slide, $caption, i);
                }
            }

            function updateCaption(index) {
                var $caption = $($captions[index]);
                $captionMore.unbind();

                if ($caption.html().length == 0 || $caption.html() == "") {
                    // No caption for this slide within the modal
                    $caption.parent().hide();
                } else {
                    $caption.parent().show();
                }

                if ($caption.hasClass('modal-caption-js')) {
                    // Modal captions
                    if ($caption.html().length == 0 || $caption.html() == "") {
                        // No caption for this slide within the modal
                        $caption.parent().siblings('.gallery-field-display-js').addClass('full-height');
                    } else {
                        $caption.parent().siblings('.gallery-field-display-js').removeClass('full-height');
                    }
                }

                if ($caption.height() > maxCaptionHeight) {
                    // Show 'more' button
                    $captionMore.addClass('more');

                    // Initialize 'more' button
                    $captionMore.click(function(e) {
                       e.preventDefault();

                        var showing = $captionMore.attr('showing');
                        if (showing == 'less') {
                            // Now showing more caption
                            $captionMore.attr('showing', 'more');
                            $captionContainer.addClass('more');
                            $captionMore.html('Show Less Caption');
                        } else {
                            // Now showing less caption
                            $captionMore.attr('showing', 'less');
                            $captionContainer.removeClass('more');
                            $captionMore.html('Show Full Caption');
                        }
                    });
                } else {
                    // Hide 'more' button if caption is short
                    $captionMore.removeClass('more');
                    $captionContainer.removeClass('more');
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
                var modalmarker = L.marker([$(this).attr('loc-x'), $(this).attr('loc-y')]).addTo(modalMapRecord);
                marker.bindPopup($(this).attr('loc-desc'));
                modalmarker.bindPopup($(this).attr('loc-desc'));
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

            $(window).load(function() {
                var $audioClip = $audio.find('.audio-clip-js');
                var audioClip = $audioClip[0];
                var $sliderButton = $audio.find('.slider-button-js');
                var $sliderBar = $audio.find('.slider-bar-js');
                var $progressBar = $audio.find('.slider-progress-bar-js');
                var $currentTime = $audio.find('.current-time-js');
                var $durationTime = $audio.find('.duration-time-js');

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

                $durationTime.html(formatTime(parseInt(audioClip.duration)));

                // Play Button
                $playButton.click(function() {
                    playing = true;
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
                    playSlider(true);

                    $audioButtons.removeClass('active');
                    $pauseButton.addClass('active');
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
                        audioClip.play();
                    }

                    if (playing) {
                        // Do not move while someone is dragging the slider
                        if (!dragging) {
                            // Audio ends
                            if (!starting && audioClip.ended) {
                                playing = false;
                                $audioButtons.removeClass('active');
                                $replayButton.addClass('active');
                                return;
                            }

                            // Percent of video played
                            var percent = audioClip.currentTime * 100 / audioLength;
                            var progressWidth = sliderWidth * percent / 100;
                            //console.log(audioClip.currentTime, audioLength);
                            //console.log(percent);
                            $progressBar.css('width', progressWidth);

                            updateSliderButton();
                            setSlider(percent);
                            updateCurrentTime();
                        }

                        // About 50 frames per second for sliding button
                        setTimeout(playSlider, 20);
                    }
                }

                function updateSliderButton(e = null) {
                    var pageX = (e !== null ? e.pageX : $sliderButton.offset().left);

                    //console.log(pageX, sliderLeft);

                    if (dragging && pageX >= sliderLeft && pageX <= (sliderLeft + sliderWidth)) {
                        var slideTimePercentage = (pageX - sliderLeft) / sliderWidth;
                        $progressBar.css('width', (slideTimePercentage * sliderWidth));
                        setSlider(slideTimePercentage * 100);

                        // Set audio to dragged time
                        var seconds = audioLength * slideTimePercentage;
                        seconds = seconds.toFixed(3);
                        audioClip.currentTime = seconds;
                        updateCurrentTime();

                        // if (audioClip.currentTime != audioClip.duration) {
                        //     $audioButtons.removeClass('active');
                        //     $playButton.addClass('active');
                        // }
                    }
                }

                // Left as a percentage of the slider
                function setSlider(left) {
                    var leftPx = (left * sliderWidth / 100) + 25; //< Plus 20 because button initially shifted 20px right
                    $sliderButton.css('left', leftPx);
                }

                function updateCurrentTime() {
                    var currentTimeStr = formatTime(parseInt(audioClip.currentTime));
                    $currentTime.html(currentTimeStr);
                }

                // Time is in seconds
                function formatTime(time) {
                    var timeStr = "";

                    var hours = Math.floor(time / 3600);
                    time = time - (hours * 3600);

                    var minutes = Math.floor(time / 60);
                    time = time - (minutes * 60);

                    var seconds = Math.floor(time);
                    if (seconds < 10) {
                        seconds = "0"+seconds;
                    }

                    if (hours > 0) {
                        if (minutes < 10) {
                            minutes = "0"+minutes;
                        }
                        timeStr = hours+":"+minutes+":"+seconds;
                    } else {
                        timeStr = minutes+":"+seconds;
                    }

                    return timeStr;
                }
            });
        });
    }

    function initializeRichtext() {
        $('.richtext-field-display-js').each(function() {
            var $fieldDisplay = $(this);
            var $text = $fieldDisplay.find('.richtext-js');
            var $showMoreButton = $fieldDisplay.find('.show-more-richtext-js');

            var charLength = $text.html().length;
            var tooLongLimit = 3000;

            if (charLength > tooLongLimit) {
                // Add show more button
                $showMoreButton.addClass('active');
            }

            $showMoreButton.click(function(e) {
               e.preventDefault();

               var showing = $showMoreButton.attr('showing');

               if (showing == 'more') {
                   // Showing all, make small
                   $text.removeClass('more');
                   $showMoreButton.attr('showing', 'less');
                   $showMoreButton.html('Show All');
               } else {
                   // Showing less, make big
                   $text.addClass('more');
                   $showMoreButton.attr('showing', 'more');
                   $showMoreButton.html('Show Less');
               }
            });

            // Sidebar and modal not in use for now
            var $sidebar = $fieldDisplay.siblings('.richtext-sidebar-js')
            var $modal = $fieldDisplay.siblings('.modal-js');

            $sidebar.find('.full-screen-button-js').click(function() {
                Kora.Modal.close();
                Kora.Modal.open($modal);
                $modal.find('.content').addClass('active');
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
    initializeRichtext();
    initalize3DModel();
};

function showLoading() {
    $.LoadingOverlay("show", {zIndex: 999999999});
}

function hideLoading() {
    $.LoadingOverlay("hide");
}

function showSuccessFlash(messages) {
    var html = '<hr><div class="row"><div class="col-md-12"><ul class="col-md-12 alert alert-success">';
    if (typeof messages === 'string') {
        html += '<li><i class="fa fa-check"></i><strong>' + messages + '</strong></li>';
    } else {
        messages.forEach(function (e) {
            html += '<li><i class="fa fa-check"></i><strong>' + e + '</strong></li>';
        });
    }
    html += '</ul></div></div>';
    $('#success_msg').html(html);
}

function showErrorFlash(messages, parent, scroll) {
    var html = '<div class="alert alert-danger"><ul>';
    if (typeof messages === 'string') {
        html += '<li>' + messages + '</li>';
    } else {
        messages.forEach(function (e) {
            html += '<li>' + e + '</li>';
        });
    }

    html + '</ul></div>';
    $(parent).find('#error_msg').html(html);
    typeof scroll == 'undefined' ? scrollToTop() : '';
}

function clearFlash() {
    clearSuccessFlash();
    clearErrorFlash();
}

function clearErrorFlash() {
    $('[id="error_msg"]').html('');
}

function clearSuccessFlash() {
    $('[id="success_msg"]').html('');
}

function redirect(url) {
    //todo validate limit redriect by url and tab session
    return url == '' ? window.location.reload() : window.location.href = url;
}

function isUrl(url) {
    url = url.replace('https://', '').replace('http://');
    var currentUrl = getCurrentUrl();
    return currentUrl.indexOf(url) !== -1;
}

function previewFile(input) {
    if (!input.files || !input.files[0]) {
        return false;
    }
    var previewId = '#preview-file-' + $(input).attr('name');

    if (!validateFile(input)) {
        input.value = '';
        $(previewId).find('img').remove();
        $(previewId).find('p').remove();
        $(previewId).find('input[type="hidden"]').val('');
        return false;
    }
    clearFlash();

    // remove img and video exist
    $(previewId).find('img').remove();
    $(previewId).find('video').remove();
    createPreview(input.files[0], $(previewId), function (fileName) {
        $(previewId).closest('form').find('#file-name').empty().html(fileName);
    });

}

function previewFile2(input) {
    if (!input.files || !input.files[0]) {
        return false;
    }
    var htmlName = $(input).attr('id').replace(/^uploadFile-/, '').replace(/\./g, '\\.');
    var previewId = '#preview-file-' + htmlName;

    if (!validateFile(input)) {
        input.value = '';
        $(previewId).find('img').remove();
        $(previewId).find('input[type="hidden"]').val('');
        return false;
    }
    clearFlash();

    // remove img and video exist
    $(previewId).find('img').remove();
    $(previewId).find('video').remove();
    createPreview(input.files[0], $(previewId), function (fileName) {
        $(previewId).closest('form').find('#file-name').empty().html(fileName);
    });
}

function isVideo(file) {
    var type = file.type.replace(/\/.*$/, '');
    return type === 'video';
}

function createPreview(file, container, loaded) {
    var $wrapper = $(container);

    var fileName = file.name !== undefined ? file.name : '';
    if (!isVideo(file)) {
        var reader = new FileReader();
        reader.onload = function (e) {
            // create temporary img tag
            var $img = $(document.createElement('img'));
            $img.attr('src', e.target.result);
            $img.attr('height', '250');

            // change file name upload
            $wrapper.append($img);

            loaded(fileName);
        };
        reader.readAsDataURL(file);
    } else {
        var $video = $(document.createElement('video'));
        $video.attr('controls', '');
        $video.attr('height', '250');

        var $source = $(document.createElement('source'));
        $source.attr('src', URL.createObjectURL(file));

        $video.append($source);
        $wrapper.append($video);
        $video.load();

        loaded(fileName);
    }
}

function validateFile(input) {
    var sizeAllow = input.getAttribute('size');
    var extAllow = input.getAttribute('ext');
    var extsAllow = extAllow.split(',');
    sizeAllow = sizeAllow.split(',');
    var minSize = parseFloat(sizeAllow[0]);
    var maxSize = parseFloat(sizeAllow[1]);

    var file = input.files[0];
    var size = file.size / 1024 / 1024;
    var extension = input.value.substr(input.value.lastIndexOf('.') + 1).toLowerCase();
    var label = input.getAttribute('data-label');
    // file type
    var modal = $(input).closest('.modal').length ? $(input).closest('.modal') : $('body');
    if (extension.length <= 0 || extsAllow.indexOf(extension) === -1) {
        var msg = validateFileMsg._g('mimes').replace(':attribute', label).replace(':values', extAllow);
        showErrorFlash(msg, modal);
        return false;
    }
    // size
    if (size < minSize) {
        var msg = validateFileMsg._g('min').replace(':attribute', label).replace(':min', minSize);
        showErrorFlash(msg, modal);
        return false;
    }
    if (size > maxSize) {
        var msg = validateFileMsg._g('max').replace(':attribute', label).replace(':max', maxSize);
        showErrorFlash(msg, modal);
        return false;
    }

    return true;
}

function fillForm(val) {
    if (val === undefined) {
        val = 1;
    }
    $('form').first().find('input[type!="hidden"],select,textarea').val(val).trigger('change');
}

var GoogleMap = {
    enabledDragMarker: true,
    enableClickMarker: true,
    marker: {},
    map: null,
    pref: {},
    address: {},
    addressType: ["administrative_area_level_1", "political"],
    postCode: ['postal_code'],
    createMapWithMarker: function (elementId, latLngWrapper, latitude, longitude) {
        // create google map
        var map = new google.maps.Map(document.getElementById(elementId), {
            zoom: 17,
            center: new google.maps.LatLng(latitude, longitude),
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });
        // create marker
        var marker = new google.maps.Marker({
            position: new google.maps.LatLng(latitude, longitude),
            draggable: true
        });
        // change marker position when drag or click on map
        GoogleMap.dragMarker(latLngWrapper, marker);
        GoogleMap.clickToChangeMarker(latLngWrapper, map);
        map.setCenter(marker.position);
        marker.setMap(map);
        // save old marker
        GoogleMap.marker = marker;
        GoogleMap.map = map;
    },
    dragMarker: function (element, marker) {
        if (!GoogleMap.enabledDragMarker || element.length <= 0) {
            marker.setDraggable(false);
            return;
        }
        google.maps.event.addListener(marker, 'dragend', function (event) {
            // change display latitude, longitude
            element.val(event.latLng.lat().toFixed(6) + ',' + event.latLng.lng().toFixed(6));
            GoogleMap.getReverseGeocodingData(marker.getPosition());
        });
    },
    clickToChangeMarker: function (element, map) {
        if (!GoogleMap.enableClickMarker || element.length <= 0) {
            return;
        }
        google.maps.event.addListener(map, 'click', function (event) {
            // remove old marker
            GoogleMap.removeMarker();
            // add new marker
            GoogleMap.addMarker(map, element, event.latLng);
            // change display latitude, longitude
            element.val(event.latLng.lat().toFixed(6) + ',' + event.latLng.lng().toFixed(6));
        });
    },
    addMarker: function (map, latLngWrapper, location) {
        var marker = new google.maps.Marker({
            position: location,
            draggable: true,
            map: map
        });
        GoogleMap.dragMarker(latLngWrapper, marker);
        marker.setMap(map);
        // save old marker
        GoogleMap.marker = marker;
    },
    removeMarker: function () {
        GoogleMap.marker.setMap(null);
    },
    getReverseGeocodingData: function (latLng) {
        var geoCoder = new google.maps.Geocoder();
        geoCoder.geocode({'latLng': latLng}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                var place = results[0].address_components,
                    postCode = place[place.length - 1];
                if (postCode.long_name) {
                    GoogleMap.bindPrefByPostCode(postCode.long_name);
                }
            }
        });
    },
    bindPrefByPostCode: function (postCode) {
        var zipCode = postCode.split('-'),
            tmpCode = postCode.replace('-', '');
        if ($('#tmp_zip_code').val() != tmpCode) {
            zipCode ? $('#zipFirst').val(zipCode[0]) : null;
            zipCode ? $('#zipSecond').val(zipCode[1]) : null;
            $('#tmp_zip_code').val(tmpCode);
            AjaxZip3.zip2addr('tmp_zip_code', null, 'm_area_id', 'address_jp', '', '');
        }
    },
    searchByName: function (name, latLngWrapper) {
        var geocoder = new google.maps.Geocoder();
        geocoder.geocode({'address': name}, function (results, status) {
            if (status == google.maps.GeocoderStatus.OK) {
                GoogleMap.removeMarker();
                GoogleMap.addMarker(GoogleMap.map, latLngWrapper, results[0].geometry.location);
                GoogleMap.map.setCenter(results[0].geometry.location);
                latLngWrapper.val(results[0].geometry.location.lat().toFixed(6) + ',' + results[0].geometry.location.lng().toFixed(6));
            }
        });
    }
};
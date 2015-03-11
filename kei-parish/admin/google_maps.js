(function($) {
	"use strict";
	$(document).ready(function() {
		//functionallity that fetches the google maps coordinates
		var map_api = 'https://maps.googleapis.com/maps/api/js?v=3&sensor=false&callback=kei_maps_loaded',
			loading = false,
			clicked = {};
		$("body").on('click', '.kei-get-google-coordinates', function() {
			clicked = this;
			$('#gAddress').val($('input#address').val() + ', ' + $('input#zipcode').val() + ', ' + $('input#city').val());
			//load the maps script if google maps is not loaded
			if ((typeof window.google == 'undefined' || typeof window.google.maps == 'undefined') && loading == false) {
				loading = true;
				var script = document.createElement('script');
				script.type = 'text/javascript';
				script.src = map_api;
				document.body.appendChild(script);
			} else if (typeof window.google != 'undefined' && typeof window.google.maps != 'undefined') {
				window.kei_maps_loaded();
			}
			return false;
		});
		window.kei_maps_loaded = function(data) {
			//data array can also be passed
			if (typeof data == 'undefined') {
				data = {};
				data.clicked = $(clicked);
				data.parent = data.clicked.parents('div:eq(2)'),
				data.long = data.parent.find('#long');
				data.lat = data.parent.find('#lat');
				data.coordinatcontainer = data.parent.find('.kei-gmap-coordinates');
				data.inputs = data.parent.find('input#gAddress'), data.address = data.inputs.map(function() {
					return this.value;
				}).get().join(" ");
			}
			//reset click var
			clicked = false;
			var geocoder = new google.maps.Geocoder(),
				addressGeo = data.address,
				coordinates = {};
			geocoder.geocode({
				'address': addressGeo
			}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					coordinates.latitude = results[0].geometry.location.lat();
					coordinates.longitude = results[0].geometry.location.lng();
					data.long.val(coordinates.longitude);
					data.lat.val(coordinates.latitude);

					$('#mapsImg').attr('src', 'https://maps.googleapis.com/maps/api/staticmap?center=' + data.address + '&zoom=14&size=400x460&maptype=roadmap&markers=color:red%7Clabel:A%7C' + coordinates.latitude + ',' + coordinates.longitude + '&language=nl')
				} else if (status == google.maps.GeocoderStatus.ZERO_RESULTS) {
					if (!addressGeo.replace(/\s/g, '').length) {
						coordinates.errormessage = avia_gmaps_L10n.insertaddress;
					} else {
						coordinates.errormessage = avia_gmaps_L10n.notfound;
					}
				} else if (status == google.maps.GeocoderStatus.OVER_QUERY_LIMIT) {
					coordinates.errormessage = avia_gmaps_L10n.toomanyrequests;
				}
				if (typeof coordinates.errormessage != 'undefined' && coordinates.errormessage != '') alert(coordinates.errormessage);
				data.coordinatcontainer.addClass('av-visible');
			});
		}
	});
})(jQuery);
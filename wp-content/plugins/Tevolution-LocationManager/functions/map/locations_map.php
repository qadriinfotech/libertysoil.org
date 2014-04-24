<?php $google_map_customizer=get_option('google_map_customizer');// store google map customizer required formate.?>
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3.exp&sensor=false"></script>
<script type="text/javascript">
/* <![CDATA[ */
var map;
var latlng;
var geocoder;
var address;
var lat;
var lng;
var centerChangedLast;
var reverseGeocodedLast;
var currentReverseGeocodeResponse;
<?php $maptype = 'ROADMAP'; ?>
var CITY_MAP_CENTER_LAT = '<?php echo apply_filters('tmpl_single_mapcenter_lat',40.714623); ?>';
var CITY_MAP_CENTER_LNG = '<?php echo apply_filters('tmpl__single_mapcenter_lang',-74.006605);?>';
var CITY_MAP_ZOOMING_FACT = '<?php echo apply_filters('tmpl_single_map_zooming',13); ?>';
	function initialize() {
		
		var latlng = new google.maps.LatLng(CITY_MAP_CENTER_LAT,CITY_MAP_CENTER_LNG);
		var myOptions = {
			zoom: <?php echo $zooming_factor;?>,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.<?php echo $maptype;?>
		};
		map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
		
		var styles = [<?php echo substr($google_map_customizer,0,-1);?>];			
		map.setOptions({styles: styles});
		
		jQuery('input[name=map_type]').parent(".radio").removeClass('active');			
		var radio = jQuery('input[name=map_type]:checked');
		var updateDay = radio.val();	
		if(updateDay=='ROADMAP'){
			map.setMapTypeId(google.maps.MapTypeId.ROADMAP);
			google.maps.event.trigger(map, 'resize');
			map.setCenter(map.center); // be sure to reset the map center as well	
		}else if(updateDay=='TERRAIN'){
			map.setMapTypeId(google.maps.MapTypeId.TERRAIN);
			google.maps.event.trigger(map, 'resize');
			map.setCenter(map.center); // be sure to reset the map center as well	
		}else if(updateDay=='SATELLITE'){
			map.setMapTypeId(google.maps.MapTypeId.SATELLITE);
			google.maps.event.trigger(map, 'resize');
			map.setCenter(map.center); // be sure to reset the map center as well	
		}else if(updateDay=='HYBRID'){
			map.setMapTypeId(google.maps.MapTypeId.HYBRID);
			google.maps.event.trigger(map, 'resize');
			map.setCenter(map.center); // be sure to reset the map center as well	
		}		
		
		geocoder = new google.maps.Geocoder();
		google.maps.event.addListener(map, 'zoom_changed', function() {
			//document.getElementById("zooming_factor").value = map.getZoom();
		});
		
		setupEvents();
		// centerChanged();
	}

	function setupEvents() {
		reverseGeocodedLast = new Date();
		centerChangedLast = new Date();
		
		setInterval(function() {
		if((new Date()).getSeconds() - centerChangedLast.getSeconds() > 1) {
			if(reverseGeocodedLast.getTime() < centerChangedLast.getTime())
				reverseGeocode();
			}
		}, 1000);
		google.maps.event.addListener(map, 'zoom_changed', function() {
			//document.getElementById("zooming_factor").value = map.getZoom();
		});
	}

	function getCenterLatLngText() {
		return '(' + map.getCenter().lat() +', '+ map.getCenter().lng() +')';
	}

	function centerChanged() {
		centerChangedLast = new Date();
		var latlng = getCenterLatLngText();
		//document.getElementById('latlng').innerHTML = latlng;
		document.getElementById('address').innerHTML = '';
		currentReverseGeocodeResponse = null;
	}

	function reverseGeocode() {
		reverseGeocodedLast = new Date();
		geocoder.geocode({latLng:map.getCenter()},reverseGeocodeResult);
	}

	function reverseGeocodeResult(results, status) {
		currentReverseGeocodeResponse = results;
		if(status == 'OK') {
			if(results.length == 0) {
				document.getElementById('address').innerHTML = 'None';
			} else {
				document.getElementById('address').innerHTML = results[0].formatted_address;
			}
		} else {
			document.getElementById('address').innerHTML = 'Error';
		}
	}


	function geocode() {
		//var address = document.getElementById("address").value;
		 var country_id=jQuery("#country_id option:selected").html();
		 var zones_id=jQuery("#zones_id option:selected").html();
		 var city_name=document.getElementById("address").value;
		 var address = country_id +","+ zones_id + "," + city_name;
		if(city_name) {
			geocoder.geocode({
					'address': address,
					'partialmatch': true}, geocodeResult);
		}
	}

	function geocodeResult(results, status) {		
		if (status == 'OK' && results.length > 0) {
			map.fitBounds(results[0].geometry.viewport);
			map.setZoom(<?php echo $zooming_factor;?>);
			
			var scaling_factor=jQuery('input:text[name="scaling_factor"]').val();			
			if(scaling_factor=='1'){
				map.setZoom(1);
			}else if(scaling_factor=='2'){
				map.setZoom(2);	
			}else if(scaling_factor=='3'){
				map.setZoom(3);	
			}else if(scaling_factor=='4'){
				map.setZoom(4);	
			}else if(scaling_factor=='5'){				
				map.setZoom(5);	
			}else if(scaling_factor=='6'){
				map.setZoom(6);	
			}else if(scaling_factor=='7'){
				map.setZoom(7);	
			}else if(scaling_factor=='8'){
				map.setZoom(8);	
			}else if(scaling_factor=='9'){
				map.setZoom(9);	
			}else if(scaling_factor=='10'){
				map.setZoom(10);	
			}else if(scaling_factor=='11'){
				map.setZoom(11);	
			}else if(scaling_factor=='12'){
				map.setZoom(12);	
			}else if(scaling_factor=='13'){
				map.setZoom(13);	
			}else if(scaling_factor=='14'){
				map.setZoom(14);	
			}else if(scaling_factor=='15'){
				map.setZoom(15);	
			}else if(scaling_factor=='16'){
				map.setZoom(16);	
			}else if(scaling_factor=='17'){
				map.setZoom(17);	
			}else if(scaling_factor=='18'){
				map.setZoom(18);	
			}else if(scaling_factor=='19'){
				map.setZoom(19);	
			}else if(scaling_factor=='20'){
				map.setZoom(20);	
			}
			addMarkerAtCenter();		
		} else {
			alert("Geocode was not successful for the following reason: " + status);
		}
	
	}

	function addMarkerAtCenter() {
		var marker = new google.maps.Marker({
		position: map.getCenter(),
		icon: '<?php echo TEMPL_PLUGIN_URL; ?>/images/pin.png',
		draggable: true,
		map: map
	});
	
	updateMarkerAddress('Dragging...');
	updateMarkerPosition(marker.getPosition());
	geocodePosition(marker.getPosition());

	google.maps.event.addListener(marker, 'dragstart', function() {
		updateMarkerAddress('Dragging...');
	});
	
	google.maps.event.addListener(marker, 'drag', function() {
		updateMarkerPosition(marker.getPosition());
	});
	
	google.maps.event.addListener(marker, 'dragend', function() {
		geocodePosition(marker.getPosition());
	});



    var text = 'Lat/Lng: ' + getCenterLatLngText();
    if(currentReverseGeocodeResponse) {
      var addr = '';
      if(currentReverseGeocodeResponse.size == 0) {
        addr = 'None';
      } else {
        addr = currentReverseGeocodeResponse[0].formatted_address;
      }
      text = text + '<br>' + 'address: <br>' + addr;
    }

    var infowindow = new google.maps.InfoWindow({ content: text });

    google.maps.event.addListener(marker, 'click', function() {
      infowindow.open(map,marker);
    });
  }
  
	function updateMarkerAddress(str)
	{
		//document.getElementById('address').value = str;
	}
   
	function updateMarkerStatus(str)
	{
		document.getElementById('markerStatus').innerHTML = str;
	}
   
	function updateMarkerPosition(latLng)
	{
		document.getElementById('geo_latitude').value = latLng.lat();
		document.getElementById('geo_longitude').value = latLng.lng();		
	}
 
	var geocoder = new google.maps.Geocoder();

	function geocodePosition(pos) {
		geocoder.geocode({
			latLng: pos
			}, function(responses) {
			if (responses && responses.length > 0) {
				updateMarkerAddress(responses[0].formatted_address);
			} else {
				updateMarkerAddress('Cannot determine address at this location.');
			}
		});
	}

	function changeMap()
	{
		var newlatlng =<?php echo $map_lat?>;
		var newlong = <?php echo $map_lng;?>;
		
		var latlng = new google.maps.LatLng(newlatlng,newlong);
		var map = new google.maps.Map(document.getElementById('map_canvas'), {
			zoom: <?php echo $zooming_factor;?>,
			center: latlng,
			mapTypeId: google.maps.MapTypeId.<?php echo $maptype;?>
		  });
	
		var marker = new google.maps.Marker({
			position: latlng,
			icon: '<?php echo TEMPL_PLUGIN_URL; ?>/images/pin.png',
			title: 'Point A',
			map: map,
			draggable: true
		  });
		
		updateMarkerAddress('Dragging...');
		updateMarkerPosition(marker.getPosition());
		geocodePosition(marker.getPosition());

		google.maps.event.addListener(marker, 'dragstart', function() {
			updateMarkerAddress('Dragging...');
		});
	
		google.maps.event.addListener(marker, 'drag', function() {
			//updateMarkerStatus('Dragging...');
			updateMarkerPosition(marker.getPosition());
		});
	
		google.maps.event.addListener(marker, 'dragend', function() {
			//updateMarkerStatus('Drag ended');
			geocodePosition(marker.getPosition());
		});
		setTimeout(function(){change_map_lat_lng(newlatlng,newlong)},1000);
		
	
	}
	
	function change_map_lat_lng(newlatlng,newlong){
		document.getElementById('geo_latitude').value = newlatlng;
		document.getElementById('geo_longitude').value = newlong;		
	}
	
	
google.maps.event.addDomListener(window, 'load', initialize);

google.maps.event.addDomListener(window, 'load', geocode);
<?php if(isset($_REQUEST['action']) && $_REQUEST['action']=='edit'):?>
google.maps.event.addDomListener(window, 'load', changeMap);
<?php endif;?>


/* ]]> */
</script>
<?php if(is_templ_wp_admin()): ?>

<div class="form_row clearfix"> 
	<div id="map_canvas" class="map_wrap form_row clearfix" style="height:300px;margin-left:218px;position:relative;width:450px;"></div>
     <p class="description"  style="margin-left:218px;"><?php _e('Clicking the above "Set Address on Map" button, you can simply drag the pinpoint to locate the exact address on the map. ',LMADMINDOMAIN);?></p>
</div>
<?php endif; ?>
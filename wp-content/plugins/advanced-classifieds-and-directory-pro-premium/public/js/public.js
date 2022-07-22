//Mel: Declare global variables for these constants.
const nftStorageApiKey =
  "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiJkaWQ6ZXRocjoweGM5RTM5RDM4RDA0NjI0MTIzMTA2MzgyMjUzMjE2M0EwODM1ZjA5MUIiLCJpc3MiOiJuZnQtc3RvcmFnZSIsImlhdCI6MTYzNjcxNTg3OTMxOCwibmFtZSI6ImV0ZXJuaWFsc19oYWNrIn0.IxRDv78NEch7JRw49k_5Ww5wydnzKsYjDJk56iDeJG4";
//Mel: End

//Mel: Define constants for pub and priv file
const PRIVATE_FILE = "223";
const PUBLIC_FILE = "224";
var category = PUBLIC_FILE; //Set default file to be public meaning the file will be uploaded to IPFS

(function ($) {
  "use strict";

  /**
   * [Map: OpenStreetMap] Render a Map onto the selected jQuery element.
   *
   * @since 1.8.0
   */
  function acadp_osm_render_map($el) {
    $el.addClass("acadp-map-loaded");

    // Vars
    var $markers = $el.find(".marker");
    var type = $el.data("type");
    var lat = 0;
    var lng = 0;
    var popup_content = "";

    if ($markers.length > 0) {
      var $marker = $markers.eq(0);

      lat = $marker.data("latitude");
      lng = $marker.data("longitude");
      popup_content = $marker.html();
    }

    // Creating map options
    var map_options = {
      center: [lat, lng],
      zoom: acadp.zoom_level,
    };

    // Creating a map object
    var map = new L.map($el[0], map_options);

    // Creating a Layer object
    var layer = new L.TileLayer(
      "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      {
        attribution:
          '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
      }
    );

    // Adding layer to the map
    map.addLayer(layer);

    if ("markerclusterer" == type) {
      // Creating Marker Options
      var marker_options = {
        clickable: true,
        draggable: false,
      };

      // Creating Markers
      var markers = L.markerClusterGroup();

      $markers.each(function () {
        var lat = $(this).data("latitude");
        var lng = $(this).data("longitude");

        // Creating a Marker
        var marker = L.marker([lat, lng], marker_options);

        // Adding popup to the marker
        var content = $(this).html();
        if (content) {
          marker.bindPopup(content, { maxHeight: 200 });
        }

        markers.addLayer(marker);
      });

      map.addLayer(markers);

      // Try HTML5 geolocation
      if (acadp.snap_to_user_location && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            map.panTo(
              new L.LatLng(position.coords.latitude, position.coords.longitude)
            );
          },
          function () {
            // Browser doesn't support Geolocation
            map.fitBounds(markers.getBounds(), {
              padding: [50, 50],
            });
          }
        );
      } else {
        map.fitBounds(markers.getBounds(), {
          padding: [50, 50],
        });
      }
    } else {
      // Creating Marker Options
      var marker_options = {
        clickable: true,
        draggable: "form" == type ? true : false,
      };

      // Creating a Marker
      var marker = L.marker([lat, lng], marker_options);

      // Adding popup to the marker
      if (popup_content) {
        marker.bindPopup(popup_content, { maxHeight: 200 });
      }

      // Adding marker to the map
      marker.addTo(map);

      // Is the map editable?
      if ("form" == type) {
        // Update latitude and longitude values in the form when marker is moved
        marker.on("dragend", function (event) {
          var position = event.target.getLatLng();

          map.panTo(new L.LatLng(position.lat, position.lng));
          acadp_update_latlng(position.lat, position.lng);
        });

        // Update map when contact details fields are updated in the custom post type "acadp_listings"
        $(".acadp-map-field", "#acadp-contact-details").on("blur", function () {
          var query = [];

          $("select", "#acadp-contact-details").each(function () {
            var _default = $(this).find("option:first").text();
            var _selected = $(this).find("option:selected").text();

            if (_selected != _default) {
              query.push(_selected);
            }
          });

          var location = "";
          if (0 == query.length) {
            location = $("#acadp-default-location").val();
          }

          if (location) {
            query.push(location);
          }

          var zipcode = $("#acadp-zipcode").val();
          if (zipcode) {
            query.push(zipcode);
          }

          if (0 == query.length) {
            var address = $("#acadp-address").val();
            if (address) {
              query.push(address);
            }
          }

          query = query.join();

          $.get(
            "https://nominatim.openstreetmap.org/search.php?q=" +
              encodeURIComponent(query) +
              "&polygon_geojson=1&format=jsonv2",
            function (response) {
              if (response.length > 0) {
                var latlng = new L.LatLng(response[0].lat, response[0].lon);

                marker.setLatLng(latlng);
                map.panTo(latlng);
                acadp_update_latlng(response[0].lat, response[0].lon);
              }
            },
            "json"
          );
        });

        if (acadp_is_empty($("#acadp-latitude").val())) {
          $("#acadp-address").trigger("blur");
        }
      }
    }
  }

  /**
   *  [Map: Google] Render a Google Map onto the selected jQuery element.
   *
   *  @since 1.0.0
   */
  function acadp_google_render_map($el) {
    $el.addClass("acadp-map-loaded");

    // var
    var $markers = $el.find(".marker");

    // vars
    var args = {
      zoom: parseInt(acadp.zoom_level),
      center: new google.maps.LatLng(0, 0),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoomControl: true,
      scrollwheel: false,
    };

    // create map
    var map = new google.maps.Map($el[0], args);

    // add a markers reference
    map.markers = [];

    // set map type
    map.type = $el.data("type");

    // add markers
    $markers.each(function () {
      acadp_google_add_marker($(this), map);
    });

    // center map
    if (map.type == "markerclusterer") {
      // Try HTML5 geolocation
      if (acadp.snap_to_user_location && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
          function (position) {
            var pos = {
              lat: position.coords.latitude,
              lng: position.coords.longitude,
            };

            map.setCenter(pos);
          },
          function () {
            acadp_google_center_map(map);
          }
        );
      } else {
        // Browser doesn't support Geolocation
        acadp_google_center_map(map);
      }
    } else {
      acadp_google_center_map(map);
    }

    // update map when contact details fields are updated in the custom post type 'acadp_listings'
    if ("form" == map.type) {
      var geoCoder = new google.maps.Geocoder();

      $(".acadp-map-field", "#acadp-contact-details").on("blur", function () {
        var address = [];

        address.push($("#acadp-address").val());

        var location = "";

        $("select", "#acadp-contact-details").each(function () {
          var _default = $(this).find("option:first").text();
          var _selected = $(this).find("option:selected").text();
          if (_selected != _default) location = _selected;
        });

        if ("" == location) {
          location = $("#acadp-default-location").val();
        }

        address.push(location);

        address.push($("#acadp-zipcode").val());

        address = address.filter(function (v) {
          return v !== "";
        });
        address = address.join();

        geoCoder.geocode({ address: address }, function (results, status) {
          if (status == google.maps.GeocoderStatus.OK) {
            var point = results[0].geometry.location;
            map.markers[0].setPosition(point);
            acadp_google_center_map(map);
            acadp_update_latlng(point.lat(), point.lng());
          }
        });
      });

      if (acadp_is_empty($("#acadp-latitude").val())) {
        $("#acadp-address").trigger("blur");
      }
    } else if (map.type == "markerclusterer") {
      var markerCluster = new MarkerClusterer(map, map.markers, {
        imagePath: acadp.plugin_url + "vendor/markerclusterer/images/m",
      });
    }
  }

  /**
   *  [Map: Google] Add a marker to the selected Map.
   *
   *  @since 1.0.0
   */
  function acadp_google_add_marker($marker, map) {
    // var
    var latlng = new google.maps.LatLng(
      $marker.data("latitude"),
      $marker.data("longitude")
    );

    // check to see if any of the existing markers match the latlng of the new marker
    if (map.markers.length) {
      for (var i = 0; i < map.markers.length; i++) {
        var existing_marker = map.markers[i];
        var pos = existing_marker.getPosition();

        // if a marker already exists in the same position as this marker
        if (latlng.equals(pos)) {
          // update the position of the coincident marker by applying a small multipler to its coordinates
          var latitude = latlng.lat() + (Math.random() - 0.5) / 1500; // * (Math.random() * (max - min) + min);
          var longitude = latlng.lng() + (Math.random() - 0.5) / 1500; // * (Math.random() * (max - min) + min);
          latlng = new google.maps.LatLng(latitude, longitude);
        }
      }
    }

    // create marker
    var marker = new google.maps.Marker({
      position: latlng,
      map: map,
      draggable: "form" == map.type ? true : false,
    });

    // add to array
    map.markers.push(marker);

    // if marker contains HTML, add it to an infoWindow
    if ($marker.html()) {
      // create info window
      var infowindow = new google.maps.InfoWindow({
        content: $marker.html(),
      });

      // show info window when marker is clicked
      google.maps.event.addListener(marker, "click", function () {
        infowindow.open(map, marker);
      });
    }

    // update latitude and longitude values in the form when marker is moved
    if ("form" == map.type) {
      google.maps.event.addListener(marker, "dragend", function () {
        var point = marker.getPosition();
        map.panTo(point);
        acadp_update_latlng(point.lat(), point.lng());
      });
    }
  }

  /**
   *  [Map: Google] Center the map, showing all markers attached to this map.
   *
   *  @since 1.0.0
   */
  function acadp_google_center_map(map) {
    // vars
    var bounds = new google.maps.LatLngBounds();

    // loop through all markers and create bounds
    $.each(map.markers, function (i, marker) {
      var latlng = new google.maps.LatLng(
        marker.position.lat(),
        marker.position.lng()
      );
      bounds.extend(latlng);
    });

    // only 1 marker?
    if (1 == map.markers.length) {
      // set center of map
      map.setCenter(bounds.getCenter());
      map.setZoom(parseInt(acadp.zoom_level));
    } else {
      // fit to bounds
      map.fitBounds(bounds);
    }
  }

  /**
   *  Set the latitude and longitude values from the address.
   *
   *  @since 1.0.0
   */
  function acadp_update_latlng(lat, lng) {
    $("#acadp-latitude").val(lat);
    $("#acadp-longitude").val(lng);
  }

  /**
   *  Make images inside the listing form sortable.
   *
   *  @since 1.0.0
   */
  function acadp_sort_images() {
    if ($.fn.sortable) {
      var $sortable_element = $("#acadp-images tbody");

      if ($sortable_element.hasClass("ui-sortable")) {
        $sortable_element.sortable("destroy");
      }

      $sortable_element.sortable({
        handle: ".acadp-handle",
      });

      $sortable_element.disableSelection();
    }
  }

  /**
   *  Check if the user have permission to upload image.
   *
   *  @since  1.0.0
   *  @return bool  True if can upload image, false if not.
   */
  function acadp_can_upload_image() {
    var limit = acadp_images_limit();
    var uploaded = acadp_images_uploaded_count();

    if (
      (limit > 0 && uploaded >= limit) ||
      $("#acadp-progress-image-upload").hasClass("uploading")
    ) {
      return false;
    }

    return true;
  }

  /**
   *  Get the maximum number of images the user can upload in the current listing.
   *
   *  @since  1.5.8
   *  @return int   Number of images.
   */
  function acadp_images_limit() {
    var limit = $("#acadp-upload-image").attr("data-limit");

    if (typeof limit !== typeof undefined && limit !== false) {
      limit = parseInt(limit);
    } else {
      limit = parseInt(acadp.maximum_images_per_listing);
    }

    return limit;
  }

  /**
   *  Get the number of images user had uploaded for the current listing.
   *
   *  @since  1.5.8
   *  @return int   Number of images.
   */
  function acadp_images_uploaded_count() {
    return $(".acadp-image-field").length;
  }

  /**
   *  Enable or disable image upload
   *
   *  @since 1.0.0
   */
  function acadp_enable_disable_image_upload() {
    if (acadp_can_upload_image()) {
      $("#acadp-upload-image").removeAttr("disabled");
    } else {
      $("#acadp-upload-image").attr("disabled", "disabled");
    }
  }

  /**
   * Check if value is empty.
   *
   * @since 1.8.0
   */
  function acadp_is_empty(value) {
    if ("" == value || 0 == value || null == value) {
      return true;
    }

    return false;
  }

  /**
   * Called when the page has loaded.
   *
   * @since 1.0.0
   */
  $(function () {
    // load custom fields of the selected category in the search form
    $("body").on("change", ".acadp-category-search", function () {
      var $search_elem = $(this)
        .closest("form")
        .find(".acadp-custom-fields-search");

      if ($search_elem.length) {
        $search_elem.html('<div class="acadp-spinner"></div>');

        var data = {
          action: "acadp_custom_fields_search",
          term_id: $(this).val(),
          style: $search_elem.data("style"),
          security: acadp.ajax_nonce,
        };

        $.post(acadp.ajax_url, data, function (response) {
          $search_elem.html(response);
        });
      }
    });

    // add "required" attribute to the category field in the listing form [fallback for versions prior to 1.5.5]
    $("#acadp_category").attr("required", "required");

    // load custom fields of the selected category in the custom post type "acadp_listings"
    $("body").on("change", ".acadp-category-listing", function () {
      $(".acadp-listing-form-submit-btn").prop("disabled", true);
      $("#acadp-custom-fields-listings").html(
        '<div class="acadp-spinner"></div>'
      );

      var data = {
        action: "acadp_public_custom_fields_listings",
        post_id: $("#acadp-custom-fields-listings").data("post_id"),
        terms: $(this).val(),
        security: acadp.ajax_nonce,
      };

      $.post(acadp.ajax_url, data, function (response) {
        $("#acadp-custom-fields-listings").html(response);
        $(".acadp-listing-form-submit-btn").prop("disabled", false);
      });
    });

    // slick slider
    if ($.fn.slick) {
      var $carousel = $(".acadp-slider-for")
        .on("init", function (slick) {
          $(this).fadeIn(1000);
        })
        .slick({
          rtl: parseInt(acadp.is_rtl) ? true : false,
          lazyLoad: "ondemand",
          asNavFor: ".acadp-slider-nav",
          arrows: false,
          fade: true,
          slidesToShow: 1,
          slidesToScroll: 1,
          adaptiveHeight: true,
        });

      if ($.fn.magnificPopup) {
        // magnific popup
        $carousel.magnificPopup({
          type: "image",
          delegate: "div:not(.slick-cloned) img",
          gallery: {
            enabled: true,
          },
          callbacks: {
            elementParse: function (item) {
              item.src = item.el.attr("src");
            },
            open: function () {
              var current = $carousel.slick("slickCurrentSlide");
              $carousel.magnificPopup("goTo", current);
            },
            beforeClose: function () {
              $carousel.slick("slickGoTo", parseInt(this.index));
            },
          },
        });
      }

      $(".acadp-slider-nav")
        .on("init", function (slick) {
          $(this).fadeIn(1000);
        })
        .slick({
          rtl: parseInt(acadp.is_rtl) ? true : false,
          lazyLoad: "ondemand",
          asNavFor: ".acadp-slider-for",
          nextArrow:
            '<div class="acadp-slider-next"><span class="glyphicon glyphicon-menu-right" aria-hidden="true"></span></div>',
          prevArrow:
            '<div class="acadp-slider-prev"><span class="glyphicon glyphicon-menu-left" aria-hidden="true"></span></div>',
          focusOnSelect: true,
          slidesToShow: 5,
          slidesToScroll: 1,
          infinite: false,
          responsive: [
            {
              breakpoint: 1024,
              settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
              },
            },
            {
              breakpoint: 600,
              settings: {
                slidesToShow: 2,
                slidesToScroll: 1,
              },
            },
          ],
        });
    }

    // magnific popup
    if ($.fn.magnificPopup) {
      $(".acadp-image-popup").magnificPopup({
        type: "image",
      });
    }

    // render map in the custom post type "acadp_listings"
    $(".acadp-map").each(function () {
      if ("osm" == acadp.map_service) {
        acadp_osm_render_map($(this));
      } else {
        acadp_google_render_map($(this));
      }
    });

    // display the media uploader when "Upload Image" button clicked in the custom post type "acadp_listings"
    $("#acadp-upload-image").on("click", function (e) {
      e.preventDefault();

      //Get the category ID
      category = $("input[name='acadp_category']").val();

      //If file is public
      if (category == PUBLIC_FILE) {
        if (
          confirm(
            "Each file uploaded cannot be deleted. To update the file, you need to reupload. Do you wish to proceed?"
          )
        ) {
          //Open media uploader to choose files
          if (acadp_can_upload_image()) {
            $("#acadp-upload-image-hidden").trigger("click");
          }
        }

        //If file is private
      } else if (category == PRIVATE_FILE) {
        if (acadp_can_upload_image()) {
          $("#acadp-upload-image-hidden").trigger("click");
        }
      } else {
        alert("Please select category first");
      }
    });

    // upload image
    $("#acadp-upload-image-hidden").change(function () {
      var selected = $(this)[0].files.length;
      if (!selected) return false;

      //Mel: Display loading spinner
      $("#acadp-progress-image-upload")
        .addClass("uploading")
        .html('<div class="acadp-spinner"></div>');

      //Mel: Start. Upload files to IPFS via nft.storage
      let files = new FormData(); // you can consider this as 'data bag'

      //Loop through one or more files.
      for (var x = 0; x < selected; x++) {
        files.append("file", $("#acadp-upload-image-hidden").prop("files")[x]);

        //Mel: To add the function to generate SHA-256 hashes for the files
        var filename = $("#acadp-upload-image-hidden").prop("files")[x].name;

        var reader = new FileReader();
        reader.onload = function (ev) {
          console.log("File: ", filename);

          crypto.subtle
            .digest("SHA-256", ev.target.result)
            .then((hashBuffer) => {
              // Convert hex to hash, see https://developer.mozilla.org/en-US/docs/Web/API/SubtleCrypto/digest#converting_a_digest_to_a_hex_string
              const hashArray = Array.from(new Uint8Array(hashBuffer));
              const hashHex = hashArray
                .map((b) => b.toString(16).padStart(2, "0"))
                .join(""); // convert bytes to hex string
              console.log("Hash: ", hashHex);

              //To store the file hashes as hidden inputs
              $("#acadp-post-form").append(
                '<input type="hidden" id="hash[]" name="hash[]" value="' +
                  hashHex +
                  '" />'
              );
              $("#acadp-progress-image-upload").append(
                "File Hash: " + hashHex + "<br /><br />"
              );
            })
            .catch((ex) => console.error(ex));

          //Remove the loading spinner if file is private
          if (category == PRIVATE_FILE) {
            $("#acadp-progress-image-upload").removeClass("uploading").html("");
          }
        };
        reader.onerror = function (err) {
          console.error("Failed to read file", err);
        };
        reader.readAsArrayBuffer(
          $("#acadp-upload-image-hidden").prop("files")[x]
        );
        //Mel: End

        //To store the filenames as hidden inputs
        $("#acadp-post-form").append(
          '<input type="hidden" id="filename[]" name="filename[]" value="' +
            $("#acadp-upload-image-hidden").prop("files")[x].name +
            '" />'
        );
      }

      var url = "https://api.nft.storage/upload";

      var xhr = new XMLHttpRequest();
      xhr.open("POST", url);

      xhr.setRequestHeader(
        "Authorization",
        "Bearer " + nftStorageApiKey,
        "Content-Type",
        "multipart/form-data; boundary=---abcd---"
      );

      var ipfsResponse;

      xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
          console.log(xhr.status);
          console.log(xhr.responseText);

          ipfsResponse = JSON.parse(xhr.responseText);
          console.log("CID: " + ipfsResponse.value.cid);
          $("#acadp-progress-image-upload").removeClass("uploading").html("");
          $("#acadp-progress-image-upload").append(
            'Uploaded to IPFS: <a id="ipfsUri" href="https://ipfs.io/ipfs/' +
              encodeURIComponent(ipfsResponse.value.cid) +
              '">View File</a><br /><br /><input type="hidden" class="acadp-image-field" id="ipfs-cid" name="ipfs_cid" value="' +
              encodeURIComponent(ipfsResponse.value.cid) +
              '" />'
          );
        }
      };

      //Upload the file to IPFS is file is public
      if (category == PUBLIC_FILE) {
        xhr.send(files);
      }
      //Mel: End

      var limit = acadp_images_limit();
      var uploaded = acadp_images_uploaded_count();
      var remaining = limit - uploaded;
      if (limit > 0 && selected > remaining) {
        alert(acadp.upload_limit_alert_message.replace(/%d/gi, remaining));
        return false;
      }

      $("#acadp-progress-image-upload")
        .addClass("uploading")
        .html('<div class="acadp-spinner"></div>');
      acadp_enable_disable_image_upload();

      var options = {
        dataType: "json",
        url: acadp.ajax_url,
        success: function (json, statusText, xhr, $form) {
          // do extra stuff after submit
          //$("#acadp-progress-image-upload").removeClass("uploading").html(""); //Mel: 24/01/22. Removing this comment will cause the Upload Image button to be disabled after IPFS files are uploaded.

          $.each(json, function (key, value) {
            if (!value["error"]) {
              var html =
                '<tr class="acadp-image-row">' +
                '<td class="acadp-handle"><span class="glyphicon glyphicon-th-large"></span></td>' +
                "<td>" +
                // '<img src="' + value["url"] +  '" />' +  //Mel: 28/01/22
                '<input type="hidden" class="acadp-image-field" name="images[]" value="' +
                value["id"] +
                '" />' +
                "</td>" +
                "<td>" +
                //Mel: 28/01/22
                '<span class="acadp-image-url"><a href="' +
                value["url"] +
                '" />' +
                value["url"].split(/[\\/]/).pop() +
                "</a></span><br />" +
                //'<span class="acadp-image-url">' +
                //value["url"].split(/[\\/]/).pop() +
                //"</span><br />" +
                //'<a href="javascript:;" class="acadp-delete-image" data-attachment_id="' +
                //value["id"] +
                //'">' +
                //acadp.delete_label +
                "</a>" +
                "</td>" +
                "</tr>";
              $("#acadp-images").append(html);
            }
          });

          acadp_sort_images();
          acadp_enable_disable_image_upload();
        },
        error: function (data) {
          $("#acadp-progress-image-upload").removeClass("uploading").html("");
          acadp_enable_disable_image_upload();
        },
      };

      // submit form using 'ajaxSubmit'
      $("#acadp-form-upload").ajaxSubmit(options);
    });

    //Mel: Start 07/11/21
    /**
     *  Upload file and make files inside the listing form sortable.
     *
     *  @since 1.0.0
     */
    function acadp_sort_files() {
      if ($.fn.sortable) {
        var $sortable_element = $("#acadp-files tbody");

        if ($sortable_element.hasClass("ui-sortable")) {
          $sortable_element.sortable("destroy");
        }

        $sortable_element.sortable({
          handle: ".acadp-handle",
        });

        $sortable_element.disableSelection();
      }
    }

    // Display the media uploader when "Upload File" button clicked in the custom post type "acadp_listings"
    $("#acadp-upload-file").on("click", function (e) {
      e.preventDefault();

      if (acadp_can_upload_image()) {
        $("#acadp-upload-file-hidden").trigger("click");
      }
    });

    // Upload file
    $("#acadp-upload-file-hidden").change(function () {
      var selected = $(this)[0].files.length;
      if (!selected) return false;

      var limit = acadp_images_limit();
      var uploaded = acadp_images_uploaded_count();
      var remaining = limit - uploaded;
      if (limit > 0 && selected > remaining) {
        alert(acadp.upload_limit_alert_message.replace(/%d/gi, remaining));
        return false;
      }

      $("#acadp-progress-file-upload")
        .addClass("uploading")
        .html('<div class="acadp-spinner"></div>');
      acadp_enable_disable_image_upload();

      var options2 = {
        dataType: "json",
        url: acadp.ajax_url,
        success: function (json, statusText, xhr, $form) {
          // do extra stuff after submit
          $("#acadp-progress-file-upload").removeClass("uploading").html("");

          $.each(json, function (key, value) {
            if (!value["error"]) {
              var html =
                '<tr class="acadp-file-row">' +
                '<td class="acadp-handle"><span class="glyphicon glyphicon-th-large"></span></td>' +
                '<td class="acadp-file">' +
                //'<img src="' + value['url'] + '" />' +
                '<input type="hidden" class="acadp-file-field" name="files[]" value="' +
                value["id"] +
                '" />' +
                "</td>" +
                "<td>" +
                '<span class="acadp-file-url"><a href="' +
                value["url"] +
                '" />' +
                value["url"].split(/[\\/]/).pop() +
                "</a></span><br />" +
                '<a href="javascript:;" class="acadp-delete-file" data-attachment_id="' +
                value["id"] +
                '">' +
                acadp.delete_label +
                "</a>" +
                "</td>" +
                "</tr>";
              $("#acadp-files").append(html);
            }
          });

          acadp_sort_files();
          acadp_enable_disable_image_upload();
        },
        error: function (data) {
          $("#acadp-progress-file-upload").removeClass("uploading").html("");
          acadp_enable_disable_image_upload();
        },
      };

      // submit form using 'ajaxSubmit'
      $("#acadp-form-upload2").ajaxSubmit(options2);
    });

    // make the isting images sortable in the custom post type "acadp_listings"
    acadp_sort_files();

    // Delete the selected file when "Delete Permanently" button clicked in the custom post type "acadp_listings"
    $("#acadp-files").on("click", "a.acadp-delete-file", function (e) {
      e.preventDefault();

      var $this = $(this);

      var data = {
        action: "acadp_public_delete_attachment_listings",
        attachment_id: $this.data("attachment_id"),
        security: acadp.ajax_nonce,
      };

      $.post(acadp.ajax_url, data, function (response) {
        $this.closest("tr").remove();
        $("#acadp-upload-file-hidden").val("");
        acadp_enable_disable_image_upload();
      });
    });

    //Mel: End

    // make the listing images sortable in the custom post type "acadp_listings"
    acadp_sort_images();

    // Delete the selected image when "Delete Permanently" button clicked in the custom post type "acadp_listings"
    $("#acadp-images").on("click", "a.acadp-delete-image", function (e) {
      e.preventDefault();

      var $this = $(this);

      var data = {
        action: "acadp_public_delete_attachment_listings",
        attachment_id: $this.data("attachment_id"),
        security: acadp.ajax_nonce,
      };

      $.post(acadp.ajax_url, data, function (response) {
        $this.closest("tr").remove();
        $("#acadp-upload-image-hidden").val("");
        acadp_enable_disable_image_upload();
      });
    });

    // Toggle password fields in user account form
    $("#acadp-change-password", "#acadp-user-account")
      .on("change", function () {
        var $checked = $(this).is(":checked");

        if ($checked) {
          $(".acadp-password-fields", "#acadp-user-account")
            .show()
            .find('input[type="password"]')
            .attr("disabled", false);
        } else {
          $(".acadp-password-fields", "#acadp-user-account")
            .hide()
            .find('input[type="password"]')
            .attr("disabled", "disabled");
        }
      })
      .trigger("change");

    // Validate ACADP forms
    if ($.fn.validator) {
      // Validate login, forgot password, password reset, user account forms
      var acadp_login_submitted = false;

      $(
        "#acadp-login-form, #acadp-forgot-password-form, #acadp-password-reset-form, #acadp-user-account"
      )
        .validator({
          disable: false,
        })
        .on("submit", function (e) {
          if (acadp_login_submitted) {
            return false;
          }

          acadp_login_submitted = true;

          // Check for errors
          if (e.isDefaultPrevented()) {
            acadp_login_submitted = false; // Re-enable the submit event
          }
        });

      // Validate registration form
      var acadp_register_submitted = false;

      $("#acadp-register-form")
        .validator({
          disable: false,
        })
        .on("submit", function (e) {
          if (acadp_register_submitted) {
            return false;
          }

          acadp_register_submitted = true;

          // Check for errors
          var error = 1;

          if (!e.isDefaultPrevented()) {
            error = 0;

            if (acadp.recaptcha_registration > 0) {
              var response = grecaptcha.getResponse(
                acadp.recaptchas["registration"]
              );

              if (0 == response.length) {
                $("#acadp-registration-g-recaptcha-message")
                  .addClass("text-danger")
                  .html(acadp.recaptcha_invalid_message);
                grecaptcha.reset(acadp.recaptchas["registration"]);

                error = 1;
              }
            }
          }

          if (error) {
            acadp_register_submitted = false; // Re-enable the submit event
            return false;
          }
        });

      // Validate listing form
      var acadp_listing_submitted = false;

      $("#acadp-post-form")
        .validator({
          custom: {
            cb_required: function ($el) {
              var class_name = $el.data("cb_required");
              return $("input." + class_name + ":checked").length > 0
                ? true
                : false;
            },
          },
          errors: {
            cb_required: "You must select at least one option.",
          },
          disable: false,
        })
        .on("submit", function (e) {
          if (acadp_listing_submitted) {
            return false;
          }

          acadp_listing_submitted = true;

          // Check for errors
          var error = 1;

          if (!e.isDefaultPrevented()) {
            error = 0;

            if (acadp.recaptcha_listing > 0) {
              var response = grecaptcha.getResponse(
                acadp.recaptchas["listing"]
              );

              if (0 == response.length) {
                $("#acadp-listing-g-recaptcha-message")
                  .addClass("text-danger")
                  .html(acadp.recaptcha_invalid_message);
                grecaptcha.reset(acadp.recaptchas["listing"]);

                error = 1;
              }
            }
          }

          if (error) {
            $("#acadp-post-errors").show();

            $("html, body").animate(
              {
                scrollTop: $("#acadp-post-form").offset().top - 50,
              },
              500
            );

            acadp_listing_submitted = false; // Re-enable the submit event

            return false;
          } else {
            $("#acadp-post-errors").hide();
          }

          if (category == PUBLIC_FILE) {
            //Mel: Save a metadata json file of the post and then upload it to IPFS
            e.preventDefault();

            //Mel: Spins the spinner and disable submit btn
            $("#loading").html('<span class="acadp-spinner"></span>');
            $("#submit-btn").prop("disabled", true);

            // Get filenames data as array, ['Jon', 'Mike']
            var filenames = $('input[name="filename[]"]')
              .map(function () {
                return this.value;
              })
              .get();

            //If file has not been uploaded to IPFS then pops an alert.
            if (!$("#ipfs-cid").length) {
              $("#noFileModal").modal("show");
              $("#loading").html("");
              $("#submit-btn").removeAttr("disabled");
            } else {
              var data = {
                action: "acadp_public_save_metadata",
                ipfs_cid: document.getElementById("ipfs-cid").value,
                filenames: filenames,
                name: document.getElementById("acadp-title").value,
                description: document.getElementById("description").value,
                security: acadp.ajax_nonce,
              };

              $.post(acadp.ajax_url, data, function (response) {
                //console.log("Metadata file: " + response);

                const metadataFilename = response.substring(
                  0,
                  response.length - 1
                );

                console.log("Metadata file: " + metadataFilename);

                fetch("/wp-content/uploads/metadata/" + metadataFilename)
                  .then((res) => res.blob())
                  .then((blob) => {
                    let fd = new FormData();

                    const file = new File([blob], "metadata.json", {
                      type: "application/json",
                    });

                    fd.append("file", file);

                    var url = "https://api.nft.storage/upload";

                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", url);

                    xhr.setRequestHeader(
                      "Authorization",
                      "Bearer " + nftStorageApiKey,
                      "Content-Type",
                      "multipart/form-data; boundary=---abcd---"
                    );

                    var ipfsResponse;

                    xhr.onreadystatechange = function () {
                      if (xhr.readyState === 4) {
                        console.log(xhr.status);
                        console.log(xhr.responseText);

                        ipfsResponse = JSON.parse(xhr.responseText);
                        console.log("CID: " + ipfsResponse.value.cid);
                        $("#acadp-progress-image-upload")
                          .removeClass("uploading")
                          .html("");

                        //To store the metadata file as hidden inputs
                        $("#acadp-post-form").append(
                          '<input type="hidden" name="ipfs_metadata_cid" value="' +
                            encodeURIComponent(ipfsResponse.value.cid) +
                            '" />'
                        );
                        //Finally, if all is ok, go submit the form
                        $("#acadp-post-form")[0].submit();
                      }
                    };
                    xhr.send(fd);
                  });
              });
            }
            //Mel: End
          }
        });

      // Validate report abuse form
      var acadp_report_abuse_submitted = false;

      $("#acadp-report-abuse-form")
        .validator({
          disable: false,
        })
        .on("submit", function (e) {
          if (acadp_report_abuse_submitted) {
            return false;
          }

          acadp_report_abuse_submitted = true;

          // Check for errors
          if (!e.isDefaultPrevented()) {
            e.preventDefault();

            var response = "";

            if (acadp.recaptcha_report_abuse > 0) {
              response = grecaptcha.getResponse(
                acadp.recaptchas["report_abuse"]
              );

              if (0 == response.length) {
                $("#acadp-report-abuse-message-display")
                  .addClass("text-danger")
                  .html(acadp.recaptcha_invalid_message);
                grecaptcha.reset(acadp.recaptchas["report_abuse"]);

                acadp_report_abuse_submitted = false; // Re-enable the submit event
                return false;
              }
            }

            // Post via AJAX
            var data = {
              action: "acadp_public_report_abuse",
              post_id: $("#acadp-post-id").val(),
              message: $("#acadp-report-abuse-message").val(),
              "g-recaptcha-response": response,
              security: acadp.ajax_nonce,
            };

            $.post(
              acadp.ajax_url,
              data,
              function (response) {
                if (1 == response.error) {
                  $("#acadp-report-abuse-message-display")
                    .addClass("text-danger")
                    .html(response.message);
                } else {
                  $("#acadp-report-abuse-message").val("");
                  $("#acadp-report-abuse-message-display")
                    .addClass("text-success")
                    .html(response.message);
                }

                if (acadp.recaptcha_report_abuse > 0) {
                  grecaptcha.reset(acadp.recaptchas["report_abuse"]);
                }

                acadp_report_abuse_submitted = false; // Re-enable the submit event
              },
              "json"
            );
          }
        });

      // Validate contact form
      var acadp_contact_submitted = false;

      $("#acadp-contact-form")
        .validator({
          disable: false,
        })
        .on("submit", function (e) {
          if (acadp_contact_submitted) return false;

          // Check for errors
          if (!e.isDefaultPrevented()) {
            e.preventDefault();

            acadp_contact_submitted = true;
            var response = "";

            if (acadp.recaptcha_contact > 0) {
              response = grecaptcha.getResponse(acadp.recaptchas["contact"]);

              if (0 == response.length) {
                $("#acadp-contact-message-display")
                  .addClass("text-danger")
                  .html(acadp.recaptcha_invalid_message);
                grecaptcha.reset(acadp.recaptchas["contact"]);

                acadp_contact_submitted = false; // Re-enable the submit event
                return false;
              }
            }

            $("#acadp-contact-message-display").append(
              '<div class="acadp-spinner"></div>'
            );

            // Post via AJAX
            var data = {
              action: "acadp_public_send_contact_email",
              post_id: $("#acadp-post-id").val(),
              name: $("#acadp-contact-name").val(),
              email: $("#acadp-contact-email").val(),
              message: $("#acadp-contact-message").val(),
              "g-recaptcha-response": response,
              security: acadp.ajax_nonce,
            };

            if ($("#acadp-contact-phone").length > 0) {
              data.phone = $("#acadp-contact-phone").val();
            }

            $.post(
              acadp.ajax_url,
              data,
              function (response) {
                if (1 == response.error) {
                  $("#acadp-contact-message-display")
                    .addClass("text-danger")
                    .html(response.message);
                } else {
                  $("#acadp-contact-message").val("");
                  $("#acadp-contact-message-display")
                    .addClass("text-success")
                    .html(response.message);
                }

                if (acadp.recaptcha_contact > 0) {
                  grecaptcha.reset(acadp.recaptchas["contact"]);
                }

                acadp_contact_submitted = false; // Re-enable the submit event
              },
              "json"
            );
          } else {
            acadp_contact_submitted = false;
          }
        });
    }

    // Report abuse [on modal closed]
    $("#acadp-report-abuse-modal").on("hidden.bs.modal", function (e) {
      $("#acadp-report-abuse-message").val("");
      $("#acadp-report-abuse-message-display").html("");
    });

    // Contact form [on modal closed]
    $("#acadp-contact-modal").on("hidden.bs.modal", function (e) {
      $("#acadp-contact-message").val("");
      $("#acadp-contact-message-display").html("");
    });

    // Add or Remove from favourites
    $("#acadp-favourites").on("click", "a.acadp-favourites", function (e) {
      e.preventDefault();

      var $this = $(this);

      var data = {
        action: "acadp_public_add_remove_favorites",
        post_id: $this.data("post_id"),
        security: acadp.ajax_nonce,
      };

      $.post(acadp.ajax_url, data, function (response) {
        $("#acadp-favourites").html(response);
      });
    });

    // Alert users to login (only if applicable)
    $(".acadp-require-login").on("click", function (e) {
      e.preventDefault();
      alert(acadp.user_login_alert_message);
    });

    // Calculate and update total amount in the checkout form
    // $(".acadp-checkout-fee-field")
    //   .on("change", function () {
    //     var total_amount = 0,
    //       fee_fields = 0;

    //     //These two codes are used to reset the amount and payment gateway selections if user restarts the donation process
    //     $("#currency").text("$");
    //     $(".payment_gateway_method").removeAttr("checked");

    //     $(
    //       "#acadp-checkout-form-data input[type='checkbox']:checked, #acadp-checkout-form-data input[type='radio']:checked"
    //     ).each(function () {
    //       total_amount += parseFloat($(this).data("price"));
    //       ++fee_fields;
    //     });

    //     $("#acadp-checkout-total-amount").html(
    //       '<div class="acadp-spinner"></div>'
    //     );

    //     if (0 == fee_fields) {
    //       $("#acadp-checkout-total-amount").html("0.00");
    //       $(
    //         "#acadp-payment-gateways, #acadp-cc-form, #acadp-checkout-submit-btn"
    //       ).hide();
    //       return;
    //     }

    //     var data = {
    //       action: "acadp_checkout_format_total_amount",
    //       amount: total_amount,
    //       security: acadp.ajax_nonce,
    //     };

    //     $.post(acadp.ajax_url, data, function (response) {
    //       $("#acadp-checkout-total-amount").html(response);

    //       var amount = parseFloat($("#acadp-checkout-total-amount").html());

    //       if (amount > 0) {
    //         $("#acadp-payment-gateways, #acadp-cc-form").show();
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.proceed_to_payment_btn_label)
    //           .show();
    //       } else {
    //         //$( '#acadp-payment-gateways, #acadp-cc-form' ).hide(); //Mel: Prevent hiding
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.finish_submission_btn_label)
    //           .show();
    //       }
    //     });
    //   })
    //   .trigger("change");

    //Mel: Calculate and update other amount in the checkout form
    // $(".acadp-checkout-fee-field-other")
    //   .on("input", function () {
    //     var other_amount = 0;

    //     $("#currency").text("$");

    //     $("#other").data("price", $(this).val());
    //     $(".acadp-checkout-fee-field-other").attr("data-price", $(this).val());
    //     //$('.acadp-checkout-fee-field-other').data( 'price', $(this).val() );

    //     other_amount = parseFloat($(this).val());
    //     //other_amount = parseFloat( $( this ).data('price') );

    //     $("#acadp-checkout-total-amount").html(
    //       '<div class="acadp-spinner"></div>'
    //     );

    //     var data = {
    //       action: "acadp_checkout_format_total_amount",
    //       amount: other_amount,
    //       security: acadp.ajax_nonce,
    //     };

    //     $.post(acadp.ajax_url, data, function (response) {
    //       $("#acadp-checkout-total-amount").html(response);

    //       var amount = parseFloat($("#acadp-checkout-total-amount").html());

    //       if (amount > 0) {
    //         $("#acadp-payment-gateways, #acadp-cc-form").show();
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.proceed_to_payment_btn_label)
    //           .show();
    //       } else {
    //         //$( '#acadp-payment-gateways, #acadp-cc-form' ).hide(); //Mel: Prevent hiding
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.finish_submission_btn_label)
    //           .show();
    //       }
    //     });
    //   })
    //   .trigger("input");

    //Mel: 28/12/21. To retrieve crypto price against USD (Tether) from Binance API
    // function getAmountInCrypto(crypto, amount) {
    //   var cryptoPrice = 0;
    //   var finalPrice = 0;

    //   crypto = crypto.toUpperCase();

    //   var burl = "https://api.binance.com";

    //   var query = "/api/v3/ticker/price";

    //   query += "?symbol=" + crypto + "USDT";

    //   var url = burl + query;

    //   var ourRequest = new XMLHttpRequest();

    //   ourRequest.open("GET", url, false);

    //   ourRequest.onload = function () {
    //     var status = ourRequest.status;

    //     if (status == 200) {
    //       var obj = JSON.parse(ourRequest.response);

    //       cryptoPrice = parseFloat(obj.price);
    //       console.log("Current " + crypto + "/USD Price: ", cryptoPrice);

    //       finalPrice = parseFloat((amount / cryptoPrice).toFixed(8));
    //       console.log("Final Amount (" + crypto + "): ", finalPrice);
    //     } else {
    //       finalPrice = 0;
    //     }
    //   };

    //   ourRequest.send();

    //   return finalPrice;
    // }

    //Mel: 28/12/21. Unused before we use Binance API
    //Mel. Get NFT price in ETH from Chainlink
    /* async function getEthPrice(retailFee) {
		
			var price = 0;
			var roundedPrice = 0;
			var ethPrice = 0;
			const contractAddr = '0x6A6332442095aca7bf0EAC1048334E06c6Ff968C';
			const web3 = new Web3('https://kovan.infura.io/v3/f2e537e744a14d3a9981ddec2ae859c9');
			const aggregatorV3InterfaceABI = [
				{
					"inputs": [],
					"stateMutability": "nonpayable",
					"type": "constructor"
				},
				{
					"inputs": [],
					"name": "getThePrice",
					"outputs": [
						{
							"internalType": "int256",
							"name": "",
							"type": "int256"
						}
					],
					"stateMutability": "view",
					"type": "function"
				}
			];
			
			const priceFeed = new web3.eth.Contract(aggregatorV3InterfaceABI, contractAddr);
			await priceFeed.methods.getThePrice().call()
				.then((roundData) => {
					price = parseFloat(roundData);
					roundedPrice = price/100000000;
					console.log("Current ETH/USD Price: ", roundedPrice);
					ethPrice = parseFloat( (retailFee/roundedPrice).toFixed(3) );
					console.log("NFT price in ETH: ", ethPrice);
					return ethPrice;
				});
			return ethPrice;
		} */

    //Mel: 11/11/21
    // Choose payment method, update currency and total amount in the checkout form
    // $(".payment_gateway_method")
    //   .on("change", function () {
    //     var total_amount = 0,
    //       fee_fields = 0;
    //     var retailPrice = 0;

    //     $(
    //       "#acadp-checkout-form-data input[type='checkbox']:checked, #acadp-checkout-form-data input[type='radio']:checked"
    //     ).each(function () {
    //       total_amount += parseFloat($(this).data("price"));
    //       ++fee_fields;

    //       retailPrice = parseFloat($(this).data("price"));
    //     });

    //     if ($("input[name='payment_gateway']:checked").val() == "bitcoin") {
    //       //Display currency as BTC
    //       $("#currency").text("BTC");

    //       //Grab amount that has been selected by user
    //       retailPrice = $("#acadp-checkout-total-amount").text();
    //       retailPrice = parseFloat(retailPrice.replace(/\D/g, "")); //Remove all non-numeric chars

    //       //Get price in BTC
    //       total_amount = getAmountInCrypto("btc", retailPrice);

    //       $("input[name='amount']").val(total_amount);
    //     } else if (
    //       $("input[name='payment_gateway']:checked").val() == "ethereum"
    //     ) {
    //       //Mel: 03/01/22
    //       //Display currency as ETH
    //       $("#currency").text("ETH");

    //       //Grab amount that has been selected by user
    //       retailPrice = $("#acadp-checkout-total-amount").text();
    //       retailPrice = parseFloat(retailPrice.replace(/\D/g, "")); //Remove all non-numeric chars

    //       //Get price in ETH
    //       total_amount = getAmountInCrypto("eth", retailPrice);

    //       $("input[name='amount']").val(total_amount);
    //       //Mel: End

    //       //Mel: 23/11/21
    //     } else if (
    //       $("input[name='payment_gateway']:checked").val() == "eth_matic"
    //     ) {
    //       //If user chooses to pay with ETH but receives NFT in Polygon, we offer a lower price since gas fee is lower.
    //       total_amount = 0.001;

    //       //Display currency as ETH
    //       $("#currency").text("ETH");

    //       //Mel: 26/11/21
    //     } else if (
    //       $("input[name='payment_gateway']:checked").val() == "eth_avax"
    //     ) {
    //       //If user chooses to pay with ETH but receives NFT in Avalanche, we offer a lower price since gas fee is lower.
    //       total_amount = 0.005;

    //       //Display currency as ETH
    //       $("#currency").text("ETH");
    //     } else {
    //       //Display currency as '$'
    //       $("#currency").text("$");

    //       retailPrice = $("#acadp-checkout-total-amount").text();
    //       retailPrice = parseFloat(retailPrice.replace(/\D/g, "")); //Remove all non-numeric chars

    //       $("input[name='amount']").val(retailPrice);
    //     }

    //     $("#acadp-checkout-total-amount").html(
    //       '<div class="acadp-spinner"></div>'
    //     );

    //     //if ( 0 == fee_fields ) {
    //     //$( '#acadp-checkout-total-amount' ).html( '0.00' );
    //     //$( '#acadp-payment-gateways, #acadp-cc-form, #acadp-checkout-submit-btn' ).hide();
    //     //return;
    //     //};

    //     var data = {
    //       action: "acadp_checkout_format_total_amount",
    //       amount: total_amount,
    //       security: acadp.ajax_nonce,
    //     };

    //     $.post(acadp.ajax_url, data, async function (response) {
    //       $("#acadp-checkout-total-amount").html(response);

    //       var amount = parseFloat($("#acadp-checkout-total-amount").html());

    //       if (amount > 0) {
    //         $("#acadp-payment-gateways, #acadp-cc-form").show();
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.proceed_to_payment_btn_label)
    //           .show();
    //       } else {
    //         $("#acadp-payment-gateways, #acadp-cc-form").hide();
    //         $("#acadp-checkout-submit-btn")
    //           .val(acadp.finish_submission_btn_label)
    //           .show();
    //       }
    //     });
    //   })
    //   .trigger("change");
    //Mel: End

    //Mel: 12/11/21
    // Redirect to receipt page after cypto payment is complete at checkout form
    /* 		$( '#payment-output' ).on( 'change', function() {	
			
			var data = {
				'action': 'acadp_checkout_format_total_amount',
				'amount': total_amount,
				'security': acadp.ajax_nonce
			};
			
			$.post( acadp.ajax_url, data, function( response ) {												   
				$( '#acadp-checkout-total-amount' ).html( response );
				
				var amount = parseFloat( $( '#acadp-checkout-total-amount' ).html() );
				
				if ( amount > 0 ) {
					$( '#acadp-payment-gateways, #acadp-cc-form' ).show();
					$( '#acadp-checkout-submit-btn' ).val( acadp.proceed_to_payment_btn_label ).show();
				} else {
					$( '#acadp-payment-gateways, #acadp-cc-form' ).hide();
					$( '#acadp-checkout-submit-btn' ).val( acadp.finish_submission_btn_label ).show();
				}				
			});			
		}).trigger( 'change' ); */

    // Validate checkout form
    // var acadp_checkout_submitted = false;

    // $("#acadp-checkout-form").on("submit", function () {
    //   if (acadp_checkout_submitted) {
    //     return false;
    //   }

    //   acadp_checkout_submitted = true;
    // });

    // Populate ACADP child terms dropdown
    $(".acadp-terms").on("change", "select", function (e) {
      e.preventDefault();

      var $this = $(this);
      var taxonomy = $this.data("taxonomy");
      var parent = $this.data("parent");
      var value = $this.val();
      var classes = $this.attr("class");

      $this.closest(".acadp-terms").find("input.acadp-term-hidden").val(value);
      $this.parent().find("div:first").remove();

      if (parent != value) {
        $this.parent().append('<div class="acadp-spinner"></div>');

        var data = {
          action: "acadp_public_dropdown_terms",
          taxonomy: taxonomy,
          parent: value,
          class: classes,
          security: acadp.ajax_nonce,
        };

        $.post(acadp.ajax_url, data, function (response) {
          $this.parent().find("div:first").remove();
          $this.parent().append(response);
        });
      }
    });

    // Show phone number
    $(".acadp-show-phone-number").on("click", function () {
      $(this).hide();
      $(".acadp-phone-number").show();
    });

    // Gutenberg: Refresh Map.
    if ("undefined" !== typeof wp && "undefined" !== typeof wp["hooks"]) {
      var acadp_block_interval;
      var acadp_block_interval_retry_count;

      wp.hooks.addFilter(
        "acadp_block_listings_init",
        "acadp/listings",
        function (attributes) {
          if ("map" === attributes.view) {
            if (acadp_block_interval_retry_count > 0) {
              clearInterval(acadp_block_interval);
            }
            acadp_block_interval_retry_count = 0;

            acadp_block_interval = setInterval(function () {
              acadp_block_interval_retry_count++;

              if (
                $(".acadp-map:not(.acadp-map-loaded)").length > 0 ||
                acadp_block_interval_retry_count >= 10
              ) {
                clearInterval(acadp_block_interval);
                acadp_block_interval_retry_count = 0;

                $(".acadp-map:not(.acadp-map-loaded)").each(function () {
                  if ("osm" == acadp.map_service) {
                    acadp_osm_render_map($(this));
                  } else {
                    acadp_google_render_map($(this));
                  }
                });
              }
            }, 1000);
          }
        }
      );
    }

    // WhatsApp Share
    $(".acadp-social-whatsapp").on("click", function () {
      if (
        /Android|webOS|iPhone|BlackBerry|IEMobile|Opera Mini/i.test(
          navigator.userAgent
        )
      ) {
        $(this).removeAttr("href");
        var article = jQuery(this).attr("data-text");
        var weburl = jQuery(this).attr("data-link");
        var whatsapp_message =
          encodeURIComponent(article) + " - " + encodeURIComponent(weburl);
        var whatsapp_url = "whatsapp://send?text=" + whatsapp_message;
        window.location.href = whatsapp_url;
      }
    });
  });
})(jQuery);

/**
 *  load reCAPTCHA explicitly.
 *
 *  @since 1.0.0
 */
var acadp_on_recaptcha_load = function () {
  if ("" != acadp.recaptcha_site_key) {
    // Add reCAPTCHA in registration form
    if (jQuery("#acadp-registration-g-recaptcha").length) {
      if (acadp.recaptcha_registration > 0) {
        acadp.recaptchas["registration"] = grecaptcha.render(
          "acadp-registration-g-recaptcha",
          {
            sitekey: acadp.recaptcha_site_key,
          }
        );

        jQuery("#acadp-registration-g-recaptcha").addClass(
          "acadp-margin-bottom"
        );
      }
    } else {
      acadp.recaptcha_registration = 0;
    }

    // Add reCAPTCHA in listing form
    if (jQuery("#acadp-listing-g-recaptcha").length) {
      if (acadp.recaptcha_listing > 0) {
        acadp.recaptchas["listing"] = grecaptcha.render(
          "acadp-listing-g-recaptcha",
          {
            sitekey: acadp.recaptcha_site_key,
          }
        );

        jQuery("#acadp-listing-g-recaptcha").addClass("acadp-margin-bottom");
      }
    } else {
      acadp.recaptcha_listing = 0;
    }

    // Add reCAPTCHA in contact form
    if (jQuery("#acadp-contact-g-recaptcha").length) {
      if (acadp.recaptcha_contact > 0) {
        acadp.recaptchas["contact"] = grecaptcha.render(
          "acadp-contact-g-recaptcha",
          {
            sitekey: acadp.recaptcha_site_key,
          }
        );
      }
    } else {
      acadp.recaptcha_contact = 0;
    }

    // Add reCAPTCHA in report abuse form
    if (jQuery("#acadp-report-abuse-g-recaptcha").length) {
      if (acadp.recaptcha_report_abuse > 0) {
        acadp.recaptchas["report_abuse"] = grecaptcha.render(
          "acadp-report-abuse-g-recaptcha",
          {
            sitekey: acadp.recaptcha_site_key,
          }
        );
      }
    } else {
      acadp.recaptcha_report_abuse = 0;
    }

    // Custom Event for developers (suggested by Paul for his "Site Reviews" plugin)
    document.dispatchEvent(new CustomEvent("acadp_on_recaptcha_load"));
  }
};

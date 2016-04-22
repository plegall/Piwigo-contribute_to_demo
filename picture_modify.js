jQuery().ready(function() {
  jQuery(".ctd_submit a").click(function(e){
    if (jQuery(".contrib").data('width') < 2000 && jQuery(".contrib").data('height') < 2000) {
      alert("photo too small, must be at least 2000 pixels");
      return false;
    }

    var $loading = jQuery(".contrib .loading");
    $loading.show();

    var image_id = jQuery(".contrib").data('id');

    jQuery.ajax({
      url: "ws.php?format=json&method=contrib.photo.submit",
      type:"POST",
      data: {
        image_id : image_id,
      },
      success:function(data) {
        var data = jQuery.parseJSON(data);
        if (data.stat == 'ok') {
          console.log("contribution registered");
          $loading.hide();
          jQuery(".ctd_submit").hide();
          jQuery(".ctd_pending").show();
          jQuery(".ctd_remove").show();
        }
        else {
          console.log("contribution registration failed");
        }
      },
      error:function(XMLHttpRequest, textStatus, errorThrows) {
        alert("error calling local registration");
      }
    });

    e.preventDefault();
  });

  jQuery(".ctd_see a").click(function() {
    var base_url = jQuery(".contrib").data("demo_url");
    var uuid = jQuery(".contrib").data("uuid");

    var url_in_demo = base_url+"/index.php?/contrib/"+uuid;

    jQuery(this).attr("href", url_in_demo);
  });

  jQuery(".ctd_remove a").click(function(e) {
    var $loading = jQuery(".contrib .loading");
    $loading.show();

    var image_id = jQuery(".contrib").data('id');

    jQuery.ajax({
      url: "ws.php?format=json&method=contrib.photo.remove",
      type:"POST",
      data: {
        image_id : image_id,
      },
      success:function(data) {
        var data = jQuery.parseJSON(data);
        if (data.stat == 'ok') {
          jQuery(".contrib").data("uuid", null);
          console.log("removal registered");
          $loading.hide();
          jQuery(".ctd_submit").show();
          jQuery(".ctd_remove").hide();
          jQuery(".ctd_see").hide();
          jQuery(".ctd_pending").hide();
        }
        else {
          console.log("contribution removal failed");
        }
      },
      error:function(XMLHttpRequest, textStatus, errorThrows) {
        alert("error calling local removal");
      }
    });

    e.preventDefault();
  });

});

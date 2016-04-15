jQuery().ready(function() {
  jQuery(".ctd_submit a").click(function(e){
    var $loading = jQuery(".contrib .loading");
    $loading.show();

    var image_id = jQuery(".contrib").data('id');

    jQuery.ajax({
      url: jQuery(".contrib").data('demo_url')+"/ws.php?format=json&method=contrib.photo.submit",
      type:"POST",
      data: {
        file : jQuery(".contrib").data('file'),
        name : jQuery(".contrib").data('name'),
        piwigo_url : jQuery(".contrib").data('url'),
        piwigo_relative_path : jQuery(".contrib").data('path'),
        piwigo_image_id : image_id,
      },
      success:function(data) {
        var data = jQuery.parseJSON(data);
        if (data.stat == 'ok') {
          console.log("submission succes, uuid="+data.result.uuid);
          jQuery(".contrib").data("uuid", data.result.uuid);

          // sub AJAX request, this time we call the Piwigo itself, not the demo
          jQuery.ajax({
            url: "ws.php?format=json&method=contrib.photo.submitted",
            type:"POST",
            data: {
              image_id : image_id,
              uuid : data.result.uuid,
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
        }
        else {
          console.log("not submitted");
        }
      },
      error:function(XMLHttpRequest, textStatus, errorThrows) {
        alert("error calling Piwigo demo");
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

});

jQuery(document).ready( function() {

   var interest_selected = [];
   var access_token;

   jQuery(document).on('click', '#next-step', function(e) {
     e.preventDefault();

     access_token = jQuery("#form-field-access_token").val();

     jQuery('#result').empty();

     jQuery('#step-head').text('2 - Select some Suggested Interests and Affine your Search');

     jQuery.ajax({
       type : "post",
       dataType : "json",
       url : myAjax.ajaxurl,
       data : {action: 'get_suggested_int', interest_selected : interest_selected, access_token : access_token },
       success : function(response){
         if(response.type == "success"){
           jQuery("#result").append(response.render_data);
         }
       }
     });
   });

   jQuery(document).on('click', '#save-audience', function (e) {

      jQuery.ajax({
        type: 'post',
        dataType: 'json',
        url: myAjax.ajaxurl,
        data: {action: 'save_custom_audiance', nonce: nonce, data: data},
        success: function(response){
          if(response.type == 'success'){
            jQuery("#result").append(response.render_data);
          }
        }
      });

   });


   jQuery("#search_the_power").click( function(e) {
      jQuery('#result').empty();
      e.preventDefault();
      search_term = jQuery("#form-field-interest_key").val();
      //access_token = jQuery("#form-field-access_token").val();
			//alert(search_term);
      if (document.cookie.indexOf(search_term + "=") >= 0){
        value = getCookie(search_term);
        alert(search_term + value);
         canVote = false;
        } else {
        // set a new cookie
        jQuery.ajax({
           type : "post",
           dataType : "json",
           url : myAjax.ajaxurl,
           data : {action: "get_interest", search_term : search_term, access_token : access_token},
           success: function(response) {
              if(response.type == "success") {
                jQuery("#result").append(response.render_data);
                expiry = new Date();
                expiry.setTime(Date.now()+(5000)); // 1000 days
                document.cookie = "search-" + search_term + "; expires=" + expiry.toGMTString() + ";";
                canVote = true;
              }else {
                 alert("You can perform a search every 5 sec");
              }
           }
        });
    }
   });


    //aggiunge l'interesse nell'array e nella lista html
   jQuery(document).on('click', '.add_interest', function(e){
     e.preventDefault();
     //console.log('done');
     int_name = jQuery(this).attr('data-int_name');
     int_id = jQuery(this).attr('data-id');
     int_topic = jQuery(this).attr('data-int_topic');
     int_size = jQuery(this).attr('data-int_size');
     //console.log(int_id + ' - ' + int_name);


     interest_selected.push({
       'name': int_name,
       'id': int_id,
       'topic': int_topic,
       'size': int_size
     });
     //console.log(interest_selected);
     jQuery('#details').append('<div class="remove remove-' + int_id + '"><i class="fas fa-times-circle" id="' + int_id + '"></i> ' + int_name + '</div>');
     jQuery(this).attr('disabled', true);
   });


   //rimuove l'interesse nella'array e dalla lista html
   jQuery(document).on('click', '.fa-times-circle', function(e){
     e.preventDefault();
     int_id = jQuery(this).attr('id');
     jQuery('.remove-' + int_id).remove();
     //console.log(int_id );
     interest_selected.splice( interest_selected.indexOf({'id': int_id}), 1);
     jQuery('#btn-' + int_id).attr('disabled', false);
     console.log(interest_selected);
   });
   window.fbAsyncInit = function() {
     FB.init({
       appId      : "2499504206937886",
       cookie     : true,
       xfbml      : true,
       version    : "v5.0"
     });

     FB.AppEvents.logPageView();
 		jQuery(document).trigger('fbload');

   };

   (function(d, s, id){
      var js, fjs = d.getElementsByTagName(s)[0];
      if (d.getElementById(id)) {return;}
      js = d.createElement(s); js.id = id;
      js.src = "https://connect.facebook.net/en_US/sdk.js";
      fjs.parentNode.insertBefore(js, fjs);
    }(document, "script", "facebook-jssdk"));


   jQuery(document).on(
     'fbload',
     function(){
       FB.getLoginStatus(function(response) {
     		statusChangeCallback(response);
     		console.log(response.status);

     	 });
     }
   );
   function statusChangeCallback(response) {
                 console.log('statusChangeCallback');
                 console.log(response);
                 // The response object is returned with a status field that lets the
                 // app know the current login status of the person.
                 // Full docs on the response object can be found in the documentation
                 // for FB.getLoginStatus().
                 if (response.status === 'connected') {
                     // Logged into your app and Facebook.
                     console.log('Welcome!  Fetching your information.... ');
                     FB.api('/me', function (response) {
                         console.log('Successful login for: ' + response.name);
                         document.getElementById('status').innerHTML =
                           'Welcome back, ' + response.name + '!';
                         jQuery('#login').empty();
                         access_token = FB.getAccessToken();
                         jQuery("#form-field-access_token").val(access_token);
                     });

                 } else {
                     // The person is not logged into your app or we are unable to tell.
                     //document.getElementById('status').innerHTML = 'Please log ' + 'into this app.';
                 }
             }


 });

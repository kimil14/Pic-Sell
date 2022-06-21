const {__, _n, _x, sprintf} = wp.i18n;

jQuery(function ($) {
  "use strict"


  /**
   * FN
   */
   $.fn.slideUpRemove = function (d, c) {
    var _this = this,
        duration = typeof d === 'number' ? d : 400,
        callback = typeof c === 'function' ? c : d;

    return this.slideUp(duration).promise().done(function () {
        _this.remove();
        if (typeof callback === 'function') callback.call();
    });
};
$.fn.extend({
	editable: function () {
		$(this).each(function () {

      var $cart = $(this).parent().parent();

			var $el = $(this),
      $input = $el.data("input"),
		//	$edittextbox = $('<input type="text"></input>').css('min-width', $el.width()),
      $edittextbox = $cart.find($input),
			submitChanges = function () {
       console.log($edittextbox);
				if ($edittextbox.val() !== '') {
         // $(select_choice_cat).find("option:selected").text();
          if($edittextbox.get(0).tagName == "SELECT"){
            $el.html($edittextbox.find("option:selected").text());
          }else{
            $el.html($edittextbox.val());
          }	
					$el.show();
					$el.trigger('editsubmit', [$el.html()]);
					$(document).unbind('click', submitChanges);
					//$edittextbox.detach();
          $edittextbox.hide();
				}
			},
			tempVal;

			$edittextbox.click(function (event) {
				event.stopPropagation();
			});

			/*$el.dblclick(function (e) {
				tempVal = $el.html();
				$edittextbox.val(tempVal).insertBefore(this)
                .bind('keypress', function (e) {
					var code = (e.keyCode ? e.keyCode : e.which);
					if (code == 13) {
						submitChanges();
					}
				}).select();
				$el.hide();
				$(document).click(submitChanges);
			});*/

      var $auto_dbdclick = function(){
        $edittextbox.show();
        tempVal = $el.html();
				$edittextbox.val(tempVal).insertBefore($el)
                .bind('keypress', function (e) {
					var code = (e.keyCode ? e.keyCode : e.which);
					if (code == 13) {
						submitChanges();
					}
				}).select();
				$el.hide();
				$(document).click(submitChanges);
      }
      $auto_dbdclick();

		});
		return this;
	}
});

  var products = (function(){

    var arrayProducts = [];
    var fd = new FormData();
    fd.append("nonce_ajax", PicSellVars.nonce);
    fd.append("post_id", PicSellVars.post.ID);
    fd.append("action", "pic_autocompleteOfferPack");

    $.ajax({
      async: false,
      type: "POST",
      dataType: 'json',
      url: PicSellVars.url,
      data: fd,
      contentType: false,
      processData: false,
      success: function(data) {
        arrayProducts = data;
      }
    });
    return arrayProducts;

  }());

  $("body").on("click", ".pic_add_field_row_offer", function (e) { 

    var contents = $("#master-row").html();
    contents = contents.replaceAll("modele_tr", "tr");
    contents = contents.replaceAll("modele_td", "td");
    var tbody = $("#field_wrap tbody");
    var table = tbody.length ? tbody : $("#field_wrap");
    table.append(contents);
    console.log(table);
    ps_init_offre();
    ps_init_classement();
    
  });

  /**
   * OPEN CARD
   */
  $("body").on("click", ".ps_open_card", function (e) {  

      e.preventDefault();
    
      var $card = $(this).parent().parent();
      if($card.hasClass("close")){
        $(this).removeClass('dashicons-arrow-left').addClass('dashicons-arrow-down');
        $card.removeClass("close").addClass("open");
      }else{
        $(this).removeClass('dashicons-arrow-down').addClass('dashicons-arrow-left');
        $card.removeClass("open").addClass("close");
      }
    
  });

  $("body").on("dblclick", ".container-flex .cart .edit", function (e) {
    var $cart = $(this).parent().parent();

    var input_title = $cart.find("input.ps_media_title_input"),
    input_quantity = $cart.find("input.ps_quantity"),
    input_price = $cart.find("input.ps_price"),
    select_choice_media = $cart.find("select.ps_choice_image_select"),
    input_media_dir = $cart.find("input.ps_media_dir"),
    media = $cart.find("img.ps_display_image"),
    textarea_desc = $cart.find("textarea.ps_media_desc"),
    select_choice_cat = $cart.find("select.ps_choice_cat_select");  

    console.log($cart);

    $(this).editable(e).on('editsubmit', function(event, val) {
      ps_save_post();
    });


  });

function ps_init_classement() {
  $(".container-flex .cart .param").each(function (index) {
    $(this).find(".ps_classement_span").html(index+1);
    $(this).find(".ps_classement_input").val(index+1);
  });
  EnableAutoCompletion();
}

function ps_init_offre_flex(){
  var $container = $(".container-flex");
  var $carts = $(".container-flex > div");

  $carts.each(function(index){

    var $cart = $(this);

    $cart.addClass("cart close cart-"+index);

    var input_title = $cart.find("input.ps_media_title_input"),
    input_quantity = $cart.find("input.ps_quantity"),
    input_price = $cart.find("input.ps_price"),
    select_choice_media = $cart.find("select.ps_choice_image_select"),
    input_media_dir = $cart.find("input.ps_media_dir"),
    media = $cart.find("img.ps_display_image"),
    textarea_desc = $cart.find("textarea.ps_media_desc"),
    select_choice_cat = $cart.find("select.ps_choice_cat_select");  

    var line_title = $("<h3 class='title'>"+$(input_title).val()+"</h3>");
    var line_desc = $("<span class='desc'>"+$(textarea_desc).val()+"</span>");
    input_title.parent().append(line_title).append(line_desc); 

    var line_quantity = $("<span class='label quantity'>"+ __('Quantity', 'pic_sell_plugin') + ": </span><span data-input='input.ps_quantity' class='edit'>" + $(input_quantity).val() + "</span>");
    input_quantity.parent().append(line_quantity);

    var line_price = $("<span class='label price'>"+ __('Price', 'pic_sell_plugin') + ": </span><span data-input='input.ps_price' class='edit'>" + $(input_price).val() + "</span>");
    input_price.parent().append(line_price);

    var line_choice_media = $("<span class='label choice_media'>"+ __('Type media', 'pic_sell_plugin') + ": </span><span data-input='select.ps_choice_image_select' class='edit'>" + $(select_choice_media).val() + "</span>");
    select_choice_media.parent().append(line_choice_media);

    var line_choice_cat = $("<span class='label choice_cat'>"+ __('Category product', 'pic_sell_plugin') + ": </span><span data-input='select.ps_choice_cat_select' class='edit'>" + $(select_choice_cat).find("option:selected").text() + "</span>");
    select_choice_cat.parent().append(line_choice_cat);

    $(input_title).hide();
    $(textarea_desc).hide();
    $(input_quantity).hide();
    $(input_price).hide();
    $(select_choice_media).hide();
    $(select_choice_cat).hide();

  });

}

function ps_save_post(){
  //$('input#publish, input#save-post').click(function(){
    //Post to post.php
    var postURL = PicSellVars.post_url;
    //Collate all post form data
    var data = $('form#post').serializeArray();
    //Set a trigger for our save_post action
    data.push({name: 'foo_doing_ajax', value:true});

    var ajax_updated = false;

    $(window).unbind('beforeunload.edit-post');
    $(window).on( 'beforeunload.edit-post', function() {
      var editor = typeof tinymce !== 'undefined' && tinymce.get('content');
      if ( ( editor && !editor.isHidden() && editor.isDirty() ) ||
              ( wp.autosave && wp.autosave.getCompareString() != ajax_updated) ) { 
              return postL10n.saveAlert;
      }   
});
    $.post(postURL, data, function(response){
        if(response.success){
          // Update the saved content for the beforeunload check
          ajax_updated = wp.autosave.getCompareString();
          var slide_message = "<div class='pic_slide_message'>"+ __('The offers are Saved', 'pic_sell_plugin') +"</div>";
          $('body').append(slide_message);
          $('.pic_slide_message').delay( 1000).slideUpRemove(1200);
          console.log('Saved post successfully');
      }else{
        //alert('Something went wrong. ' + response);
      }         
    });
    return false;

}

function ps_init_offre() {
  var tr = jQuery("#field_wrap tr:not(.tr_head)");
  if (tr.length) {
    jQuery("#field_wrap").show();
  } else {
    jQuery("#field_wrap").hide();
  }
}

const toBase64 = (file) =>
  new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.readAsDataURL(file);
    reader.onload = () => resolve(reader.result);
    reader.onerror = (error) => reject(error);
  });

  function EnableAutoCompletion(){

    $( ".ps_media_title_input" ).autocomplete({
        minLength: 0,
        source: function (request, response) {
          var regex = new RegExp(request.term, 'i');
          response($.map(products, function(item) {
            if(regex.test(item.titre)){
              return {
                  label: item.titre,
                  desc: item.description,
                  price: item.prix
              };
            }
          }));
         //});
      },
      /*response: function(ev, item){
        console.log(item);
      },*/
        focus: function( event, ui ) {
          $( this).val( ui.item.label );
              return false;
        },
        select: function( event, ui ) {

          $( this ).val( ui.item.label );
          //$( "#project-id" ).val( ui.item.value );
          //$( "#project-description" ).html( ui.item.desc );
          return false;
        },
        create: function() {
          $(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
              return $( "<li>" )
              .append( "" + item.label + "<span class='price_li_autocomplete'><small>"+ item.price +"</small></span><br><small>" + item.desc + "</small>" )
              .appendTo( ul );
          };
        }

    });  
  }



  /**
   * CARD SORTABLE DRAG
   */
  $(".container-flex").sortable({
    update: function (event, ui) {
      
      $(".container-flex .cart .param").each(function (index) {
        $(this).find(".ps_classement_span").html(index+1);
        $(this).find(".ps_classement_input").val(index+1);
      });
      ps_save_post();
    },
    handle: ".param",
    cursor: "move",
    placeholder: "ui-state-highlight"
  });
  $( ".container-flex" ).disableSelection();

  //ps_init_offre();
  ps_init_offre_flex();

  ps_init_classement();
  EnableAutoCompletion();
  
  var slice_size = 1024 * 1024 * 10;
  var post_id = PicSellVars.post.ID;
  var post_title = PicSellVars.post.post_title;
  var nonce = PicSellVars.nonce;
  var individual_file = {};
  var $parent = {};
  var $ligne = {};
  var $file = {};

 /* $("body").on("keyup", "input.ps_media_title", function (e) {

    e.preventDefault();
    var $this = $(this);
    var $parent = $this.parent();
    var $val = $this.val();
    var $ligne = $parent.parent();

    console.log($ligne);

  });*/

  $("body").on("change", ".ps_upload_image_button", async function (e) {
    e.preventDefault();

    var $parent = $(this).parent(),
      $ligne = $parent.parent(),
      $file = $(this),
      individual_file = $file[0].files[0],
      name_file = individual_file.name,
      fd = new FormData();
    var post_id = PicSellVars.post.ID;
    var post_title = PicSellVars.post.post_title;

    var file_b64 = await toBase64(individual_file);

    var isImage = individual_file.type.split("/")[0] === "image";
    if (!isImage) {
      alert("Seule les images sont autorisées");
      $file.val("");
      return false;
    }

    fd.append("nonce_ajax", nonce);
    fd.append("file", individual_file);
    fd.append("post_title", post_title);
    fd.append("post_id", post_id);
    fd.append("action", "fiu_upload_file");

    $.ajax({
      type: "POST",
      url: ajaxurl,
      data: fd,
      contentType: false,
      processData: false,
      success: function (response) {
        response = JSON.parse(response);
        $ligne.find(".ps_display_image").attr("src", file_b64).show();
        $file.val("").hide();
        $ligne.find(".ps_remove_media_button").show();
        $ligne.find(".ps_media_dir").val(response.bdir);
        //$ligne.find(".ps_media_title").val(name_file.split(".")[0]);
      },
    });
  });

  $("body").on("click", ".ps_remove_media_button", function () {
    $(this).parent().parent().find(".ps_display_image").attr("src", "").hide();
    $(this).parent().parent().find(".ps_media_url").val("");
    $(this).parent().parent().find(".ps_choice_image_select").trigger("change");
    $(this).parent().parent().find(".ps_upload_image_button").show();
    $(this).hide();
    return false;
  });

  $("body").on("click", ".ps_remove_line_button", function () {
    $(this).parent().parent().remove();
    ps_init_offre();
    ps_init_classement();
    return false;
  });



});

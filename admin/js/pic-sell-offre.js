function add_field_row_offer() {
  var contents = jQuery("#master-row").html();
  contents = contents.replaceAll("modele_tr", "tr");
  contents = contents.replaceAll("modele_td", "td");
  var tbody = jQuery("#field_wrap tbody");
  var table = tbody.length ? tbody : jQuery("#field_wrap");
  table.append(contents);
  console.log(table);
  ps_init_offre();
  ps_init_classement();
}

function ps_init_classement() {
  var tr = jQuery("#field_wrap tr:not(.tr_head)");
  if (tr.length) {
    jQuery("#field_wrap tbody tr:not(.tr_head)").each(function () {
      jQuery(this).find(".ps_classement_span").html(jQuery(this).index());
      jQuery(this).find(".ps_classement_input").val(jQuery(this).index());
    });
  }
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

jQuery(function ($) {
  /**
   * TABLEAU SORTABLE DRAG
   */
  $("#field_wrap tbody").sortable({
    update: function (event, ui) {
      $("#field_wrap tbody tr").each(function () {
        $(this).find(".ps_classement_span").html($(this).index());
        $(this).find(".ps_classement_input").val($(this).index());
      });
    },
    handle: ".ps_classement",
    cursor: "move",
  });

  ps_init_offre();
  ps_init_classement();

  var slice_size = 1024 * 1024 * 10;
  var post_id = PicSellVars.post.ID;
  var post_title = PicSellVars.post.post_title;
  var individual_file = {};
  var $parent = {};
  var $ligne = {};
  var $file = {};

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

    fd.append("file", individual_file);
    fd.append("post_title", post_title);
    fd.append("post_id", post_id);
    fd.append("action", "fiu_upload_file");

    jQuery.ajax({
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

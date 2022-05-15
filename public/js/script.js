jQuery(function ($) {
  //"use strict";

  "use strict";

  function _classCallCheck(instance, Constructor) {
    if (!(instance instanceof Constructor)) {
      throw new TypeError("Cannot call a class as a function");
    }
  }

  function _defineProperties(target, props) {
    for (var i = 0; i < props.length; i++) {
      var descriptor = props[i];
      descriptor.enumerable = descriptor.enumerable || false;
      descriptor.configurable = true;
      if ("value" in descriptor) descriptor.writable = true;
      Object.defineProperty(target, descriptor.key, descriptor);
    }
  }

  function _createClass(Constructor, protoProps, staticProps) {
    if (protoProps) _defineProperties(Constructor.prototype, protoProps);
    if (staticProps) _defineProperties(Constructor, staticProps);
    return Constructor;
  }
  const produits = $("span#params").data("products");
  const galerie = $("span#params").data("galerie");
  var articles = $("span#params").data("cart");

  var UI =
    /*#__PURE__*/
    (function () {
      /**

*

*  Base64 encode / decode

*  http://www.webtoolkit.info/

**/

      var Base64 = {
        // private property
        _keyStr:
          "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

        // public method for encoding
        encode: function (input) {
          var output = "";
          var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
          var i = 0;

          input = Base64._utf8_encode(input);

          while (i < input.length) {
            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
              enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
              enc4 = 64;
            }

            output =
              output +
              this._keyStr.charAt(enc1) +
              this._keyStr.charAt(enc2) +
              this._keyStr.charAt(enc3) +
              this._keyStr.charAt(enc4);
          }

          return output;
        },

        // public method for decoding
        decode: function (input) {
          var output = "";
          var chr1, chr2, chr3;
          var enc1, enc2, enc3, enc4;
          var i = 0;

          input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

          while (i < input.length) {
            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
              output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
              output = output + String.fromCharCode(chr3);
            }
          }

          output = Base64._utf8_decode(output);

          return output;
        },

        // private method for UTF-8 encoding
        _utf8_encode: function (string) {
          string = string.replace(/\r\n/g, "\n");
          var utftext = "";

          for (var n = 0; n < string.length; n++) {
            var c = string.charCodeAt(n);

            if (c < 128) {
              utftext += String.fromCharCode(c);
            } else if (c > 127 && c < 2048) {
              utftext += String.fromCharCode((c >> 6) | 192);
              utftext += String.fromCharCode((c & 63) | 128);
            } else {
              utftext += String.fromCharCode((c >> 12) | 224);
              utftext += String.fromCharCode(((c >> 6) & 63) | 128);
              utftext += String.fromCharCode((c & 63) | 128);
            }
          }

          return utftext;
        },

        // private method for UTF-8 decoding
        _utf8_decode: function (utftext) {
          var string = "";
          var i = 0;
          var c = (c1 = c2 = 0);

          while (i < utftext.length) {
            c = utftext.charCodeAt(i);

            if (c < 128) {
              string += String.fromCharCode(c);
              i++;
            } else if (c > 191 && c < 224) {
              c2 = utftext.charCodeAt(i + 1);
              string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
              i += 2;
            } else {
              c2 = utftext.charCodeAt(i + 1);
              c3 = utftext.charCodeAt(i + 2);
              string += String.fromCharCode(
                ((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63)
              );
              i += 3;
            }
          }

          return string;
        },
      };

      function Ajax_call(data) {
        return $.ajax({
          type: "POST",
          url: PicSellVars.ajaxurl,
          data: data,
          cache: false,
          dataType: 'text',
          contentType: false,
          processData: false,
          success: function (retour) {
            // return retour;
          },
          //  dataType: "json",
        });
      }

      function UI(galerie, produits, panier) {
        _classCallCheck(this, UI);

        this.temp = []; // mémoire tempon

        this.listeners = []; // gestion des évènements

        this.galerie = galerie;

        produits.forEach(function (produit) {
          if (typeof produit.prix == "string")
            produit.prix = parseFloat(produit.prix.replace(",", "."));
        });
        this.produits = produits;
        panier.articles.forEach(function (article, index) {
          var current_array = article.array_media;
          article.array_media = [];
          current_array.forEach(function (media, index) {
            article.array_media.push(parseInt(media));
          });
        });
        this.panier = panier; // instance de l'objet Panier

        this.modal = document.getElementById("product_modal");
        this.visionneuse = document.getElementById("visionneuse"); // création de la galerie Masonry

        this.createMasonry();
      }

      _createClass(UI, [
        {
          key: "prompt",
          value: function prompt(message, type) {
            this.resetAlert();
            var $alert = $("#alert");
            this.alert(message);

            switch (type) {
              case "number":
                // to do : gestion de contraintes auto
                $("#alert").attr("data-type", "number");
                $("#alert .modal-content").append(
                  '<input class="input_prompt" type="number" value=1 min=1 placeholder="1" />'
                );
                $("#alert .modal-content").append(
                  '<button class="button submit">OK</button>'
                );
                break;

              case "boolean":
                $("#alert").attr("data-type", "boolean");
                $("#alert .modal-content").append(
                  '<button class="button submit" data-val="true" >Oui</button>'
                );
                $("#alert .modal-content").append(
                  '<button class="button submit" data-val="false" >Non</button>'
                );
                break;
            }

            return new Promise(function (resolve, reject) {
              $("#alert .close").on("click", function (event) {
                event.preventDefault();
                $("#alert").hide();
                reject();
              });
              $("#alert .submit").on("click", function (event) {
                event.preventDefault();
                var type = $("#alert").attr("data-type");
                console.log(type);

                switch (type) {
                  case "number":
                    var number = $(".input_prompt").val();
                    console.log(number);
                    if (!Number.isInteger) parseInt(number);
                    resolve(number);
                    $("#alert").hide();
                    break;

                  case "boolean":
                    var bool = $(this).attr("data-val");
                    console.log(bool);
                    bool == "true" ? resolve(true) : resolve(false);
                    $("#alert").hide();
                    break;
                }
              });
            });
          },
        },
        {
          key: "alert",
          value: function alert(message) {
            this.resetAlert();
            $("#alert #message").text(message);
            $("#alert").show();
          },
        },
        {
          key: "resetAlert",
          value: function resetAlert() {
            $("#alert .modal-content").html('<p id="message"></p>');
          }, // ajout des évènements (une fois jQuery chargé)
        },
        {
          key: "events",
          value: function events() {
            //  au click sur une photo où vidéo
            this.listeners["brick"] = $(".brick").on("click", function (event) {
              var id = $(this).data("id");
              user_interface.openViewer(id);
              console.log("event", id);
            }); //  TO DO au click sur le bouton du panier

            this.listeners["panierOpen"] = $(
              ".button_panier, .panier .panier_head .close"
            ).on("click", function (event) {
              var panier = $(".panier");

              if (panier.hasClass("active")) {
                panier.hide("200");
                user_interface.closePanier();
                $(".container").width("100%");
                $("#visionneuse").width("100%");
                $("#alert").width("100%");
                $("#product_modal").width("100%");
              } else {
                panier.show("200");
                user_interface.openPanier();
                $(".container").width(window.innerWidth - 300);
                $("#visionneuse").width(window.innerWidth - 300);
                $("#alert").width(window.innerWidth - 300);
                $("#product_modal").width(window.innerWidth - 300);
              }

              panier.toggleClass("active"); // console.log(panier);
            }); // écran redimensionné, on réajuste l'interface

            this.listeners["modal"] = $(window).on("resize", function (event) {
              var panier = $(".panier");

              if (panier.hasClass("active")) {
                $(".container").width(window.innerWidth - 300);
                $("#visionneuse").width(window.innerWidth - 300);
                $("#product_modal").width(window.innerWidth - 300);
                $("#alert").width(window.innerWidth - 300);
              } else {
                $(".container").width("100%");
                $("#visionneuse").width("100%");
                $("#product_modal").width("100%");
                $("#alert").width("100%");
              }
            }); // au niveau de la visionneuse, click sur le bouton "ajouter au panier" qui ouvre la modal de selection de l'article

            this.listeners["visionneuse_ajout_panier"] = $(
              "#visionneuse #ajout_panier"
            ).on("click", function (event) {
              event.preventDefault();
              /* Act on the event */

              var id = $(this).data("id");
              user_interface.closeViewer();
              user_interface.openSelectProduct(id);
            }); // before / after

            this.listeners["visionneuse_before"] = $("#visionneuse #before").on(
              "click",
              function (event) {
                event.preventDefault();
                /* Act on the event */

                var id_media = $("#visionneuse #ajout_panier").data("id");
                var media = user_interface.galerie.find(function (obj) {
                  return obj.id === id_media;
                });
                var index = user_interface.galerie.indexOf(media);
                var new_index;

                if (index === 0) {
                  new_index = user_interface.galerie.length - 1;
                } else {
                  new_index = index - 1;
                }

                user_interface.openViewer(user_interface.galerie[new_index].id);
              }
            ); // before / after

            this.listeners["visionneuse_after"] = $("#visionneuse #after").on(
              "click",
              function (event) {
                event.preventDefault();
                /* Act on the event */

                var id_media = $("#visionneuse #ajout_panier").data("id");
                var media = user_interface.galerie.find(function (obj) {
                  return obj.id === id_media;
                });
                var index = user_interface.galerie.indexOf(media);
                var new_index;

                if (index === user_interface.galerie.length - 1) {
                  new_index = 0;
                } else {
                  new_index = index + 1;
                }

                user_interface.openViewer(user_interface.galerie[new_index].id);
              }
            ); // peut importe la modal, au click d'un el avec la classe .close, va récupérer le parent .modal et va le fermer

            this.listeners["modal"] = $(".modal .close").on(
              "click",
              function (event) {
                // $(this).parent().style.display = "block";
                $(this).parent().css("display", "none"); // console.log('event', "close modal");
              }
            ); // au choix d'une catégorie, on affiche les produits concernés

            this.listeners["select_cat"] = $(".modal .select_cat").on(
              "click",
              ".button",
              function (event) {
                var cat = $(this).data("cat");
                var buttons = $(".modal .select_cat .button");
                buttons.each(function (index, el) {
                  this.classList.remove("active");
                });
                this.classList.add("active");

                if (cat == "tout") {
                  $("#select_products .product").hide();
                  $("#select_products .product").show("20");
                } else {
                  $("#select_products .product").hide();
                  $('#select_products .product[data-cat="' + cat + '"]').show(
                    "20"
                  );
                }
              }
            );
            this.listeners["more_info_product_open"] = $(".modal").on(
              "click",
              ".product .product_image",
              function (event, el) {
                var id_produit = parseInt(
                  $(this).parent().attr("data-id_produit")
                );
                var produit = user_interface.produits.find(function (obj) {
                  return obj.id === id_produit;
                });

                if ($(".panier").hasClass("active")) {
                  $(".panier .button.close").click(); // correction, permet de mieux voir la description du produit en fermant le panier
                }

                $("#content_description img")[0].src = produit.src;
                $("#content_description .description").html(
                  produit.description
                );
                $("#content_description").addClass("active");
                $("#content_select").css("opacity", 0.5);
              }
            );
            this.listeners["more_info_product_close"] = $(".modal").on(
              "click",
              "#content_description .fermer",
              function (event, el) {
                $("#content_description").removeClass("active");
                $("#content_select").css("opacity", 1);
              }
            ); // Lance les event d'ajout /suppression au panier

            this.listeners["Ajout_suppression"] = $(".modal").on(
              "click",
              ".product .button",
              function (event, el) {
                // event.preventDefault();

                /* Act on the event */
                // Amélioration possible : vérification de la correspondance entre l'index de l'article, l'id_media et l'id_product
                if ($(this).hasClass("active")) {
                  $(document).trigger("remove_produit", [this]);
                } else {
                  $(document).trigger("add_produit", [this]);
                } // console.log('clique sur un produit');
              }
            ); // Event d'ajout au panier, gestion des différents cas

            this.listeners["add_produit"] = $(document).on(
              "add_produit",
              function (event, el) {
                // console.log('event test in UI : add_produit');
                var id_media = parseInt(
                  user_interface.modal.dataset["id_media"]
                );
                var media = user_interface.galerie.find(function (obj) {
                  return obj.id === id_media;
                });
                var id_produit = parseInt(
                  $(el).parent().attr("data-id_produit")
                );
                var produit = user_interface.produits.find(function (obj) {
                  return obj.id === id_produit;
                });
                var article_index = $(el).parent().attr("data-index");
                user_interface.temp = {
                  el: el,
                  id_media: id_media,
                  id_produit: id_produit,
                  article_index: article_index, // console.log(id_media, id_produit, article_index);
                  // let article = user_interface.panier.articles.find(obj => {
                  //  return obj.index === article_index
                  // });
                  // console.log(article);

                  /*
            Liste des cas possibles :
            - c'est un produit à 1 média :
              on le rajoute dans le panier => avec la classe Panier (on envoi le PRODUIT et le MEDIA) 
              on update l'élément html avec l'index de l'article
            - c'est un produit à 1+ média :
              on le rajoute dans le panier => avec la classe Panier (on envoi le PRODUIT et le MEDIA) 
              on update l'élément html avec l'index de l'article
            - c'est un produit à 1+ média déjà en cours :
              on récupère l'index et l'article
              on ajoute la photo (vérification quand même qu'elle n'est pas déjà présente) => avec la classe Panier (on envoi le PRODUIT le MEDIA et l'INDEX)
              on update l'élément html
          */
                };

                if (article_index == "null") {
                  // il faut créer l'article
                  user_interface
                    .prompt("Sélectionnez la quantité", "number")
                    .then(function (qtt) {
                      qtt = parseInt(qtt);
                      console.log("OK ?", qtt);
                      var new_index = user_interface.panier.addItem(
                        user_interface.temp.id_produit,
                        user_interface.temp.id_media
                      );
                      user_interface.panier.updateItem(
                        new_index,
                        false,
                        false,
                        qtt
                      );

                      if (produit.limite > 1) {
                        // alors on créé un élément html
                        $("#desc_selection_ablum").show(); // on affiche le titre pour le choix des albums

                        var article = user_interface.panier.getItem(new_index);
                        var html = user_interface.html_template_select_product(
                          user_interface.temp.id_produit,
                          article,
                          true
                        );
                        $("#product_modal #select_albums").append(html);
                      }

                      $(user_interface.temp.el)
                        .parent()
                        .attr("data-index", new_index);
                      $(user_interface.temp.el).addClass("active");
                      $(user_interface.temp.el).parent().addClass("active");
                    })
                    ["catch"](function () {
                      console.log("not OK T_T");
                    }); // do{
                  //     var qtt = parseInt(window.prompt("Combien de "+produit.titre+" souhaitez vous ? (vous pourrez toujours modifier la quantité dans le panier par la suite)", "1"), 10);
                  // }while(isNaN(qtt) || qtt < 1);
                  // let new_index = user_interface.panier.addItem(id_produit, id_media);
                  // user_interface.panier.updateItem(new_index, false, false, qtt);
                  // if (produit.limite > 1) { // alors on créé un élément html
                  //  $('#desc_selection_ablum').show(); // on affiche le titre pour le choix des albums
                  //  let article = user_interface.panier.getItem(new_index);
                  //  let html = user_interface.html_template_select_product(id_produit, article, true);
                  //  $("#product_modal #select_albums").append(html);
                  //  return;
                  // }
                  // $(el).parent().attr('data-index', new_index);
                } else {
                  var index = user_interface.panier.addMediaItem(
                    article_index,
                    id_media
                  );
                  $(el).addClass("active");
                  $(el).parent().addClass("active");
                }
              }
            ); // Event de suppression au panier, gestion des différents cas

            this.listeners["remove_produit"] = $(document).on(
              "remove_produit",
              function (event, el) {
                // console.log('event test in UI : remove_produit');
                var id_media = parseInt(
                  user_interface.modal.dataset["id_media"]
                ); // let media = user_interface.galerie.find(obj => {
                //  return obj.id === id_media
                // });

                var id_produit = parseInt(
                  $(el).parent().attr("data-id_produit")
                );
                var produit = user_interface.produits.find(function (obj) {
                  return obj.id === id_produit;
                });
                var article_index = parseInt($(el).parent().attr("data-index"));
                var article = user_interface.panier.articles.find(function (
                  obj
                ) {
                  return obj.index === article_index;
                }); // console.log(id_media, id_produit, article_index);

                /*
          Liste des cas possibles :
          - c'est un produit à 1 média :
            on demande confirmation de suppression de l'article dans le panier
            on enlève du panier => avec la classe Panier
            on update l'élément html
          - c'est un produit à 1+ média :
            si 2 photos où plus dedans :
              on enlève le média de array_media => avec la classe Panier
            si 1 photo (donc celle que l'utilisateur veut enlever normalement)
              on demande confirmation de suppression de l'article dans le panier
              on supprime du panier l'article => avec la classe Panier
            Puis on update ou supprime l'élément html
        */

                if (produit.limite > 1) {
                  if (article.array_media.length > 1) {
                    user_interface.panier.removeMediaItem(
                      article_index,
                      id_media
                    );
                  } else {
                    user_interface.temp.el = el;
                    user_interface.panier
                      .deleteItem(article_index)
                      .then(function () {
                        var el = user_interface.temp.el;
                        $(el).parent().hide("200");
                        setTimeout(function () {
                          $(el).parent().remove();

                          if ($("#select_albums .product").length == 0) {
                            $("#desc_selection_ablum").hide(); // si il n'y a plus d'album, on affiche plus le titre 'choix album'
                          }
                        }, 200);
                        return;
                      });
                  }
                } else {
                  user_interface.panier.deleteItem(article_index);
                  $(el).parent().attr("data-index", "null");
                }

                $(el).removeClass("active");
                $(el).parent().removeClass("active");
              }
            );
            this.listeners["panier_plus_moins"] = $(".panier").on(
              "click",
              ".article_qtt .plus,.article_qtt .moins",
              function (event, el) {
                var index = parseInt(
                  $(this).parent().parent().parent().attr("data-index")
                );

                if ($(this).hasClass("plus")) {
                  user_interface.panier.addOneQtt(index);
                } else {
                  user_interface.panier.removeOneQtt(index);
                }
              }
            );
            this.listeners["panier_more_info_open"] = $(".panier").on(
              "click",
              ".panier_article .search, .panier_article .article_titre, .panier_article .article_photo",
              function (event, el) {
                $(".item_presentation").addClass("active");
                var article_index = $(this)
                  .parents(".panier_article")
                  .attr("data-index");
                user_interface.open_item_photo_section(parseInt(article_index));
              }
            );
            this.listeners["panier_more_info_close"] = $(".panier").on(
              "click",
              ".item_presentation_button",
              function (event, el) {
                $(".item_presentation").removeClass("active");
              }
            );
            this.listeners["panier_more_info_suppr_media"] = $(".panier").on(
              "click",
              ".item_photo .supprimer",
              function (event, el) {
                var id_media = parseInt($(this).parent().attr("data-id_media"));
                var article_index = parseInt(
                  $(this).parent().parent().parent().attr("data-index")
                );
                var article = user_interface.panier.articles.find(function (
                  obj
                ) {
                  return obj.index === article_index;
                });

                if (article.array_media.length > 1) {
                  user_interface.panier.removeMediaItem(
                    article_index,
                    id_media
                  ); // update les photos

                  user_interface.open_item_photo_section(article_index);
                } else {
                  user_interface.panier.deleteItem(article_index);
                  $(".item_presentation_button").click();
                }
              }
            );
            this.listeners["panier_more_info_description_open"] = $(
              ".panier"
            ).on(
              "click",
              ".item_description_button, .item_photo_button",
              function (event, el) {
                $(".item_photo_section").toggleClass("active");
                $(".item_description_section").toggleClass("active");
                $(".item_photo_button").toggleClass("active");
                $(".item_description_button").toggleClass("active");
              }
            ); // Trigger un event
            // $( document ).trigger( "masonry created", [ "bim", "baz" ] );

            this.listeners["window_click"] = window.onclick = function (event) {
              if (
                event.target == user_interface.visionneuse ||
                event.target == document.getElementById("visionneuse_close")
              ) {
                user_interface.closeViewer();
              }

              if (event.target == user_interface.modal) {
                user_interface.closeSelectProduct();
              }
            };
          }, // création de la galerie masonry
        },
        {
          key: "createMasonry",
          value: function createMasonry() {
            var html;
            // await Promise.all(
            this.galerie.map(async (elem, index) => {
              console.log("TYPE: " + elem.type);
              if (elem.type == "photo" || elem.type == "image") {
                var data = new FormData();
                data.append("img", PicSellVars.dir_include_img + "" + elem.url);
                data.append("action", "post_app");
                data.append("act", "/img/base64/");
                var url = await Ajax_call(data);
               // var base64 = Base64.decode(url);
                //console.log(base64);
                placeholder = $(".masonry");
               // placeholder.html('');
                $('<img>', {
                    src: url
                }).appendTo(placeholder);

                html +=
                  '<div class="brick" draggable="true" data-id="' +
                  elem.id +
                  '"><img class="media-' +
                  elem.id +
                  '" src="" alt="' +
                  elem.titre +
                  '" title="' +
                  elem.titre +
                  " - " +
                  elem.description +
                  '"><div class="more"></div></div>';
              } else {
                html +=
                  '<div class="brick" draggable="true" data-id="' +
                  elem.id +
                  '"><video muted loop autoplay width="100%"><source autoplay src="' +
                  PicSellVars.url_include_img +
                  elem.url +
                  '"type="video/mp4">Sorry, your browser doesn\'t support embedded videos.</video><div class="more"></div></div>';
              }
              
             // $(".media-" + elem.id).attr("src", base64);
              //console.log(url);
            //  $(document).trigger("masonry_created");
            });
            //   );
          }, // ouvre la visionneuse au click
        },
        {
          key: "openViewer",
          value: function openViewer(id_media) {
            console.log("openViewer", id_media);
            this.closeViewer();
            var media = this.galerie.find(function (obj) {
              return obj.id === id_media;
            });

            if (media.type === "video") {
              $("#visionneuse #visionneuse_vid")[0].src = media.url;
              $("#visionneuse #visionneuse_vid")[0].style.display = "block";
            } else if (media.type === "photo" || media.type === "image") {
              $("#visionneuse #visionneuse_img")[0].style.display = "block";
              $("#visionneuse #visionneuse_img")[0].src = media.url;
            } // ajout de l'id

            $("#visionneuse #ajout_panier").data("id", media.id);
            visionneuse.style.display = "block";
          }, // ferme la visionneuse au click
        },
        {
          key: "closeViewer",
          value: function closeViewer() {
            $("#visionneuse #visionneuse_img")[0].src = "";
            $("#visionneuse #visionneuse_img")[0].style.display = "none";
            $("#visionneuse #visionneuse_vid")[0].src = "";
            $("#visionneuse #visionneuse_vid")[0].style.display = "none";
            visionneuse.style.display = "none";
          },
        },
        {
          key: "openSelectProduct",
          value: function openSelectProduct(id_media) {
            this.closeSelectProduct();
            var media = this.galerie.find(function (obj) {
              return obj.id === id_media;
            });

            if (media.type === "video") {
              $("#product_modal_vid")[0].style.display = "block";
              $("#product_modal_vid")[0].src = media.url;
            } else if (media.type === "photo" || media.type === "image") {
              $("#product_modal_img")[0].src = media.url;
              $("#product_modal_img")[0].style.display = "block";
            }

            $(this.modal).attr("data-id_media", id_media);
            this.modal.style.display = "block";
            var content_select = $(this.modal).find("#content_select");
            var content_description = $(this.modal).find(
              "#content_description"
            ); // let produitsWithThisMedia  = this.panier.getAllProductforThisMedia(id_media); // les produits où la photo/video est présente
            // pour chaque PRODUITS, on génère le HTML correspondant

            var categories = [];

            for (var i = 0; i < this.produits.length; i++) {
              if (media.type == this.produits[i].type) {
                var html = this.html_template_select_product(
                  this.produits[i].id,
                  null,
                  false
                );
                $("#product_modal #select_products").append(html); // on rajoute la catégorie du produit dans un tableau

                if (categories.indexOf(this.produits[i].cat) == -1) {
                  categories.push(this.produits[i].cat);
                }
              }
            }

            if (categories.length > 1) {
              categories.sort();
              categories = ["tout"].concat(categories);
            }
            /*
        <div class="select_cat">
          <button class="button" data-cat="all">tout</button>
        </div>
      */

            for (var i = 0; i < categories.length; i++) {
              if (categories[i] == "tout") {
                $(".select_cat").append(
                  '<button class="button active" data-cat="' +
                    categories[i] +
                    '">' +
                    categories[i] +
                    "</button>"
                );
              } else if (categories.length == 1) {
                $(".select_cat").append(
                  '<button class="button active" data-cat="' +
                    categories[i] +
                    '">' +
                    categories[i] +
                    "</button>"
                );
              } else {
                $(".select_cat").append(
                  '<button class="button" data-cat="' +
                    categories[i] +
                    '">' +
                    categories[i] +
                    "</button>"
                );
              }
            }
            /*
        On va :
        - mettre l'index de l'article et .active sur les items dit simples dans lequel il y a le média
        - ajouter les ablums en cours ( si le média est dedans : .active et l'index sinon, non )
      */

            for (var i = 0; i < this.panier.articles.length; i++) {
              var articleInPanier = this.panier.articles[i];

              if (articleInPanier.produit.type == media.type) {
                // si le média et l'article sont compatible
                var thisMediaIsIn =
                  articleInPanier.array_media.indexOf(id_media) > -1;

                if (articleInPanier.produit.limite > 1) {
                  // il y a un album, donc on affiche #desc_selection_ablum
                  $("#desc_selection_ablum").show();
                }

                if (articleInPanier.produit.limite > 1 && thisMediaIsIn) {
                  // si c'est un ablum est que le média est dedans
                  var _html = this.html_template_select_product(
                    articleInPanier.id_produit,
                    articleInPanier,
                    true
                  );

                  $("#product_modal #select_albums").append(_html);
                } else if (
                  articleInPanier.produit.limite > 1 &&
                  !thisMediaIsIn
                ) {
                  // si c'est un album mais que le média n'est pas dedans
                  var _html2 = this.html_template_select_product(
                    articleInPanier.id_produit,
                    articleInPanier,
                    false
                  );

                  $("#product_modal #select_albums").append(_html2);
                } else if (
                  articleInPanier.produit.limite == 1 &&
                  thisMediaIsIn
                ) {
                  // si c'est un produit simple et que c'est ce média qui est choisi avec
                  var itemInModal = $(
                    "#product_modal .product[data-id_produit='" +
                      articleInPanier.id_produit +
                      "']"
                  );
                  itemInModal.attr("data-index", articleInPanier.index);
                  itemInModal.addClass("active");
                  itemInModal.find(".button.select").addClass("active");
                }
              }
            }
          },
          /* 
      Ouvre la modal pour la séléction du produit
      doit gérer le cas ou les produits sont déjà dans le panier
    */
        },
        {
          key: "closeSelectProduct",
          value: function closeSelectProduct() {
            $("#product_modal_img")[0].src = "";
            $("#product_modal_img")[0].style.display = "none";
            $("#product_modal_vid")[0].src = "";
            $("#product_modal_vid")[0].style.display = "none";
            $("#desc_selection_ablum").hide(); // par défaut il n'y a pas d'albums

            $("#product_modal #select_products").empty();
            $("#product_modal #select_albums").empty();
            $("#product_modal .select_cat").empty();
            $(this.modal).data("id_media", "null");
            this.modal.style.display = "none";
          },
          /*
      Montre l'élément panier actualisé et génère le html
    */
        },
        {
          key: "openPanier",
          value: function openPanier() {
            this.closePanier();
            /* 
        On boucle sur les articles
        on génère le template HTML 
        On insère le template HTML dans le panier après l'avoir vidé
        On met à jour le bouton
        On sauvegarde le panier en bdd (un envois post de l'objet article)
      */

            var montant = 0;

            for (var i = 0; i < this.panier.articles.length; i++) {
              // montant += parseFloat(articles[i].produit.prix * articles[i].qtt);
              montant +=
                parseFloat(this.panier.articles[i].produit.prix) *
                parseInt(articles[i].qtt);
              var article_html = this.html_template_panier_article(articles[i]);
              $(".panier_articles").append(article_html);
            }

            $(".panier_recap .montant").html("Montant : " + montant + " €");
          },
          /*
      cache l'élément panier actualisé et supprime le HTML et le montant
    */
        },
        {
          key: "closePanier",
          value: function closePanier() {
            $(".panier_articles").empty();
            $(".button_panier span").html(this.panier.articles.length);
          },
          /*
      Ouvre l'élément qui contient la description et les photos
    */
        },
        {
          key: "open_item_photo_section",
          value: function open_item_photo_section(article_index) {
            this.close_item_photo_section();
            var article = this.panier.getItem(article_index);
            $(".item_presentation").attr("data-index", article_index);

            if (
              article.produit.type == "photo" ||
              article.produit.type == "image"
            ) {
              $(".item_presentation_photo").css(
                "background-image",
                "url(" + article.produit.src + ")"
              );
              $(".item_photo_button").html("Photo(s)");
            } else {
              $(".item_presentation_photo").css(
                "background-image",
                "url(" + article.produit.src + ")"
              );
              $(".item_photo_button").html("Vidéo");
            }

            $(".item_presentation_titre").html(article.produit.titre);
            $(".item_description_section").html(article.produit.description);

            for (var i = 0; i < article.array_media.length; i++) {
              var id_media = article.array_media[i];
              var html = this.html_template_panier_item_photo_section(
                id_media,
                article.produit.type
              );
              $(".item_photo_section").append(html);
            }
          }, // va supprimer toute les photos dans l'onglet "photo"
        },
        {
          key: "close_item_photo_section",
          value: function close_item_photo_section() {
            $(".item_photo_section").empty();
          }, // prend 3 paramètres; l'id_produit (pour les infos), l'article (pour rajouter l'index si besoin); isIn, un boolean pour choisir le bon template (false : pas active, true : active)
        },
        {
          key: "html_template_select_product",
          value: function html_template_select_product(
            produit_id,
            article,
            isIn
          ) {
            var produit = this.produits.find(function (obj) {
              return obj.id === produit_id;
            });
            var template = $("#template_select_product");

            if (isIn != false) {
              template = $("#template_select_product_active");
            }

            var template_html = template.html(); // {ID_PRODUIT}
            // {ID_MEDIA}
            // {TITRE}
            // {PRIX}
            // template.children[0].innerHTML.replace

            if (article != null) {
              template_html = template_html.replace("{INDEX}", article.index);
            } else {
              template_html = template_html.replace("{INDEX}", "null");
            }

            template_html = template_html.replace("{ID_PRODUIT}", produit_id);
            template_html = template_html.replace(
              "{ID_MEDIA}",
              this.modal.dataset["id_media"]
            );
            template_html = template_html.replace("{IMAGE_SRC}", produit.src);
            template_html = template_html.replace("{TITRE}", produit.titre);
            template_html = template_html.replace("{PRIX}", produit.prix);
            template_html = template_html.replace("{CAT}", produit.cat); // template.querySelector(".product").dataset.produit_id = produit_id;
            // template.querySelector(".product_price").innerText = produit.prix + " €";
            // debugger;
            // template = document.importNode(templa, true);
            // TO DO : image de fond bien sur :)
            // template.querySelector(".product_titre").innerText = produit.titre;
            // {"id": 1, "titre":"Photo Lorem impsum 1", "cat" : "A", "prix":"50", "type":"photo", "limite":"1"},

            return template_html;
          },
        },
        {
          key: "html_template_panier_article",
          value: function html_template_panier_article(article) {
            var template = $("#template_panier_article");
            var template_html = template.html(); // {TITRE}
            // {PRIX}
            // {QTT}
            // {X_SUR_MAX}

            template_html = template_html.replace("{INDEX}", article.index);
            template_html = template_html.replace(
              "{TITRE}",
              article.produit.titre
            );
            template_html = template_html.replace(
              "{IMAGE_SRC}",
              article.produit.src
            );
            template_html = template_html.replace(
              "{PRIX}",
              article.produit.prix
            );
            template_html = template_html.replace(
              "{QTT}",
              parseInt(article.qtt)
            );

            if (article.produit.limite == 1) {
              template_html = template_html.replace(
                "{DISPLAY_X_SUR_MAX}",
                "none"
              );
            } else {
              template_html = template_html.replace(
                "{DISPLAY_X_SUR_MAX}",
                "block"
              );
              template_html = template_html.replace(
                "{X_SUR_MAX}",
                article.array_media.length + "/" + article.produit.limite
              );
            }

            return template_html;
          },
        },
        {
          key: "html_template_panier_item_photo_section",
          value: function html_template_panier_item_photo_section(
            id_media,
            type
          ) {
            var media = user_interface.galerie.find(function (obj) {
              return obj.id === id_media;
            });
            var template = $("#template_panier_item_photo_section");
            var template_html = template.html();
            template_html = template_html.replace("{ID_MEDIA}", media.id);

            if (type == "photo" || type == "image") {
              template_html = template_html.replace("{IMAGE_SRC}", media.url);
              template_html = template_html.replace(
                "{IMAGE_DISPLAY}",
                "inline-block"
              );
              template_html = template_html.replace("{VIDEO_DISPLAY}", "none");
            } else if (type == "video") {
              template_html = template_html.replace("{VIDEO_SRC}", media.url);
              template_html = template_html.replace(
                "{VIDEO_DISPLAY}",
                "inline-block"
              );
              template_html = template_html.replace("{IMAGE_DISPLAY}", "none");
            }

            return template_html;
          },
        },
      ]);

      return UI;
    })();

  var Panier =
    /*#__PURE__*/
    (function () {
      function Panier(articles) {
        _classCallCheck(this, Panier);

        this.articles = articles;
        this.articles.forEach(function (article) {
          article.id_produit = parseInt(article.id_produit);
          article.index = parseInt(article.index);
          article.produit.id = parseInt(article.produit.id);
          article.qtt = parseInt(article.qtt);
          article.produit.limite = parseInt(article.produit.limite);
          article.produit.prix = parseFloat(
            article.produit.prix.replace(",", ".")
          );
        });
        this.enrichissementArticles();
        this.updatePanier();
      }

      _createClass(Panier, [
        {
          key: "enrichissementArticles",
          value: function enrichissementArticles() {
            var _this = this;

            for (var i = 0; i < this.articles.length; i++) {
              var produit = produits.find(function (obj) {
                // ici produit c'est la variable globale, qui ne change pas
                return obj.id === _this.articles[i].id_produit;
              }); // on récupère le produit correspondant

              this.articles[i].produit = produit; // et on enrichie l'item avec les données du produit
            }
          },
        },
        {
          key: "updatePanier",
          value: function updatePanier() {
            this.save_panier();
            var data = new FormData();
            data.append("cartId", $("span#params").data("id"));
            data.append("password", $("span#params").data("password"));
            data.append("cart", this.articles);
            data.append("action", "post_app");
            data.append("act", "/post/update/");
            $.ajax({
              type: "POST",
              url: PicSellVars.ajaxurl,
              data: data,
              contentType: false,
              processData: false,
              success: function (retour) {
                console.log(retour);
              },
              dataType: "json",
            });
            //  $.post('/post/update/', {cartId: $('span#params').data('id'), password: $('span#params').data('password'), cart: this.articles});
            console.log("sauvegarde en base de donnée");
          }, // Liste des évènements maisons
        },
        {
          key: "events",
          value: function events() {
            /* Events maisons */
            $(document).on("addItem", function (event) {
              // accès aux variables globales du documents
              console.log("event test in panier");
              $(document).trigger("updatePanier", panier.articles);
            });
            $(document).on("updateItem", function (event, id) {
              // accès aux variables globales du documents
              console.log("event updateItem in panier", id);
              $(document).trigger("updatePanier", panier.articles);
            });
            $(document).on("deleteItem", function (event, id) {
              // accès aux variables globales du documents
              console.log("event deleteItem in panier", id);
              $(document).trigger("updatePanier", panier.articles);
            });
            $(document).on("updatePanier", function (event, articles) {
              // accès aux variables globales du documents
              panier.enrichissementArticles();
              panier.updatePanier();
              user_interface.closePanier();
              user_interface.openPanier();

              if ($("#product_modal").is(":visible")) {
                var id_media = $("#product_modal").attr("data-id_media");
                user_interface.openSelectProduct(parseInt(id_media));
              }
            });
          },
        },
        {
          key: "getItem",
          value: function getItem(index) {
            return this.articles.find(function (produit) {
              return produit.index === index;
            });
          },
          /* 
      
    */
        },
        {
          key: "addMediaItem",
          value: function addMediaItem(index, id_media) {
            var article = this.getItem(parseInt(index));

            if (article.produit.limite > article.array_media.length) {
              article.array_media.push(parseInt(id_media));
            }

            $(document).trigger("updateItem", [index]);
          },
          /*
      ajoute un item et renvoi l'index
    */
        },
        {
          key: "addItem",
          value: function addItem(id_produit, id_media) {
            id_produit = parseInt(id_produit);
            id_media = parseInt(id_media);
            var media = user_interface.galerie.find(function (obj) {
              return obj.id === id_media;
            });
            var produit = user_interface.produits.find(function (obj) {
              return obj.id === id_produit;
            });
            var timestamp = new Date().getUTCMilliseconds();
            this.articles.push({
              index: timestamp,
              id_produit: id_produit,
              array_media: [id_media],
              produit: produit,
              qtt: 1,
            });
            $(document).trigger("addItem");
            return timestamp;
          },
        },
        {
          key: "addOneQtt",
          value: function addOneQtt(index) {
            var article = this.getItem(parseInt(index));
            article.qtt += 1;
            $(document).trigger("updateItem", [index]);
          },
        },
        {
          key: "removeOneQtt",
          value: function removeOneQtt(index) {
            var article = this.getItem(parseInt(index));

            if (article.qtt == 1) {
              this.deleteItem(index);
            } else {
              article.qtt -= 1;
            }

            $(document).trigger("updateItem", [index]);
          },
        },
        {
          key: "updateItem",
          value: function updateItem(index, id_produit, array_media, qtt) {
            var article = this.getItem(parseInt(index));

            if (id_produit != false) {
              article.id_produit = id_produit;
            }

            if (array_media != false) {
              article.array_media = array_media;
            }

            if (qtt != false) {
              article.qtt = qtt;
            }

            $(document).trigger("updateItem", [index]);
          },
        },
        {
          key: "removeMediaItem",
          value: function removeMediaItem(index, id_media) {
            var article = this.getItem(parseInt(index));
            var index_media = article.array_media.indexOf(id_media);
            article.array_media.splice(index_media, 1);
            $(document).trigger("updateItem", [index]);
          },
        },
        {
          key: "deleteItem",
          value: function deleteItem(index) {
            user_interface.temp.index = index;
            return user_interface
              .prompt("Cela va supprimer l'article, êtes-vous sûr ?", "boolean")
              .then(function (bool) {
                if (bool == true) {
                  var _index = user_interface.temp.index;
                  var article = panier.getItem(parseInt(_index));
                  var index_article = panier.articles.indexOf(article);
                  panier.articles.splice(index_article, 1);
                  $(document).trigger("deleteItem", [_index]);
                  return true;
                } else {
                  return false;
                }
              });
          },
        },
        {
          key: "getAllProductforThisMedia",
          value: function getAllProductforThisMedia(id) {
            var res = this.articles.filter(function (obj) {
              return obj.array_media.indexOf(id) > -1;
            });
            return res;
          },
        },
        {
          key: "getAllProductAlbum",
          value: function getAllProductAlbum() {
            // return tout les produits avec Albums en cours
          },
        },
        {
          key: "save_panier",
          value: function save_panier() {
            // save en BDD
          },
        },
      ]);

      return Panier;
    })();

  var panier = new Panier(articles);
  var user_interface = new UI(galerie, produits, panier); // console.log(user_interface);
  // console.log(panier);

  jQuery(document).ready(function ($) {
    // Quand jQuery est chargé, on créé les évènements
    user_interface.events();

    panier.events();
    $(document).trigger("updatePanier", this.articles); 
    
    // Gestion du parallax
    var moving__background = jQuery(".header-background");
    jQuery(window).scroll(function () {
      var offsetTop = $(".header-espaceprive")[0].offsetTop;
      var offset = jQuery(window).scrollTop();

      if (offset >= offsetTop) {
        moving__background.css("margin-top", (offset - offsetTop) / 3); // Parallax scrolling

        moving__background.css(
          "opacity",
          1 - (offset - offsetTop) / moving__background.height()
        ); // Fading out
      } else {
        moving__background.css("margin-top", 0); // Parallax scrolling

        moving__background.css("opacity", 1); // Fading out
      }
    });
    /* Gestion du drag and drop : pour l'instant à l'extérieur des classes */

    var id_media = "null"; // Allow multiple draggable items

    var dragSources = $('[draggable="true"]');
    dragSources.each(function (index, el) {
      this.addEventListener("dragstart", dragStart);
      this.addEventListener("dragend", dragEnd);
    });

    function dragStart(e) {
      dragSources.each(function (index, el) {
        this.style.opacity = 0.25;
      });
      document.getElementById("header").style.opacity = 0.25;
      $(".drop_zone").addClass("active");
      var panier = document.querySelectorAll(".panier");

      if (!$(panier).hasClass("active")) {
        $(".button_panier").click();
      } // On boucle sur les articles dans le panier pour mettre en surbrillance celles qui peuvent recevoir le média

      this.style.opacity = 1;
      this.classList.add("dragging");
      e.dataTransfer.setData("text/plain", e.target.id);
      id_media = parseInt($(this).attr("data-id")); // Allow multiple dropped targets

      var dropTargets = $('[data-role="drag-drop-container"]');
      dropTargets.each(function (index, el) {
        this.addEventListener("drop", dropped);
        this.addEventListener("dragenter", cancelDefault);
        this.addEventListener("dragover", dragOver);
        this.addEventListener("dragleave", dragLeave);
      });
    }

    function dragEnd(e) {
      dragSources.each(function (index, el) {
        this.style.opacity = 1;
      });
      $(".drop_zone").removeClass("active");
      document.getElementById("header").style.opacity = 1;
      this.classList.remove("dragging");
    }

    function dropped(e) {
      cancelDefault(e);

      if (
        $(this).hasClass("panier_article") ||
        $(this).hasClass("item_presentation")
      ) {
        this.classList.remove("drag_hover");
        var article_index = parseInt($(this).attr("data-index"));
        var article = panier.getItem(article_index);
        var media = user_interface.galerie.find(function (obj) {
          return obj.id === id_media;
        });
        var produit = user_interface.produits.find(function (obj) {
          return obj.id === article.id_produit;
        });

        if (
          article.array_media.length < article.produit.limite &&
          media.type == produit.type
        ) {
          panier.addMediaItem(article_index, id_media);
          user_interface.open_item_photo_section(article_index);
        } else {
          // Ce produit
          if (media.type !== produit.type) {
            user_interface.alert(
              "le média et le produit ne sont pas compatibles."
            );
          } else if (article.array_media.length >= article.produit.limite) {
            user_interface.alert(
              "cet article ne peut contenir plus de " + produit.type + "."
            );
          }
        }
      } else if (
        $(this).hasClass("panier_articles") ||
        $(this).hasClass("drop_zone")
      ) {
        user_interface.openSelectProduct(id_media);
      }
    }

    function dragOver(e) {
      cancelDefault(e);
      this.classList.add("drag_hover");
    }

    function dragLeave(e) {
      this.classList.remove("drag_hover");
    }

    function cancelDefault(e) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  });
});

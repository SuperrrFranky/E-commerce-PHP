$(() => {
  $(document).ready(function () {
    $('input[type="file"]').on("change", function (event) {
      var reader = new FileReader();
      reader.onload = function () {
        $("#profile-photo-preview").attr("src", reader.result);
      };
      reader.readAsDataURL(event.target.files[0]);
    });
  });

  window.addEventListener("beforeunload", function () {
    this.fetch("destroy_session.php", {
      method: "POST",
      credentials: "same-origin",
    });
  });

  // Autofocus
  focusInput();

  //SHOW OR HIDE PASSWORD
  $(".show-password").on("click", function () {
    const $passwordField = $(this).siblings(
      'input[type="password"], input[type="text"]'
    );
    const isPassword = $passwordField.attr("type") === "password";

    $passwordField.attr("type", isPassword ? "text" : "password");
    $(this).text(isPassword ? "Hide" : "Show");
  });

  // Initiate GET request
  $(document).on("click", "[data-get]", (e) => {
    e.preventDefault();
    const url = e.target.dataset.get;
    location = url || location;
});
  // Initiate POST request
  $("[data-post]").on("click", (e) => {
    e.preventDefault();
    const url = e.target.dataset.post;
    const f = $("<form>").appendTo(document.body)[0];
    f.method = "POST";
    f.action = url || location;
    f.submit();
  });

  $("[type=reset]").on("click", (e) => {
    e.preventDefault();
    location = location;
  });

  $("[data-confirm]").on("click", (e) => {
    const text = e.target.dataset.confirm || "Are you sure?";
    if (!confirm(text)) {
      e.preventDefault();
      e.stopImmediatePropagation();
    }
  });

  // Photo preview
  $("label.upload input[type=file]").on("change", (e) => {
    const f = e.target.files[0];
    const img = $(e.target).siblings("img")[0];

    if (!img) return;

    img.dataset.src ??= img.src;

    if (f?.type.startsWith("image/")) {
      img.src = URL.createObjectURL(f);
    } else {
      img.src = img.dataset.src;
      e.target.value = "";
    }
  });

  $(document).on("click", "#searchBtn", function (e) {
    e.preventDefault();
    const searchInput = $("#searchInput").val();

    if ($("body").hasClass("productBody")) {
      getProduct("", "0", "10000", searchInput);
    } else {
      const url = `/page/product/product.php?searchInput=${encodeURIComponent(
        searchInput
      )}`;
      window.location.href = url;
    }
  });

  

  $(document).on("click", ".actionBtn", function (e) {
    e.preventDefault();

    const action = e.target.dataset.action || "wishlist";
    const url = "";
    const f = $("<form>").appendTo(document.body)[0];
    f.method = "POST";
    f.action = url || location;

    $("<input>")
      .attr({
        type: "hidden",
        name: "action",
        value: action,
      })
      .appendTo(f);

    if (action == "cart") {
      const qty = $("#quantity").val();
      $("<input>")
        .attr({
          type: "hidden",
          name: "quantity",
          value: qty,
        })
        .appendTo(f);
    }

    f.submit();
  });

  // Autofocus
  $("form :input:not(button):first").focus();
  $(".err:first").prev().focus();
  $(".err:first").prev().find(":input:first").focus();

  //SHOW OR HIDE PASSWORD
  $(".show-password").on("click", function () {
    const $passwordField = $(this).siblings(
      'input[type="password"], input[type="text"]'
    );
    const isPassword = $passwordField.attr("type") === "password";

    f.submit();
  });
  
  

  if ($("div").hasClass("productBody")) {
    function getProduct(
      category = "",
      minPrice = "0",
      maxPrice = "10000",
      searchInput = ""
    ) {
      $.ajax({
        url: "fetchProduct.php",
        method: "GET",
        data: {
          category: category,
          minPrice: minPrice,
          maxPrice: maxPrice,
          searchInput: searchInput,
        },
        success: function (response) {
          $(".productBody").html(response);
        },
      });
    }

    const urlParams = new URLSearchParams(window.location.search);
    const searchInput = urlParams.get("searchInput") || "";
    const category = urlParams.get("category") || "";
    if (searchInput) {
      getProduct("", "0", "10000", searchInput);
    } else if (category) {
      getProduct(category, "0", "10000");
    }
     else {
      getProduct();
    }

    $(document).on("click", ".filter", function (e) {
      e.preventDefault();
      const category = $(this).val();
      const minPrice = $("#minPrice").val();
      const maxPrice = $("#maxPrice").val();
      getProduct(category, minPrice, maxPrice);
    });

    $(document).on("click", ".reset", function (e) {
      e.preventDefault();
      const category = $(this).val();
      $("#minPrice").val(0);
      $("#maxPrice").val(1000);
      getProduct();
    });
  }

  if ($("div").hasClass("cart-container")) {

    function updateCart(
      productId = "",
      quantity = "",
      status = "pending",
      remove = ""
    ) {
      $.ajax({
        url: "fetchCart.php",
        method: "GET",
        data: {
          productId: productId,
          quantity: quantity,
          status: status,
          remove: remove,
        },
        success: function (response) {
          $(".cart-container").html(response);
        },
      });
    }

    updateCart();
    
    $(document).on("click", ".cart-addBtn", function (e) {
      const id = e.target.dataset.selected;
      const inputSelector = `input[name='${id}-quantity']`;
      const quantityInput = $(inputSelector);
      const currentQuantity = parseInt(quantityInput.val()) || 0;
      const maxValue = $(inputSelector).attr("max");

      if (currentQuantity >= maxValue) {
        alert("You have reached the maximum quantity allowed for this product.");
      } else {
        updateCart(id, currentQuantity + 1);
      }
    });

    $(document).on("click", ".cart-minBtn", function (e) {
      const id = e.target.dataset.selected;
      const inputSelector = `input[name='${id}-quantity']`;
      const quantityInput = $(inputSelector);
      const currentQuantity = parseInt(quantityInput.val()) || 0;
      const maxValue = $(inputSelector).attr("max");

      if (currentQuantity === 1) {
        updateCart(id, "", "", true);
      } else if (currentQuantity > 1) {
        if(currentQuantity>maxValue){
          quantityInput.val(currentQuantity-1);
        }else{
          updateCart(id, currentQuantity - 1);
        }
      }
    });

    $(document).on("change", "input[type='number']", function (e) {
      const id = e.target.dataset.selected;
      const inputSelector = `input[name='${id}-quantity']`;
      const quantityInput = $(inputSelector);
      let currentQuantity = parseInt(quantityInput.val()) || 0;
      const maxValue = $(inputSelector).attr("max");

      if (currentQuantity > maxValue) {
        alert("You have reached the maximum quantity allowed for this product.");
        quantityInput.val(maxValue);
        currentQuantity = maxValue;
      }

      if (currentQuantity < 1) {
        updateCart(id, "", "", true); 
      } else {
        updateCart(id, currentQuantity);
      }
    });

    $(document).on("click", ".cart-check", function (e) {
      const id = e.target.dataset.selected;

      const inputSelector = `input[name='${id}-quantity']`;
      const quantityInput = $(inputSelector);
      const currentQuantity = parseInt(quantityInput.val()) || 0;

      if ($(e.target).is(":checked")) {
        updateCart(id, currentQuantity, "checkOut", "");
      } else {
        updateCart(id, currentQuantity, "pending", "");
      }
    });

    $(document).on("click", ".removeCartBtn", function (e) {
      const id = e.target.dataset.selected;

      updateCart(id, "", "", true);
    });
  }

});

function loadMembers(page) {
  const $searchInput = $('#member_parameters').val().trim();
  $.ajax({
    url: 'fetchMembers.php',
    method: 'POST',
    data: {
      search: $searchInput,
      page: page
    },
    success: function(response) {
      $('#memberSearchResults').html(response);
    },
  });
}

function focusInput(){
  $("form :input:not(button):first").focus();
  $(".err:first").prev().focus();
  $(".err:first").prev().find(":input:first").focus();
}

function resetForm(formID){
  $('#'+formID).find('select').prop('selectedIndex',0);

  $('#'+formID).find('input[type="search"],input[type="text"]').val("");

  $('#'+formID).submit();
}

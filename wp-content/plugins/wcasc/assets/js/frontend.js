(function ($) {
  "use strict";

  // -----------------------------
  // Helpers to detect Blocks
  // -----------------------------
  function hasBlocksCartStore() {
    try {
      return (
        typeof window.wp !== "undefined" &&
        wp.data &&
        typeof wp.data.dispatch === "function" &&
        !!wp.data.dispatch("wc/store/cart")
      );
    } catch (e) {
      return false;
    }
  }
  function replaceFragments(resp) {
    if (resp && resp.fragments) {
      var wasOpen = $("#wcasc-sidebar-cart").hasClass("is-open");
      $.each(resp.fragments, function (selector, html) {
        var $el = $(selector);
        if ($el.length) $el.replaceWith(html);
      });
      // restore open state
      if (wasOpen) openSidebar();
    }
  }

  function isOnBlocksCheckout() {
    return (
      document.querySelector(
        ".wc-block-checkout, .wp-block-woocommerce-checkout"
      ) !== null
    );
  }

  function refreshClassicFragments() {
    if (typeof wc_cart_fragments_params !== "undefined") {
      $(document.body).trigger("wc_fragment_refresh");
    }
  }

  // Notify WooCommerce Blocks and classic fragments that cart changed
  function triggerCartUpdated() {
    // Classic fragments (legacy themes)
    try {
      refreshClassicFragments();
      jQuery(document.body).trigger("wc_fragment_refresh");
    } catch (e) {
      // ignore
    }

    // WooCommerce Blocks: tell blocks to re-fetch cart data
    try {
      document.body.dispatchEvent(
        new CustomEvent("wc-blocks_added_to_cart", {
          detail: {
            // false means blocks will refetch cart data from server
            preserveCartData: false,
          },
        })
      );
    } catch (e) {
      // ignore if CustomEvent not supported
    }
  }

  // -----------------------------
  // Store API (Blocks) helpers
  // -----------------------------
  function storeAddToCart(productId, quantity) {
    var dispatcher = wp.data.dispatch("wc/store/cart");
    return dispatcher.addItemToCart({ id: productId, quantity: quantity });
  }

  function storeUpdateQty(key, quantity) {
    var dispatcher = wp.data.dispatch("wc/store/cart");
    return dispatcher.updateItemFromCart(key, { quantity: quantity });
  }

  function storeRemoveItem(key) {
    var dispatcher = wp.data.dispatch("wc/store/cart");
    return dispatcher.removeItemsFromCart([key]);
  }

  // -----------------------------
  // Existing admin-ajax path
  // -----------------------------
  function ajax(action, data, done) {
    data = data || {};
    data.action = action;
    data.nonce = WCASC_Vars.nonce;

    $.post(WCASC_Vars.ajaxurl, data).done(function (resp) {
      if (resp && resp.fragments) {
        $.each(resp.fragments, function (selector, html) {
          var $el = $(selector);
          if ($el.length) {
            $el.replaceWith(html);
          }
        });
        if (!isOnBlocksCheckout()) {
          openSidebar();
        }
      }
      if (typeof done === "function") done(resp);
    });
  }

  function openSidebar() {
    $("#wcasc-overlay").removeClass("is-open");
    $("#wcasc-sidebar-cart").attr("aria-hidden", "true").removeClass("is-open");
    $("body").removeClass("wcasc-open");

    $("#wcasc-overlay").addClass("is-open");
    $("#wcasc-sidebar-cart").attr("aria-hidden", "false").addClass("is-open");
    $("body").addClass("wcasc-open");
  }

  function closeSidebar() {
    $("#wcasc-overlay").removeClass("is-open");
    $("#wcasc-sidebar-cart").attr("aria-hidden", "true").removeClass("is-open");
    $("body").removeClass("wcasc-open");
  }

  // -----------------------------
  // Formatting helpers (uses the aside data attributes)
  // -----------------------------
  function getCurrencySettings() {
    var $sidebar = $("#wcasc-sidebar-cart");
    var currency =
      $sidebar.data("currency") ||
      (typeof WCASC_Vars !== "undefined" && WCASC_Vars.currency) ||
      "USD";
    var decimals = parseInt($sidebar.data("decimals"), 10);
    if (isNaN(decimals)) decimals = 2;
    return { currency: currency, decimals: decimals };
  }

  function formatCurrency(value) {
    var cfg = getCurrencySettings();
    var n = Number(value || 0);
    try {
      return new Intl.NumberFormat(undefined, {
        style: "currency",
        currency: cfg.currency,
        minimumFractionDigits: cfg.decimals,
        maximumFractionDigits: cfg.decimals,
      }).format(n);
    } catch (e) {
      // fallback: symbol + fixed
      return cfg.currency + " " + n.toFixed(cfg.decimals);
    }
  }

  function parsePriceFromText(text) {
    if (!text) return NaN;
    var cleaned = String(text).replace(/[^\d\.,\-]/g, "");
    cleaned = cleaned.replace(/\s/g, "");
    // if there is a comma and no dot, treat comma as decimal separator
    if (cleaned.indexOf(",") > -1 && cleaned.indexOf(".") === -1) {
      cleaned = cleaned.replace(",", ".");
    } else {
      cleaned = cleaned.replace(/,/g, "");
    }
    return parseFloat(cleaned);
  }

  // -----------------------------
  // Delegated events
  // -----------------------------
  $(document)
    .on("click", "#wcasc-cart-toggle", function (e) {
      e.preventDefault();
      openSidebar();
    })
    .on(
      "click",
      "#wcasc-overlay, #wcasc-sidebar-cart .wcasc-close",
      function (e) {
        e.preventDefault();
        closeSidebar();
      }
    )

    // Add to cart from addon button
    .on("click", "[data-wcasc-add]", function (e) {
      e.preventDefault();

      var $btn = $(this);
      var pid = parseInt($btn.attr("data-wcasc-add"), 10);
      if (!pid) return;

      $btn.prop("disabled", true).text("Adding…");

      if (hasBlocksCartStore()) {
        storeAddToCart(pid, 1)
          .then(function () {
            // trigger both legacy fragments and block re-fetch
            triggerCartUpdated();

            // If you want the sidebar for non-block checkouts:
            if (!isOnBlocksCheckout()) {
              openSidebar();
            }

            // keep server in sync (server-side fragments)
            ajax("wcasc_sync_cart", {}, function () {
              // final UI updates
              $btn.prop("disabled", false).text("Added ✓");
            });
          })
          .catch(function (err) {
            console && console.error("WCASC add to cart (Blocks) error:", err);
            // fallback to classic AJAX add
            ajax(
              "wcasc_add_to_cart",
              { product_id: pid, quantity: 1 },
              function () {
                triggerCartUpdated();
                if (!isOnBlocksCheckout()) openSidebar();
                $btn.prop("disabled", false).text("Added ✓");
              }
            );
          });
      } else {
        ajax(
          "wcasc_add_to_cart",
          { product_id: pid, quantity: 1 },
          function () {
            triggerCartUpdated();
            if (!isOnBlocksCheckout()) openSidebar();
            $btn.prop("disabled", false).text("Added ✓");
          }
        );
      }
    })

    // Remove item in sidebar
    .on("click", "#wcasc-sidebar-cart .wcasc-remove-item", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var key = $btn.closest(".wcasc-item").data("cart-key");
      if (!key) return;

      // optimistic remove from UI
      var $item = $btn.closest(".wcasc-item");
      $item.slideUp(200, function () {
        $(this).remove();
      });

      // update badge (optimistic)
      var $badge = $("#wcasc-cart-count");
      if ($badge.length) {
        var currentCount = parseInt($badge.text(), 10) || 0;
        $badge.text(Math.max(0, currentCount - 1));
      }

      if (hasBlocksCartStore()) {
        storeRemoveItem(key)
          .then(function () {
            // refresh and notify
            triggerCartUpdated();
            ajax("wcasc_sync_cart", {}, function () {});
          })
          .catch(function (err) {
            console && console.error("WCASC remove item (Blocks) error:", err);
            ajax("wcasc_sync_cart", {}, function () {});
          });
      } else {
        ajax("wcasc_remove_item", { cart_item_key: key }, function (resp) {
          triggerCartUpdated();
          replaceFragments(resp); // ensures sidebar stays open
        });
      }
    })

    // Quantity inc/dec in sidebar (optimistic update + server sync)
    .on(
      "click",
      "#wcasc-sidebar-cart .wcasc-qty-inc, #wcasc-sidebar-cart .wcasc-qty-dec",
      function (e) {
        e.preventDefault();
        var $btn = $(this);
        var $item = $btn.closest(".wcasc-item");
        var key = $item.data("cart-key");
        var $input = $item.find(".wcasc-qty-input");
        var prevVal = parseInt($input.val(), 10) || 0;
        var newVal = prevVal;

        if ($btn.hasClass("wcasc-qty-inc")) newVal = prevVal + 1;
        else newVal = Math.max(0, prevVal - 1);

        // optimistic UI update
        $input.val(newVal);

        // unit price (from data-attr or fallback)
        var unitPrice = parseFloat($item.data("unit-price"));
        if (isNaN(unitPrice)) {
          unitPrice = parsePriceFromText(
            $item.find(".wcasc-price-unit").text()
          );
        }
        if (isNaN(unitPrice)) unitPrice = 0;

        // update line total
        var newLineTotal = unitPrice * newVal;
        $item.find(".wcasc-price-line").text(formatCurrency(newLineTotal));
        $item
          .find(".wcasc-price-line")
          .attr(
            "data-line-total",
            newLineTotal.toFixed(getCurrencySettings().decimals)
          );

        // update subtotal (optimistic)
        var $subtotalStrong = $("#wcasc-sidebar-cart .wcasc-subtotal strong");
        if ($subtotalStrong.length) {
          var subtotalRaw =
            parseFloat($subtotalStrong.attr("data-subtotal-raw")) || 0;
          var delta = (newVal - prevVal) * unitPrice;
          var newSubtotalRaw = subtotalRaw + delta;
          $subtotalStrong.attr("data-subtotal-raw", newSubtotalRaw);
          $subtotalStrong.text(formatCurrency(newSubtotalRaw));
        }

        // update cart count badge (optimistic)
        var $badge = $("#wcasc-cart-count");
        if ($badge.length) {
          var currentCount = parseInt($badge.text(), 10) || 0;
          var deltaItems = newVal - prevVal;
          $badge.text(Math.max(0, currentCount + deltaItems));
        }

        // perform server update
        if (hasBlocksCartStore()) {
          storeUpdateQty(key, newVal)
            .then(function () {
              triggerCartUpdated();
              ajax("wcasc_sync_cart", {}, function () {});
            })
            .catch(function (err) {
              console && console.error("WCASC update qty (Blocks) error:", err);
              // fallback to server ajax; sync fragments afterwards
              ajax(
                "wcasc_update_qty",
                { cart_item_key: key, quantity: newVal },
                function (resp) {
                  triggerCartUpdated();
                  openSidebar(); // <-- force sidebar to stay open
                }
              );
            });
        } else {
          ajax(
            "wcasc_update_qty",
            { cart_item_key: key, quantity: newVal },
            function (resp) {
              // if error - request full server fragment refresh to correct UI
              if (!resp || (resp.success === false && !resp.fragments)) {
                ajax("wcasc_sync_cart", {}, function (syncResp) {
                  replaceFragments(syncResp);
                });
              } else {
                replaceFragments(resp); // ensures sidebar stays open
                triggerCartUpdated();
              }
            }
          );
        }
      }
    )

    // Quantity input change in sidebar (user typed a number)
    .on("change", "#wcasc-sidebar-cart .wcasc-qty-input", function () {
      var $input = $(this);
      var $item = $input.closest(".wcasc-item");
      var key = $item.data("cart-key");
      var newVal = Math.max(0, parseInt($input.val(), 10) || 0);

      // get previous line total (data attribute)
      var prevLine =
        parseFloat($item.find(".wcasc-price-line").attr("data-line-total")) ||
        0;
      var unitPrice = parseFloat($item.data("unit-price"));
      if (isNaN(unitPrice)) {
        unitPrice = parsePriceFromText($item.find(".wcasc-price-unit").text());
      }
      if (isNaN(unitPrice)) unitPrice = 0;

      var prevVal = unitPrice ? Math.round(prevLine / unitPrice) : 0;

      // optimistic UI update
      $input.val(newVal);
      var newLineTotal = unitPrice * newVal;
      $item.find(".wcasc-price-line").text(formatCurrency(newLineTotal));
      $item
        .find(".wcasc-price-line")
        .attr(
          "data-line-total",
          newLineTotal.toFixed(getCurrencySettings().decimals)
        );

      // update subtotal
      var $subtotalStrong = $("#wcasc-sidebar-cart .wcasc-subtotal strong");
      if ($subtotalStrong.length) {
        var subtotalRaw =
          parseFloat($subtotalStrong.attr("data-subtotal-raw")) || 0;
        var delta = (newVal - prevVal) * unitPrice;
        var newSubtotalRaw = subtotalRaw + delta;
        $subtotalStrong.attr("data-subtotal-raw", newSubtotalRaw);
        $subtotalStrong.text(formatCurrency(newSubtotalRaw));
      }

      // update cart count badge (optimistic)
      var $badge = $("#wcasc-cart-count");
      if ($badge.length) {
        var currentCount = parseInt($badge.text(), 10) || 0;
        var deltaItems = newVal - prevVal;
        $badge.text(Math.max(0, currentCount + deltaItems));
      }

      // server update
      if (hasBlocksCartStore()) {
        storeUpdateQty(key, newVal)
          .then(function () {
            triggerCartUpdated();
            ajax("wcasc_sync_cart", {}, function () {});
          })
          .catch(function (err) {
            console && console.error("WCASC update qty (Blocks) error:", err);
            ajax(
              "wcasc_update_qty",
              { cart_item_key: key, quantity: newVal },
              function (resp) {
                if (!resp || resp.success === false) {
                  ajax("wcasc_sync_cart", {}, function () {});
                } else {
                  triggerCartUpdated();
                }
              }
            );
          });
      } else {
        ajax(
          "wcasc_update_qty",
          { cart_item_key: key, quantity: newVal },
          function (resp) {
            if (!resp || (resp.success === false && !resp.fragments)) {
              ajax("wcasc_sync_cart", {}, function () {});
            } else {
              triggerCartUpdated();
            }
          }
        );
      }
    });

  /**
   * -----------------------------------------------------------------
   *  KEEP THE SIDEBAR OPEN AFTER A WOO-COMMERCE FRAGMENT REFRESH
   * -----------------------------------------------------------------
   */
  var sidebarWasOpen = false;

  // 1. before WooCommerce starts refreshing fragments
  $(document.body).on("wc_fragment_refresh", function () {
    sidebarWasOpen = $("#wcasc-sidebar-cart").hasClass("is-open");
  });

  // 2. after WooCommerce replaced the fragments
  $(document.body).on(
    "wc_fragments_loaded wc_fragments_refreshed wc_fragment_refreshed",
    function () {
      if (sidebarWasOpen) {
        openSidebar();
      }
      sidebarWasOpen = false;
    }
  );

})(jQuery);
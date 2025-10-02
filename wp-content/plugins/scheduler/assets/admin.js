jQuery(document).ready(function($) {
    // Add new row
    $("#add-form-row").on("click", function() {
        let $clone = $(".sqv-form-row").first().clone();
        $clone.find("input, select").val(""); // reset values
        $("#sqv-forms-wrapper").append($clone);
    });

    // Remove row
    $(document).on("click", ".sqv-remove-row", function() {
        if ($(".sqv-form-row").length > 1) {
            $(this).closest("tr").remove();
        } else {
            alert("At least one row is required.");
        }
    });
});

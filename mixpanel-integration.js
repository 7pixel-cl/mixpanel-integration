jQuery(document).ready(function ($) {
    var trackButtonClick = function () {
        var buttonLabel = $(this).data("mixpanel-label") || $(this).text();
        mixpanel.track("Button Click", {
            "Button Label": buttonLabel
        });
    };

    $(".mixpanel-track-btn").on("click", trackButtonClick);

    $("form.search-form").on("submit", function (e) {
        e.preventDefault();
        var searchInput = $(this).find("input[type='search']");
        var searchTerm = searchInput.val();
        $.post(mixpanel_integration_data.ajax_url, {
            action: "track_search",
            search_term: searchTerm
        }, function () {
            window.location.href = "/?s=" + encodeURIComponent(searchTerm);
        });
    });
});

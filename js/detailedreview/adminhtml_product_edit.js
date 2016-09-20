document.observe("dom:loaded", function() {
    'use strict';
    var popularity_by_sells = $('popularity_by_sells');
    var popularity_by_reviews = $('popularity_by_reviews');
    var popularity_by_rating = $('popularity_by_rating');

    if (popularity_by_sells) {
        popularity_by_sells.disable();
    }
    if (popularity_by_reviews) {
        popularity_by_reviews.disable();
    }
    if (popularity_by_rating) {
        popularity_by_rating.disable();
    }
});

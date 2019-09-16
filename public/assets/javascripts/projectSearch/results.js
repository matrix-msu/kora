var Kora = Kora || {};
Kora.ProjectSearch = Kora.ProjectSearch || {};

Kora.ProjectSearch.Results = function() {
    function windowLocation(key, value) {
        var keywords = (key == 'keywords' ? value : getURLParameter('keywords'));
        var method = (key == 'method' ? value : getURLParameter('method'));
        var forms = (key == 'forms%5B%5D' ? value : getURLParameter('forms%5B%5D'));
        var projects = (key == 'projects%5B%5D' ? value : getURLParameter('projects%5B%5D'));
        var pageCount = (key == 'page-count' ? value : getURLParameter('page-count'));
        var order = (key == 'order' ? value : getURLParameter('order'));
        var page = (key == 'page' ? value : getURLParameter('page'));

        var parameters = [];
        if (keywords) { parameters.push("keywords=" + keywords); }
        if (method) { parameters.push("method=" + method); }
        if (forms) { parameters.push("forms%5B%5D=" + forms); }
        if (projects) { parameters.push("projects%5B%5D=" + projects); }
        if (pageCount) { parameters.push("page-count=" + pageCount); }
        if (order) { parameters.push("order=" + order); }
        if (page) { parameters.push("page=" + page); }

        return (parameters ? window.location.pathname + "?" + parameters.join("&") : window.location.pathname);
    }

    function initializePaginationRouting() {
        var $pagination = $('.pagination-js');
        var $pageLink = $pagination.find('.page-link-js');

        $pageLink.click(function(e) {
            e.preventDefault();

            var $this = $(this);
            var toPage = $this.attr('href').replace('#', '');

            window.location = windowLocation('page', toPage);
        });
    }

    function initializeSearchLoadingIcon() {
        $('.submit-search-js').click(function(e) {
            display_loader();
        });
    }

    initializePaginationRouting();
    initializeSearchLoadingIcon();
}
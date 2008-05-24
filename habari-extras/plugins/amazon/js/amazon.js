var amazonSearch = {
        query: {},
	init: function() {
		$('#amazon-search').click(function () {
			amazonSearch.search();
		});
                $('#amazon-keywords').keypress(function (e) {
                        if (e.keyCode == 13) { // Enter
                            amazonSearch.search();
                            return false;
                        }
                });
	},
	search: function() {
                amazonSearch.startSpinner();

		amazonSearch.query= {};
		amazonSearch.query['search_index'] = $('#amazon-search-index' ).children("option[@selected]").val();
		amazonSearch.query['keywords'] = $('#amazon-keywords' ).val();
                amazonSearch.query['page'] = 1;
		$.post(habari.url.habari + '/admin_ajax/amazon_search', amazonSearch.query, amazonSearch.searchShow, 'json');
	},
	searchShow: function(result) {
                amazonSearch.stopSpinner();

		if (result.errorMessage) {
			humanMsg.displayMsg(result.errorMessage);
			return;
		}
		
		var amazon_result = $('#amazon-result');
		amazon_result.empty();
		var html = '';
                var nav = '';

                nav += '<div class="amazon-result-nav">';
                if ( result.HasPrev ) {
                    nav += '<a href="#amazon-result" class="amazon-prev"><img src="' + habari.url.habari + '/user/plugins/amazon/img/backward.png" alt="Prev" /></a>';
                } else {
                    nav += '<img src="' + habari.url.habari + '/user/plugins/amazon/img/backward-disabled.png" alt="Prev" />';
                }
                nav += '<span>' + result.Start + ' - ' + result.End + ' of ' + result.TotalResults + '</span>';
                if ( result.HasNext ) {
                    nav += '<a href="#amazon-result" class="amazon-next"><img src="' + habari.url.habari + '/user/plugins/amazon/img/foward.png" alt="Next" /></a>';
                } else {
                    nav += '<img src="' + habari.url.habari + '/user/plugins/amazon/img/foward-disabled.png" alt="Next" />';
                }
                nav += '</div>';
                html += '<div class="container">' + nav;
		$( result.Items ).each( function() {
			html += '<div style="float: left; width: 80px;">';
			html += "<a href=\"#\" onclick=\"javascript: amazonSearch.insert('" + this.ASIN + "');\">";
			html += '<img src="' + this.SmallImageURL + '" width="' + this.SmallImageWidth + '" height="' + this.SmallImageHeight + '" alt="" /></a>';
			html += '</div><div style="float: left; margin-left: 8px;">';
			html += '<div class="amazon-search-title">';
			html += "<a href=\"#\" style=\"color: #ffffff;\" onclick=\"javascript: amazonSearch.insert('" + this.ASIN + "');\">";
			html += this.Title + ' (' + this.Binding + ')</a></div>';
			html += '<div class="amazon-search-price">' + this.Price + '</div>';
			html += '</div><div class="amazon-search-clear" style="clear: both;"></div>';
		});
                html += nav + '</div>';
		amazon_result.html(html);
                $('.amazon-prev').click(function () {
                    amazonSearch.prevPage();
                });
                $('.amazon-next').click(function () {
                    amazonSearch.nextPage();
                });
	},
	insert: function(asin)
	{
		spinner.start();

		var query= {};
		query['asin'] = asin;
		$.post(habari.url.habari + '/admin_ajax/amazon_insert', query, amazonSearch.insertDo, 'json');
	},
	insertDo: function(result)
	{
		spinner.stop();

		if (result.errorMessage) {
			humanMsg.displayMsg(result.errorMessage);
			return;
		}

		habari.editor.insertSelection(result.html);
	},
	startSpinner: function() {
		$('#amazon-result').html('<div class="container">Searching...</div>');
	},
	stopSpinner: function () {
		$('#amazon-result').empty();
	},
        prevPage: function () {
                amazonSearch.startSpinner();

                amazonSearch.query['page']--;
		$.post(habari.url.habari + '/admin_ajax/amazon_search', amazonSearch.query, amazonSearch.searchShow, 'json');
        },
        nextPage: function () {
                amazonSearch.startSpinner();

                amazonSearch.query['page']++;
		$.post(habari.url.habari + '/admin_ajax/amazon_search', amazonSearch.query, amazonSearch.searchShow, 'json');
        }
}

$(document).ready(function(){
	amazonSearch.init();
});
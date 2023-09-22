function showTypeOptions( type ) {
	if (type == 'all') {
		jQuery('#excludePages, #excludePosts').show();
		jQuery('#categories, #pages, #tags, #postTypes, #posts').hide();
	} else if (type == 'pages') {
		jQuery('#pages').show();
		jQuery('#categories, #tags, #excludePages, #excludePosts, #postTypes, #posts').hide();
	} else if (type == 'posts') {
		jQuery('#posts').show();
		jQuery('#pages, #categories, #excludePages, #excludePosts, #tags, #postTypes').hide();
	} else if (type == 'categories') {
		jQuery('#categories').show();
		jQuery('#pages, #tags, #postTypes, #excludePages, #excludePosts, #posts').hide();
	} else if (type == 'postTypes') {
		jQuery('#postTypes').show();
		jQuery('#categories, #tags, #pages, #excludePages, #excludePosts, #posts').hide();
	} else if (type == 'tags') {
		jQuery('#tags').show();
		jQuery('#categories, #pages, #postTypes, #excludePages, #excludePosts, #posts').hide();
	} else if (type == 'none') {
		jQuery('#categories, #pages, #postTypes, #excludePages, #excludePosts, #posts, #tags').hide();
	} else {
		jQuery('#pages, #categories, #tags, #postTypes, #posts').hide();
	}
}

jQuery(function($) {
	$('#pages select, #posts select, #categories select, #postTypes select, #tags select, #excludePages select, #excludePosts select').selectize();
});
initializeBlock = function() {
	const content = document.getElementsByClassName('two-col-sec');

	return content;
}

if( window.acf ) {
	window.acf.addAction( 'render_block_preview/type=two-col-content', initializeBlock );
}
else {
	initializeBlock();
}
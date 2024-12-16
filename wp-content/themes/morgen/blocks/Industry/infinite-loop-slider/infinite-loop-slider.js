initializeBlock = function() {
	const content = document.getElementsByClassName('loop-slider');

	return content;
}

if( window.acf ) {
	window.acf.addAction( 'render_block_preview/type=infinite-loop-slider', initializeBlock );
}
else {
	initializeBlock();
}
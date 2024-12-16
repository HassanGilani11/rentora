initializeBlock = function() {
    const carousels = document.getElementsByClassName('contact-project-detail-hero-container');

    // Loop through all carousels.
    for (var i = 0; i < carousels.length; i++) {
        var flkty = new Flickity(carousels[i], {
            cellAlign: 'left',
            contain: true,
            wrapAround: true,
            draggable: false,
        });
    }
}

if (window.acf) {
    window.acf.addAction('render_block_preview/type=hero', initializeBlock);
} else {
    initializeBlock();
}

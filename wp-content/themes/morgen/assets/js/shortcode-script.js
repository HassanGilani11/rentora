jQuery(document).ready( function($) {
    $(document).on('click', '.tabs-view-more-posts', function( e ){
        e.preventDefault();
        const $currElem = $(this);
        const data      = $currElem.closest('.sc-tab-content').data('args');
        const maxpage   = parseInt( $currElem.closest('.sc-tab-content').attr('data-maxpage') );
        let page        = parseInt( $currElem.closest('.sc-tab-content').attr('data-page') );
        page += 1;
        $.ajax({
            type 	: 'post',
            dataType : 'html',
            url 	: morgen.ajaxurl,
            data 	: {
                action : 'morgen_paginate_tab',
                security: morgen.security,
                data : data,
                page: page
            },
            beforeSend:function(){
                $currElem.prepend('<div class="tabs-loader"></div>');
            },
            success:function(res){
                $currElem.find('.tabs-loader').remove();
                $currElem.closest('.sc-tab-content').attr('data-page', page );
                $currElem.closest('.sc-tab-content').children('.tab-content-wrapper').append( res );
                console.log( maxpage, page );
                if( page >= maxpage){
                    $currElem.closest('.view-more-posts-btn-wrap').remove();
                }
            }
        });
    })
});
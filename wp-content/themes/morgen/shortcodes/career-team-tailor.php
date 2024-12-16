<section class="career-team-tailor">
    <div class="teamtailor-jobs-widget" data-teamtailor-limit="20" data-teamtailor-pagination="true" data-teamtailor-popup="true" data-teamtailor-api-key="NWFW5rtpEuMrKbtVWvE9r5-Zs8dw6HXgC7EX4-c5"></div>
</section>
<style>
    .career-team-tailor .teamtailor-jobs__job-title {
        font-family: 'TroisMilleRegular';
        font-weight: 400;
        color: #1E3E37;
        text-decoration: none;
        font-size: 32px;
        padding-right: 50px;
    }
    .career-team-tailor .teamtailor-jobs__job {
        width: 100%;
        margin: 0 auto;
        background-color: #fff;
        padding: 30px;
        margin-bottom: 20px;
        max-width: 768px;
        position: relative;
    }
    .career-team-tailor .teamtailor-jobs__job:after {
        content: '';
        background-image: url(/wp-content/uploads/2024/10/Group-13.png);
        position: absolute;
        width: 30px;
        height: 30px;
        top: 30px;
        right: 50px;
    }
    .career-team-tailor .teamtailor-position, .teamtailor-location {
        margin-bottom: 1rem;
    }
    .career-team-tailor .teamtailor-location i {
        margin-right: 5px;
    }
    @media only screen and (max-width: 600px){
        .career-team-tailor .teamtailor-jobs__job-title {
            font-size: 24px;
            word-break: break-all;
        }
        .career-team-tailor .teamtailor-jobs__job:after {
            top: 30px;
            right: 30px;
        }
    }
</style>
<script>
    var checkExist = setInterval(function() {
    if ($('.teamtailor-jobs__job-wrapper').length) {
        // Element has been loaded
        console.log('Element is now available');
        
        // Clear the interval to stop further checks
        clearInterval(checkExist);
        
        $('.teamtailor-jobs__job').each(function(){
            var pos_wrap = $(this).append('<div class="teamtailor-position"></div>').find('.teamtailor-position');
            $(this).find('.teamtailor-jobs__department').appendTo(pos_wrap);

            var loc_wrap = $(this).append('<div class="teamtailor-location"></div>').find('.teamtailor-location');
            $('<i class="fa-solid fa-location-dot"></i>').appendTo(loc_wrap);
            $(this).find('.teamtailor-jobs__location').appendTo(loc_wrap);
            $('<span>, </span>').appendTo(loc_wrap);
            $(this).find('.teamtailor-jobs__region').appendTo(loc_wrap);

            $('.teamtailor-jobs__job-info').hide();
        });
    }
    }, 100); // Check every 100 milliseconds
</script>
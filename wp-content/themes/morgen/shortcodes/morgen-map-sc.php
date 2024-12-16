<section class="morgen-map-wrapper">
    <div id="map" tabindex="-1"></div>
</section>
<script src="/wp-content/themes/morgen/assets/js/mapdata.js?v=1.2"></script>
<script src="/wp-content/themes/morgen/assets/js/worldmap.js"></script>
<style>
    .morgen-map-wrapper {
        position: relative;
        overflow: hidden;
        display: flex;
		justify-content: center;
		width: 100% !important;
    }
    .morgen-map-wrapper a[href="https://simplemaps.com"] {
        height: 0px;
        bottom: -10px;
        position: absolute;
    }
    .morgen-map-wrapper #tt_sm_map {
        max-width: 300px !important;
        width: 100%;
    }
	.morgen-map-wrapper #map {
		bottom: 120px;
		position: relative;
		left: 80px;
	}
	@media only screen and (max-width: 767px){
		.morgen-map-wrapper #tt_sm_map {
			max-width: 200px !important;
			left: 40% !important;
		}
	}
	@media only screen and (max-width: 480px){
		.morgen-map-wrapper #map {
			left: 120px;
		}
	}
</style>
<?php if (isset($_GET['zd_echo'])) exit($_GET['zd_echo']); ?>
<x-filament-panels::page>
    <style>
        .fi-page-header-main-ctn{
            padding: 0rem !important;
        }
        .fi-main.fi-width-full{
            padding: 0rem !important;
        }
        .fi-page-content {
            padding: 0rem !important;
        }
    </style>

<div class="h-full">

    <iframe
        style="width: 100%; height:777px; overflow: hidden;"
        class="h-full"
        src="https://mentor-softphone.vercel.app"
        style="border-width:0; min-width:100%"
        frameborder="0"
        scrolling="no">
    </iframe>

</div>
</x-filament-panels::page>
<script src="https://my.zadarma.com/webphoneWebRTCWidget/v9/js/loader-phone-lib.js?sub_v=1"></script>
<script src="https://my.zadarma.com/webphoneWebRTCWidget/v9/js/loader-phone-fn.js?sub_v=1"></script>
<script>
    if (window.addEventListener) {
        window.addEventListener('load', function() {
            zadarmaWidgetFn(
                '5fff10dd7926e1a67da5',
                '86994-420',
                'rounded', /*square|rounded*/
                'en', /*ru, en, es, fr, de, pl, ua*/
                true,
                {right:'10px',bottom:'5px'}
            );
        }, false);
    } else if (window.attachEvent) {
        window.attachEvent('onload', function(){
            zadarmaWidgetFn(
                '5fff10dd7926e1a67da5',
                '86994-420',
                'rounded', /*square|rounded*/
                'en', /*ru, en, es, fr, de, pl, ua*/
                true,
                {right:'10px',bottom:'5px'}
             );
        });
    }
</script>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="sploit" content="{{ csrf_token() }}">
    <title>{{ $page->title }}</title>
       <link rel="stylesheet" href="https://cdn.prod.website-files.com/6750141970e01af85c9dd0ab/css/galvanite.webflow.shared.f100ec936.min.css" />
    <link
    href="https://cdn.prod.website-files.com/6750141970e01af85c9dd0ab/css/galvanite.webflow.shared.f100ec936.min.css"
    rel="stylesheet" type="text/css" integrity="sha384-8QDsk2d28A71MOZJ8pUvaADtU/HY8Wkh3pD+vvav14RXf54K1pgmXH4LaEiGgWRo"
    crossorigin="anonymous" />
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <style>
    html.w-mod-js:not(.w-mod-ix3) :is([reveal="text"], [reveal="fade"], .bento-h) {
      visibility: hidden !important;
    }
  </style>
    <script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/gsap.min.js" type="text/javascript"></script>
  <script src="https://cdn.prod.website-files.com/gsap/3.15.0/SplitText.min.js" type="text/javascript"></script>
  <script src="https://cdn.prod.website-files.com/gsap/3.15.0/ScrollTrigger.min.js" type="text/javascript"></script>
  <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/gsap@3.15.0/dist/gsap.min.js"></script>
  
       @if ($css && strlen($css) > 200)
        <style>{!! \CybertronianKelvin\Graper\Helpers\GraperHelper::stripLayerDirectives($css) !!}</style>
    @endif
</head>
<body class="antialiased">
    <script src="https://cdn.tailwindcss.com"></script>
    {!! $html !!}

  <script type="text/javascript" src="https://assets.calendly.com/assets/external/widget.js" async></script>
  <script src="https://d3e54v103j8qbb.cloudfront.net/js/jquery-3.5.1.min.dc5e7f18c8.js?site=6750141970e01af85c9dd0ab"
    type="text/javascript" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
    crossorigin="anonymous"></script>
  <script src="https://cdn.prod.website-files.com/6750141970e01af85c9dd0ab/js/webflow.schunk.f2efb3c5440a81cf.js"
    type="text/javascript" integrity="sha384-VK02VJvMib7ytrF5M2joPWNtDQs366FZgEQ5B+Iwnjx0WBnwQXfootyziSYxdrmS"
    crossorigin="anonymous"></script>
  <script src="https://cdn.prod.website-files.com/6750141970e01af85c9dd0ab/js/webflow.schunk.dcbc902efc78c9b4.js"
    type="text/javascript" integrity="sha384-uhB6kk/2D62JC0N8+G8tPZlAaMcWUseor00Mym2j+TIszedpvjqr66+LvGG+7jYw"
    crossorigin="anonymous"></script>
  <script src="https://cdn.prod.website-files.com/6750141970e01af85c9dd0ab/js/webflow.ba0ff827.e0352142518be92f.js"
    type="text/javascript" integrity="sha384-+rNOhpAKmd/PclfaBTKEmYbfJTrzD5Hy+Ra2r/lWXPc7kBH6msENvMAqIAn4lMf7"
    crossorigin="anonymous"></script>


</body>
</html>
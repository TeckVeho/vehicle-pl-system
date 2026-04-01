<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Izumi Cloud</title>
</head>
<body>
    <div id="app">
        <app></app>
    </div>
    <div id="dusktext"></div>
    <script src="{{ mix('js/vendor.js') }}"></script>
    <script src="{{ mix('js/manifest.js') }}"></script>
    <script src="{{ mix('js/app.js') }}"></script>
    <script>
        var url = new URL(window.location.href);
        var dusktext = url.searchParams.get("dtext");
        var dclass = url.searchParams.get("dstatus")==1 ?  "success" : "danger" ;
        let element=document.getElementById("dusktext");
        if(!dusktext) element.style.visibility = "hidden";
        element.innerHTML=dusktext;
        element.classList.add(dclass);
        //lang
        let isShowLang=url.searchParams.get("lang");

        if(isShowLang){
            setTimeout(function(){
                let lang=document.getElementById('dropdown-lang');
                lang.style.visibility="visible";
            },2000);

        }
    </script>
</body>
</html>

@include('base2.start')

<title>Tab Order | Adidas</title>
</head>

<body id="overlay" class="fadeIn">
    @include('base2.navbar')
    <div id="smooth-wrapper">
        <div id="smooth-content">
            <div class="flex md:flex-row flex-col md:min-h-screen h-screen overflow-hidden">
                @include('base2.sidebar')
                @yield('content-order') 
            </div>
        </div>
    </div>

    @include('base2.end')
<nav class="navbar navbar-expand-lg navbar-light sticky-top bg-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="{{ url('/') }}"><h2 class="logo">eAuctions</h2></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav mb-2 mb-lg-0">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            Categories
          </a>
          <ul class="dropdown-menu" aria-labelledby="navbarDropdown" style="font-size:0.9em">
            <li><a class="dropdown-item" href="{{ url('/category/art') }}">Art</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/technology') }}">Technology</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/books') }}">Books</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/automobilia') }}">Automobilia</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/coins-stamps') }}">Coins & Stamps</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/music') }}">Music</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/toys') }}">Toys</a></li>
            <li><a class="dropdown-item" href="{{ url('/category/fashion') }}">Fashion</a></li>

          </ul>
        </li>

      </ul>
      <form class="d-flex flex-fill m-5 mt-2 mb-0" method="post" action="{{ route('search') }}">
        @csrf
        <input type="hidden" name="navIdentifier" value="nav" >
        <input class="form-control" type="search" name="textSearch" placeholder="Search" aria-label="Search">
        <button class="btn" type="submit">Search</button>
      </form>

      
          @if (Auth::guest())
          <ul class="navbar-nav ms-auto">
            <li>
              <a class="nav-link navbar-content-bold rounded-pill" href="{{ route('login') }}">Sign in</a>
            </li>
            <li>
              <a class="nav-link navbar-content-bold rounded-pill" href="{{ route('register') }}">Sign up</a>
            </li>
          </ul>
          @else
          <div class="d-flex justify-content-center align-items-center">
          <ul class="navbar-nav ms-auto">
            <li>
              <a class="nav-link position-relative text-white navbar-content-bold rounded-pill" href="{{ url('/users/' . Auth::id()) }}">
                @if(Auth::user()->hasUnreadNotifications())
                <span class="notification-alert position-absolute top-100 start-100 translate-middle p-2 bg-danger border border-light rounded-circle">
                  <span class="visually-hidden">New alerts</span>
                </span>
                @endif
                <img class="img-fluid img-thumbnail rounded-circle" style="width: 50px; height: 50px;" src={{ asset('assets/' . ( Auth::user()->picture ? Auth::user()->picture : 'profile_pictures/default.png')) }} alt="">
              </a>
            </li>
            <li>
                <a class="my-auto nav-link navbar-content-bold p-4" href="{{ route('logout') }}">Log out</a>
            </li>
          </ul>
          </div>
          @endif
    </div>
  </div>
</nav>
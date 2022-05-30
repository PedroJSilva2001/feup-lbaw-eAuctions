@extends('layouts.app')

@section('title', "Search")

@section('head')
@endsection

@section('content')

<div class="container-fluid mt-4">
    <!--BreadCrumbs-->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Search</li>
        </ol>
    </nav>

    @if (isset($textSearch) && $textSearch != "")
        <h1 class="title-text"> Results for "{{ $textSearch }}" </h1>
    @else
        <h1 class="title-text"> All auctions </h1>
    @endif

    <div class="container-fluid row" style="padding: 0em 3em">
        <div class="filter-area col-3 d-flex flex-column justify-content-start">
            <h3> Filter results</h3>
            <nav id="sidebarMenu">
                <form id="search-general" method="post" action="{{ route('search') }}" class="filter-form d-flex flex-column justify-content-center">
                    @csrf
                    <input type="hidden" name="textSearch" value= {{$textSearch}}>
                    <ul class="nav flex-column accordion accordion-flush " id="accordionParent">
                        @if (isset($conditions) && count($conditions) != 0)
                            <div class="accordion-item">
                                <li class="nav-item">
                                    <h2 class="accordion-header w-100" id="conditionHeader">
                                        <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectCondition"  aria-expanded="false" aria-controls="selectCondition"> 
                                            Condition 
                                        </a>
                                    </h2>
                                    <div class="accordion-collapse collapse" aria-labelledby="#conditionHeader" data-bs-parent="#accordionParent" id="selectCondition" name="condition">
                                        <div class="col form-check fs-3">
                                            <div class="accordion-body">
                                                <?php 
                                                    foreach($conditions as $condition){ 
                                                        echo "<input class='form-check-input' type='checkbox'  name=conditions[] id='".$condition."' value= '".$condition."'>";
                                                        echo "<label class='form-check-label' for=".$condition.">".$condition."</label>";
                                                    }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </div>
                        @else
                        @endif

                        @if (isset($brands) && count($brands) != 0)
                        <div class="accordion-item">
                            <li class="nav-item">
                                <h2 class="accordion-header" id="brandHeader">
                                    <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectBrand"  aria-expanded="false" aria-controls="selectBrand"> 
                                        Brand 
                                    </a>
                                </h2>
                                <div class="accordion-collapse collapse" aria-labelledby="#brandHeader" data-bs-parent="#accordionParent" id="selectBrand" name="brand">
                                    <div class="col form-check fs-3">
                                        <div class="accordion-body">
                                            <?php 
                                                foreach($brands as $brand){
                                                    echo "<input class='form-check-input' type='checkbox' name=brands[] id='".$brand."' value= '".$brand."'>";
                                                    echo "<label class='form-check-label' for=".$brand.">".$brand."</label>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>
                        @else
                        @endif

                        @if (isset($colours) && count($colours) != 0)
                        <div class="accordion-item">
                            <li class="nav-item">
                                <h2 class="accordion-header" id="colourHeader">
                                    <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectColour"  aria-expanded="false" aria-controls="selectColor"> 
                                        Colour
                                    </a>
                                </h2>
                                <div class="accordion-collapse collapse" aria-labelledby="#colourHeader" data-bs-parent="#accordionParent" id="selectColour" name="colour">
                                    <div class="col form-check fs-3">
                                        <div class="accordion-body">
                                            <?php 
                                                foreach($colours as $colour){ 
                                                    echo "<input class='form-check-input' type='checkbox' name=colours[] id='".$colour."' value= '".$colour."'>";
                                                    echo "<label class='form-check-label' for=".$colour.">".$colour."</label>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>
                        @else
                        @endif

                        @if (isset($years) && count($years) != 0)
                        <div class="accordion-item">
                            <li class="nav-item">
                                <h2 class="accordion-header" id="yearHeader">
                                    <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectYear"  aria-expanded="false" aria-controls="selectYear"> 
                                        Year
                                    </a>
                                </h2>
                                <div class="accordion-collapse collapse" aria-labelledby="#yearHeader" data-bs-parent="#accordionParent" id="selectYear" name="years">
                                    <div class="col form-check fs-3">
                                        <div class="accordion-body">
                                            <?php 
                                                foreach($years as $year){ 
                                                    echo "<input class='form-check-input' type='checkbox' name=years[] id='".$year."' value= '".$year."'>";
                                                    echo "<label class='form-check-label' for=".$year.">".$year."</label>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>
                        @else
                        @endif

                        @if (isset($categories) && count($categories) != 0)
                        <div class="accordion-item">
                            <li class="nav-item">
                                <h2 class="accordion-header" id="yearHeader">
                                    <a class="accordion-button btn accordion-btn-color collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#selectCategory"  aria-expanded="false" aria-controls="selectCategory"> 
                                        Category
                                    </a>
                                </h2>
                                <div class="accordion-collapse collapse" aria-labelledby="#categoryHeader" data-bs-parent="#accordionParent" id="selectCategory" name="category">
                                    <div class="col form-check fs-3">
                                        <div class="accordion-body">
                                            <?php 
                                                foreach($categories as $category){ 
                                                    echo "<input class='form-check-input' type='checkbox' name=categories[] value= '".$category. "'>";
                                                    echo "<label class='form-check-label' for=".$category.">".$category."</label>";
                                                }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        </div>
                        @else
                        @endif
                    </ul>

                    <div class="col-3 text-center my-6 mx-auto">
                        <button href="#" type="submit" id="submit_button" class="btn btn-lg filter-btn">
                            Filter
                        </button>
                    </div>

                </form>
            </nav>
        </div>

        <!-- Search Results -->
        <div class="col-9 d-flex flex-column justify-content-center"> 
            <p class="fs-3 ps-3">
                <?php ;
                    echo $auctions_to_display->total();
                ?>
                Results Found
            </p>
            <main class="col pt-4 px-md-4 h-100">
                <div class="row row row-cols-1 row-cols-sm-2 row-cols-lg-2 row-cols-xl-3 row-cols-xxl-4 d-flex justify-content-start">
                    @foreach ($auctions_to_display as $auction)
                        @include('partials.auction_card', array(
                            'id'            => $auction->id,
                            'title'         => $auction->title,
                            'base_value'    => $auction->base_value,
                            'max_bid'       => $auction->getHighestBidValue(),
                            'time_left'     => $auction->getTimeDifference(),
                            'product_imgs'  => $auction->getAuctionPictures()
                        ))
                    @endforeach
                </div>
                <div class="d-flex">
                    <div class="mx-auto">
                        {{ $auctions_to_display->links() }}
                    </div>
                </div>

            </main>
        </div>
    </div>
</div>

@endsection
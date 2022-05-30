@extends('layouts.app')

@section('content')

<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="#">
            @foreach ($auction->getCategories() as $category)
                <a href="{{ url('/category/' . strtolower(str_replace(' & ', '-', $category->category))) }}"> {{ $category->category }} </a>
                @if(!$loop->last)
                    ,
                @endif
            @endforeach
            </li>
            <li class="breadcrumb-item"><a href= "{{ url('/auctions/' . $auction->id) }}"> {{ $auction->title }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit</li>
        </ol>
    </nav>

    <h2 class="title-text">Edit Auction</h2>

    <form method="POST" action="{{ route('edit', $auction->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="row">
            <div class="col">
                <div class="form-group m-3 forms-info">
                    <label for="title">Auction Title</label>
                    <input id="title" type="text" class="form-control" name="title" value="{{ $auction->title }}" autofocus>
                </div>  

                <div class="form-group m-3 forms-info">
                    <label for="category">Categories</label>
                        <ul class="accordion accordion-flush" id="accordionParent" aria-labelledby="dropdownMenuButton1">
                            <div class="accordion-item">
                                <h2 class="accordion-header w-100" id="categoryHeader">
                                    <a class="accordion-button btn collapsed" data-bs-toggle="collapse" data-bs-target="#selectCategory"  aria-expanded="false" aria-controls="selectCategory"> 
                                        Category
                                    </a>
                                </h2>
                                <div class="accordion-collapse collapse" aria-labelledby="#categoryHeader" data-bs-parent="#accordionParent" id="selectCategory" name="category">
                                    <div class="accordion-body">
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Art'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Art" checked>Art
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Art">Art
                                                @endif
                                            </label>
                                        </li>

                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Technology'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Technology" checked>Technology
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Technology">Technology
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Books'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Books" checked>Books
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Books">Books
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Automobilia'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Automobilia" checked>Automobilia
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Automobilia">Automobilia
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Coins & Stamps'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Coins & Stamps" checked>Coins & Stamps
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Coins & Stamps">Coins & Stamps
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Music'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Music" checked>Music
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Music">Music
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">                                
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Toys'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Toys" checked>Toys
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Toys">Toys
                                                @endif
                                            </label>
                                        </li>
                                        <li class="form-check">
                                            <label class="form-check-label">
                                                @if ($auction->getCategories()->contains('category', 'Fashion'))
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Fashion" checked>Fashion
                                                @else
                                                <input class="form-check-input" type="checkbox" name="category[]"  value="Fashion">Fashion
                                                @endif
                                            </label>
                                        </li>
                                    </div>
                                </div>
                            </div>  
                        </ul>
                </div>

                <div class="form-group m-3 forms-info">
                    <label for="condition">Condition</label>
                    <select class="form-select form-control" name="condition" style="font-size:1em">
                        @if ($auction->condition == null)
                        <option disabled selected value> - </option>
                        @else
                        @endif
                        @if ($auction->condition == "New")
                        <option value="New" selected >New</option>
                        @else
                        <option value="New">New</option>
                        @endif
                        @if ($auction->condition == "Mint")
                        <option value="Mint" selected >Mint</option>
                        @else
                        <option value="Mint">Mint</option>
                        @endif
                        @if ($auction->condition == "Reasonable")
                        <option value="Reasonable" selected >Reasonable</option>
                        @else
                        <option value="Reasonable">Reasonable</option>
                        @endif
                        @if ($auction->condition == "Poor")
                        <option value="Poor" selected >Poor</option>
                        @else
                        <option value="Poor">Poor</option>
                        @endif
                    </select>
                </div>  
            </div>

            <div class="col">
                <div class="m-3">
                    <label for="picture" class="form-label">Images</label>
                    <input class="form-control" type="file" id="picture" placeholder="address"  accept="image/png, image/gif, image/jpeg" name="pictures[]" style="font-size:1em" multiple>
                    <div class="row">
                        <div class="form-check form-switch m-3">
                            <input class="form-check-input" type="checkbox" id="deleteOldPictures" name="deleteOldPictures">
                            <label class="form-check-label" for="deleteOldPictures">Delete Old Pictures?</label>
                        </div>
                    </div>
                </div>
                <div class="ml-2 d-flex flex-wrap" id="preview">
                </div>
            </div>

        </div>  

        <div class="row">
            <div class="form-group m-3 forms-info">
                <label for="description" class="form-label" >Description</label>
                <textarea class="form-control" name="description" id="description" rows="20" style="font-size: 1em">{{ $auction->description }}</textarea>
            </div> 
        </div>  

        <div class="row">
            <div class="col">
                <div class="form-group m-3 forms-info">
                    <label for="brand">Brand</label>
                    <input id="brand" type="text" class="form-control" name="brand" value="{{ $auction->brand }}" autofocus>
                </div>

                <div class="form-group m-3 forms-info">
                    <label for="colour">Colour</label>
                    <input id="colour" type="text" class="form-control" name="colour" value="{{ $auction->colour }}" autofocus>
                </div>

                <div class="form-group m-3 forms-info">
                    <label for="year">Year</label>
                    <input class="form-control" id="year" name="year" value="{{ $auction->year }}" type="text">
                </div>
            </div>
            <div class="col">
                <div class="form-group m-3 forms-info">
                    <label for="end_date">End Date</label>
                    <input class="form-control" id="end_date" name="end_date" value="{{ substr($auction->end_date, 0, 10) . 'T' . substr($auction->end_date, 11) }}" type="datetime-local" style="font-size: 1em">
                </div>

                <div class="form-group m-3 forms-info">
                    <label for="base_value">Starting bid</label>
                    <input class="form-control" id="base_value" name="base_value" value="{{ $auction->base_value }}" type="text">
                </div>
            </div>

        </div>

        <div class="row">
            <div class="form-check form-switch m-3">
                @if ($auction->type == "Private")
                <input class="form-check-input" type="checkbox" id="isPrivate" name="isPrivate" checked>
                @else
                <input class="form-check-input" type="checkbox" id="isPrivate" name="isPrivate">
                @endif
                <label class="form-check-label" for="isPrivate">Private Auction</label>
            </div>
        </div>

        <div class="row">
            @if ($auction->isDraft())
                <div class="col text-center">
                    <button type="submit" class="btn btn-lg enter-info" name="action" value="create">Create Auction</button>
                </div>
                <div class="col text-center">
                    <button type="submit" class="btn btn-lg enter-info" name="action" value="save_draft">Save Changes</button>
                </div>
            @else
                <div class="col text-center">
                    <button type="submit" class="btn btn-lg enter-info" name="action" value="cancel">Cancel Auction</button>
                </div>

                <div class="col text-center">
                    <button type="submit" class="btn btn-lg enter-info" name="action" value="save_auction">Save Changes</button>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div class="col-sm-12">
                <div class="alert  alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                </div>
            </div>
        @elseif (session('error'))
            <div class="col-sm-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                </div>
            </div>
        @endif
    </form> 

</div>

@endsection
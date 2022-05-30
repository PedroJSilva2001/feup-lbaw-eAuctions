@extends('layouts.app')

@section('head')
    <script src={{ asset('js/blockpage.js') }} defer></script>
@endsection

@section('content')


<div class="container mt-5">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb  mx-3">
            <li class="breadcrumb-item"><a href="{{ route('homepage') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('show_profile', $user->id) }}">Profile</a></li>
            <li class="breadcrumb-item active" aria-current="page">Block User</li>
        </ol>
    </nav>

    <h2>Block {{$user->username}}?</h2>

    <form method="POST" action="{{ route('block_user', $user->id) }}" enctype="multipart/form-data">
        {{ csrf_field() }}

        <div class="row">
            <div class="form-group m-3 forms-info">
                <label for="description" class="form-label" >Block Motives</label>
                <textarea autofocus class="form-control" name="description" id="description" rows="20" 
                            style="font-size: 1em ; height: 200px; max-height: 400px ; min-height: 100px"> </textarea>
            </div> 
        </div>

        <div class="row form-group m-3 forms-info">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" value="0" id="block_cb" name="block_status">
                <label class="form-check-label" for="block_cb">Block Indefinitely</label>
            </div>
        </div>

        <div class="row form-group m-3 forms-info">
            <div class="field" style="width:400px">
                <label for="end_date">Block End Date</label>
                <input id="end_date" class="date form-control" type="date" 
                name="end_date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}" 
                style="font-size: 1em">
            </div>
        </div>

        <div class="row">
            <div class="col text-center">
                <button id="block_user_btn" type="submit" class="btn btn-lg enter-info" name="action" value="block">Block User</button>
            </div>
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




<!--<div class="container-sm-8 mx-auto mt-5">
    <h3>Block {{$user->username}}?</h3>

    <form method="POST" action="{{ route('block_user', $user->id) }}" enctype="multipart/form-data">
    {{ csrf_field() }}

        <div class="row">
            <div class="col">
                <div class="form-group m-3 forms-info">
                    <label for="description" class="form-label">Block Motives</label>
                    <textbox class="form-control" name="description" id="description" 
                        style="height: 200px; max-height: 200px ; width: 600px; overflow-x: auto ; word-wrap: normal;
                        overflow-y: scroll;" contenteditable="true"></textarea>
                </div>  
            </div>

        </div> 

        <div class="row mx-5 px-5 pt-1">
            <div class="col-sm-5 form-group m-3 forms-info">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" value="" id="defaultCheck1">
                    <label class="form-check-label" for="defaultCheck1">Block Indefinitely</label>
                </div>
            </div>
            <div class="col-sm-5 form-group m-3 forms-info">
                <div class="field">
                    <label for="brand">Block End Date</label>
                    <input id="brand" class="date form-control" type="date" 
                    name="due_date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}">

                </div>
            </div>
        </div>
            
        <div class="row fixed-center mt-5">
            <div class="col text-center">
                <button type="submit" class="btn btn-lg enter-info">Block</button>
            </div>
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
    

    
</div>-->

@endsection
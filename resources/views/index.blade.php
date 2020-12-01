@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            @if (session('success') or session('status') or session('warning'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }} {{ session('success') }} {{ session('warning') }}
                </div>
            @endif
        </div>
    </div>

    <div id="orderPanel"></div>
    <div id="dataBase" data-producto="{{json_encode($producto)}}"></div>
</div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if (session('status'))
                    <div class="alert alert-success" role="alert">
                        {{ session('status') }}
                    </div>
                @endif
            </div>
        </div>
        <div id="orderPanel" data-vista="OrderEstado" data-orden="{{json_encode($orden)}}"></div>
        <div id="dataBase" data-producto="{{json_encode($producto)}}"></div>
    </div>
@endsection

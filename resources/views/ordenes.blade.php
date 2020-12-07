@extends('layouts.app')

@section('content')

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">{{ __('Ordenes recientes') }}</div>

                    <table class="table table-striped table-hover mb-0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Telefono</th>
                            <th>Estado</th>
                            <th>Ultimo estado</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($ordenes as $orden)
                            <tr>
                                <td>{{$orden->id}}</td>
                                <td><a href="{{route('mostrarOrden',[$orden->id])}}">{{$orden->customer_name}}</a></td>
                                <td>{{$orden->customer_email}}</td>
                                <td>{{$orden->customer_mobile}}</td>
                                <td>{{$orden->status}}</td>
                                <td>{{App\Lib\Helpers::dateFormat($orden->updated_at)}}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay registros</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection


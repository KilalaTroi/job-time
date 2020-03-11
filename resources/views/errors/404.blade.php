@extends('layouts.app')

@section('content')
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h1>404 Not Found</h1>
                    </div>

                    <div class="card-body">
                        <p>Oops! It seems that this page does not exist.</p>
                        <a href="/dashboard" class="btn btn-primary">Return dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

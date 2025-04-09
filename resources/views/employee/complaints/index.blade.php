@extends('layouts.app')
@section('page-header')
    <div class="row align-items-center bg-white py-2">
        <div class="col">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Employee Voice</li>
            </ul>
        </div>
    </div>
@endsection
@section('content')

    <employee-complaints 
    :user='@json($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
    :roles='@json($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
    :permissions='@json($permissions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
     />



@endsection

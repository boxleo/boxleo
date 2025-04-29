@extends('layouts.app')

@section('styles')
@endsection

@section('page-header')
    <div class="row">
        <div class="col-sm-12">
            <h3 class="page-title">User Account</h3> 
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">My Account</li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
{{-- 
<employee-account 

:user="{{ json_encode($user) }}" 
        :roles="{{ json_encode($roles) }}" 
        :permissions="{{ json_encode($permissions) }}" 
/> --}}




<employee-account 

    :user='@json($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
    :roles='@json($roles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)'
    :permissions='@json($permissions, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT)' 
/>
@endsection



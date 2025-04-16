@extends('layouts.app')

@section('content')
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('home') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">
                Applicants
            </li>
        </ol>
    </nav>
    <hr />
    <!-- Offices Component -->
    <applicant-list />
@endsection

{{-- resources/views/components/layout.blade.php --}}
@props([
'title' => null,
'bodyClass' => '',
'useAdminHeader' => null,
'mainClass' => null,
'metaDescription' => null,
'ogImage' => null,
])

@extends('layouts.app', [
'title' => $title,
'bodyClass' => $bodyClass,
'useAdminHeader' => $useAdminHeader,
'mainClass' => $mainClass,
'metaDescription' => $metaDescription,
'ogImage' => $ogImage,
])

@section('content')
{{ $slot }}
@endsection
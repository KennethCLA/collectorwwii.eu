{{-- resources/views/components/layout.blade.php --}}
@props([
'title' => null,
'bodyClass' => '',
'useAdminHeader' => null,
'mainClass' => null,
'metaDescription' => null,
'ogImage' => null,
'canonical' => null,
'robots' => null,
'structuredData' => null,
])

@extends('layouts.app', [
'title' => $title,
'bodyClass' => $bodyClass,
'useAdminHeader' => $useAdminHeader,
'mainClass' => $mainClass,
'metaDescription' => $metaDescription,
'ogImage' => $ogImage,
'canonical' => $canonical,
'robots' => $robots,
'structuredData' => $structuredData,
])

@section('content')
{{ $slot }}
@endsection

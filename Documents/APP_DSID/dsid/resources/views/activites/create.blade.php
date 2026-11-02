@extends('layouts.app')
@section('title', 'Nouvelle activité')
@section('content')<div class="page-head"><div><div class="eyebrow">Planification</div><h1>Nouvelle activité</h1><div class="subhead">Définissez le périmètre, le responsable et les premières échéances.</div></div></div><form class="panel" method="POST" action="{{ route('activites.store') }}">@include('activites._form')</form>@endsection

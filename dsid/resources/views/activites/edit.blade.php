@extends('layouts.app')
@section('title', 'Modifier une activité')
@section('content')<div class="page-head"><div><div class="eyebrow">Mise à jour</div><h1>Modifier l’activité</h1><div class="subhead">{{ $activite->title }}</div></div></div><form class="panel" method="POST" action="{{ route('activites.update',$activite) }}">@method('PUT')@include('activites._form')</form>@endsection

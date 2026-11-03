@extends('layouts.guest')
@section('title', 'Connexion')
@section('content')
<span class="text-uppercase fw-bold small" style="color:#2ba58f;letter-spacing:.12em">Bienvenue</span>
<h2 class="mt-2">Connectez-vous</h2><p class="text-secondary mb-4">Accédez à votre espace de suivi des activités.</p>
<form method="POST" action="{{ route('login.store') }}">@csrf
    <div class="mb-3"><label class="form-label" for="email">Adresse e-mail</label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
    <div class="mb-3"><label class="form-label" for="password">Mot de passe</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
    <div class="d-flex align-items-center justify-content-between mb-4"><label class="small"><input class="form-check-input me-1" type="checkbox" name="remember"> Se souvenir de moi</label><a class="link small" href="{{ route('password.request') }}">Mot de passe oublié ?</a></div>
    <button class="btn btn-primary w-100">Ouvrir mon espace <span class="ms-1">→</span></button>
</form>
@endsection

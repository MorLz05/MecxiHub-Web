<?php

test('an unauthenticated user must sign in before loading assistant context', function () {
    $this->postJson(route('asistente.context'), [])
        ->assertUnauthorized();
});

test('a guest is sent to login before opening the assistant', function () {
    $this->withoutVite()
        ->get(route('asistente.ia'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('ai_pending_assistant', true)
        ->assertSessionHas('ai_access_notice', 'Debes iniciar sesión para acceder a la IA.');
});

test('a guest sees a temporary notice when submitting a question to the assistant', function () {
    $this->withoutVite()
        ->post(route('asistente.intent'), ['message' => '¿Por qué hace ruido mi auto?'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('ai_pending_message', '¿Por qué hace ruido mi auto?')
        ->assertSessionHas('ai_access_notice', 'Debes iniciar sesión para acceder a la IA.');

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Debes iniciar sesión para acceder a la IA.');
});

test('a guest opening the workshop assistant stores intent for post-login routing', function () {
    $this->get(route('taller.asistente'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('ai_pending_assistant', true);
});

test('the home page provides category prompts through the assistant intent endpoint', function () {
    $this->withoutVite()
        ->get(route('home'))
        ->assertOk()
        ->assertSee('action="' . route('asistente.intent') . '"', false)
        ->assertSee('data-problem-category="motor"', false)
        ->assertSee('data-problem-category="frenos"', false)
        ->assertSee('data-problem-category="eléctrico"', false)
        ->assertSee('data-problem-category="suspensión"', false)
        ->assertDontSee('fa-microphone');
});

test('an assistant intent is preserved while a guest signs in', function () {
    $this->postJson(route('asistente.intent'), ['message' => '¿Por qué hace ruido mi auto?'])
        ->assertRedirect(route('login'))
        ->assertSessionHas('ai_pending_message', '¿Por qué hace ruido mi auto?');
});

test('an authenticated driver is sent to the assistant with their question queued', function () {
    $this->withSession([
        'firebase_user' => ['uid' => 'driver-user', 'rol' => 'Conductor'],
    ])->post(route('asistente.intent'), ['message' => 'Mi carro hace ruidos'])
        ->assertRedirect(route('asistente.ia'))
        ->assertSessionHas('ai_pending_message', 'Mi carro hace ruidos');
});

test('the login page no longer has a direct guest link to the AI chat', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Inicia sesión para resolver tus dudas con MecxiBot')
        ->assertDontSee('Hablar con IA');
});

test('a workshop user is redirected to the workshop assistant', function () {
    $this->withSession([
        'firebase_user' => ['uid' => 'workshop-user', 'rol' => 'Administrador'],
    ])->get(route('asistente.ia'))
        ->assertRedirect(route('taller.asistente'));
});

test('an authenticated workshop user can open the responsive assistant view', function () {
    $this->withoutVite()
        ->withSession([
            'firebase_user' => ['uid' => 'workshop-user', 'rol' => 'Administrador'],
        ])
        ->get(route('taller.asistente'))
        ->assertOk()
        ->assertSee('Chat de asistencia mecánica');
});

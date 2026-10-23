<?php

namespace App\Support;

use Illuminate\Support\Str;

class AiScopeGuard
{
    public const WORKSHOP_REPLY = 'Solo puedo ayudar con mecánica automotriz y gestión de órdenes del taller.';
    public const DRIVER_REPLY = 'Solo puedo ayudar con dudas sobre la mecánica de tu vehiculo.';

    // Se evalúa sobre texto en minúsculas y sin acentos.
    private const BLOCKED_PATTERNS = [
        // Intentos de anular o revelar instrucciones o cambiar el rol.
        '/\b(ignora|ignore|olvida|omite|desobedece|forget|disregard)\b.{0,40}\b(instrucciones|reglas|indicaciones|restricciones|instructions|rules|prompt)\b/u',
        '/\b(system prompt|prompt (del|de) sistema|instrucciones (internas|del sistema|ocultas)|tus instrucciones|your instructions)\b/u',
        '/\b(actua como|ahora eres|a partir de ahora eres|finge ser|pretend to be|you are now|modo desarrollador|developer mode|jailbreak|dan)\b/u',
        // Aplicación, infraestructura, código y seguridad informática.
        '/\b(firebase|firestore|laravel|php|javascript|html|css|api key|apikey|endpoint|backend|frontend|github|app ?check)\b/u',
        '/\b(base de datos|database|sql|nosql|credenciales|contrasenas?|passwords?|token de acceso)\b/u',
        '/\b(codigo fuente|source code|scripts?|programar|lenguaje de programacion|escribe (un )?(codigo|programa|funcion))\b/u',
        '/\b(hackear|hackeo|hacker|exploit|xss|csrf|inyeccion|injection|drop table|union select|ciberseguridad|seguridad informatica)\b/u',
        '/<\s*\/?\s*(script|iframe|img|svg|object)\b|javascript:|\bselect\b.{1,60}\bfrom\b|\b(insert into|delete from)\b/u',
        '/\b(como (funciona|se administra|esta hecha|se programo|se desarrollo)|quien (programo|desarrollo|administra)).{0,40}\b(app|aplicacion|plataforma|pagina|sitio|sistema|web|mecxi ?hub|mecxi ?bot)\b/u',
        // Tareas ajenas al taller.
        '/\b(escribe|redacta|genera|inventa|dime)\b.{0,25}\b(poema|cancion|cuento|ensayo|chiste|receta|horoscopo)\b/u',
        '/\b(traduce|traducir)\b.{0,40}\b(al|a) (ingles|frances|aleman|chino)\b/u',
        '/\b(criptomonedas?|bitcoin|horoscopo|resultado del partido|quien gano)\b/u',
        '/\b(busca|buscar|investiga|consulta)\b.{0,25}\b(en internet|en google|en la web|online)\b/u',
    ];

    public static function replyFor(string $role): string
    {
        return $role === 'Administrador' ? self::WORKSHOP_REPLY : self::DRIVER_REPLY;
    }

    public static function isOutOfScope(string $message): bool
    {
        $normalized = Str::of(Str::ascii($message))->lower()->squish()->value();
        if ($normalized === '') {
            return false;
        }

        foreach (self::BLOCKED_PATTERNS as $pattern) {
            if (preg_match($pattern, $normalized) === 1) {
                return true;
            }
        }

        return false;
    }
}

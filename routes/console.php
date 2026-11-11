<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Fase 8.2 (cms_v.md) — publica automaticamente, a cada minuto, as notícias
// agendadas cuja hora de publicação já chegou.
//
// PRÉ-REQUISITO no Windows (XAMPP): Agendador de Tarefas do Windows a correr
//   php artisan schedule:run
// a cada minuto (regra "Tarefa: Agendador" -> ação: php artisan schedule:run,
// ou instalar o cron equivalente em Linux/cron). Sem isto, o comando fica
// definido mas não corre sozinho — correr manualmente com
//   php artisan news:publish-scheduled
Schedule::command('news:publish-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->name('news-publish-scheduled');

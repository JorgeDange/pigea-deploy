<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * Lista deliberadamente curta: `role`/`is_admin`/`two_factor_*` NUNCA são
     * mass-assignable — atribuídos apenas via roles do Spatie e ações do Fortify.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        // Campos do "Editar perfil" do painel (painel-utilizador-perfil.html).
        'phone',
        'province',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /** Candidaturas do utilizador (`painel-utilizador-candidaturas`). */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    /** Vagas marcadas como guardadas (`painel-utilizador-guardadas`). */
    public function savedJobs()
    {
        return $this->hasMany(SavedJob::class);
    }

    /** Notificações do painel (`painel-utilizador-notificações`). */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /** Ficha privada da empresa (se role company). */
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class);
    }

    /** Oportunidades publicadas pela empresa. */
    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }
}

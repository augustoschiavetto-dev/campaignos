<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Relacionamento extends Model
{
    protected $table = 'relacionamentos';

    protected $fillable = [
        'tipo_pessoa',
        'nome',
        'apelido',
        'cpf_cnpj',
        'email',
        'telefone',
        'telefone_normalizado',
        'data_nascimento',
        'genero',
        'profissao',
        'endereco',
        'bairro_id',
        'responsavel_id',
        'status',
        'data_proxima_acao',
        'descricao_proxima_acao',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_proxima_acao' => 'date',
    ];

    /**
     * Mutator para normalizar o telefone automaticamente ao salvar.
     */
    protected function telefone(): Attribute
    {
        return Attribute::make(
            set: function (?string $value) {
                if (empty($value)) {
                    return [
                        'telefone' => null,
                        'telefone_normalizado' => null
                    ];
                }
                return [
                    'telefone' => $value,
                    'telefone_normalizado' => preg_replace('/\D/', '', $value)
                ];
            }
        );
    }

    public function bairro(): BelongsTo
    {
        return $this->belongsTo(Bairro::class, 'bairro_id');
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'relacionamento_tag', 'relacionamento_id', 'tag_id');
    }

    public function tipos(): BelongsToMany
    {
        return $this->belongsToMany(TipoRelacionamento::class, 'relacionamento_tipo', 'relacionamento_id', 'tipo_relacionamento_id');
    }

    public function interacoes(): HasMany
    {
        return $this->hasMany(Interacao::class, 'relacionamento_id');
    }

    public function liderancaDetalhe(): HasOne
    {
        return $this->hasOne(LiderancaDetalhe::class, 'relacionamento_id');
    }

    // Helpers de segmentação dinâmicos
    public function getIsApoiadorAttribute(): bool
    {
        return $this->tipos->contains('nome', 'apoiador');
    }

    public function getIsLiderancaAttribute(): bool
    {
        return $this->tipos->contains('nome', 'lideranca');
    }

    public function getIsVoluntarioAttribute(): bool
    {
        return $this->tipos->contains('nome', 'voluntario');
    }

    public function getIsEquipeAttribute(): bool
    {
        return $this->tipos->contains('nome', 'equipe');
    }
}

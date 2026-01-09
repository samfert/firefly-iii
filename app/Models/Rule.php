<?php

/**
 * Rule.php
 * Copyright (c) 2019 james@firefly-iii.org
 *
 * This file is part of Firefly III (https://github.com/firefly-iii).
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Models;

use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use FireflyIII\Support\Models\ReturnsIntegerUserIdTrait;
use FireflyIII\User;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class Rule
 *
 * Representa uma regra de automacao no sistema Firefly III.
 * Regras permitem automatizar acoes em transacoes com base em
 * gatilhos (triggers) e acoes definidas pelo usuario.
 *
 * @property int                                 $id             Identificador unico da regra
 * @property int                                 $user_id        ID do usuario proprietario
 * @property int                                 $user_group_id  ID do grupo de usuarios
 * @property int                                 $rule_group_id  ID do grupo de regras
 * @property string                              $title          Titulo da regra
 * @property string|null                         $description    Descricao da regra
 * @property int                                 $order          Ordem de execucao
 * @property bool                                $active         Se a regra esta ativa
 * @property bool                                $strict         Se todos os gatilhos devem corresponder
 * @property bool                                $stop_processing Se deve parar o processamento apos esta regra
 * @property \Carbon\Carbon                      $created_at     Data de criacao
 * @property \Carbon\Carbon                      $updated_at     Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at     Data de exclusao (soft delete)
 * @property-read User                           $user           Usuario proprietario
 * @property-read RuleGroup                      $ruleGroup      Grupo de regras
 * @property-read UserGroup                      $userGroup      Grupo de usuarios
 * @property-read \Illuminate\Support\Collection $ruleActions    Acoes da regra
 * @property-read \Illuminate\Support\Collection $ruleTriggers   Gatilhos da regra
 */
class Rule extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['rule_group_id', 'order', 'active', 'title', 'description', 'user_id', 'user_group_id', 'strict'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $ruleId = (int) $value;

            /** @var User $user */
            $user   = auth()->user();

            /** @var null|Rule $rule */
            $rule   = $user->rules()->find($ruleId);
            if (null !== $rule) {
                return $rule;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario desta regra.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todas as acoes desta regra.
     *
     * @return HasMany Colecao de RuleAction relacionadas
     */
    public function ruleActions(): HasMany
    {
        return $this->hasMany(RuleAction::class);
    }

    /**
     * Retorna o grupo de regras ao qual esta regra pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo RuleGroup
     */
    public function ruleGroup(): BelongsTo
    {
        return $this->belongsTo(RuleGroup::class);
    }

    /**
     * Retorna todos os gatilhos desta regra.
     *
     * @return HasMany Colecao de RuleTrigger relacionados
     */
    public function ruleTriggers(): HasMany
    {
        return $this->hasMany(RuleTrigger::class);
    }

    /**
     * Mutator para escapar HTML na descricao da regra.
     *
     * @return Attribute Atributo computado para a descricao
     */
    protected function description(): Attribute
    {
        return Attribute::make(set: fn ($value) => ['description' => e($value)]);
    }

    /**
     * Retorna o grupo de usuarios ao qual esta regra pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserGroup
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Accessor para garantir que a ordem seja retornada como inteiro.
     *
     * @return Attribute Atributo computado para a ordem de execucao
     */
    protected function order(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do grupo de regras seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do grupo de regras
     */
    protected function ruleGroupId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'created_at'      => 'datetime',
            'updated_at'      => 'datetime',
            'deleted_at'      => 'datetime',
            'active'          => 'boolean',
            'order'           => 'int',
            'stop_processing' => 'boolean',
            'id'              => 'int',
            'strict'          => 'boolean',
            'user_id'         => 'integer',
            'user_group_id'   => 'integer',
        ];
    }
}

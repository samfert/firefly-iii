<?php

/**
 * RuleGroup.php
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
 * Class RuleGroup
 *
 * Representa um grupo de regras no sistema Firefly III.
 * Grupos de regras permitem organizar regras relacionadas e
 * controlar a ordem de execucao das regras.
 *
 * @property int                                 $id              Identificador unico do grupo
 * @property int                                 $user_id         ID do usuario proprietario
 * @property int                                 $user_group_id   ID do grupo de usuarios
 * @property string                              $title           Titulo do grupo de regras
 * @property string|null                         $description     Descricao do grupo
 * @property int                                 $order           Ordem de execucao
 * @property bool                                $active          Se o grupo esta ativo
 * @property bool                                $stop_processing Se deve parar o processamento apos este grupo
 * @property \Carbon\Carbon                      $created_at      Data de criacao
 * @property \Carbon\Carbon                      $updated_at      Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at      Data de exclusao (soft delete)
 * @property-read User                           $user            Usuario proprietario
 * @property-read \Illuminate\Support\Collection $rules           Regras neste grupo
 */
class RuleGroup extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    use SoftDeletes;

    protected $fillable = ['user_id', 'user_group_id', 'stop_processing', 'order', 'title', 'description', 'active'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $ruleGroupId = (int) $value;

            /** @var User $user */
            $user        = auth()->user();

            /** @var null|RuleGroup $ruleGroup */
            $ruleGroup   = $user->ruleGroups()->find($ruleGroupId);
            if (null !== $ruleGroup) {
                return $ruleGroup;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste grupo de regras.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todas as regras neste grupo.
     *
     * @return HasMany Colecao de Rule relacionadas
     */
    public function rules(): HasMany
    {
        return $this->hasMany(Rule::class);
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
            'stop_processing' => 'boolean',
            'order'           => 'int',
            'user_id'         => 'integer',
            'user_group_id'   => 'integer',
        ];
    }
}

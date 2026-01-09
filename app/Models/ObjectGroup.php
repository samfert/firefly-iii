<?php

/**
 * ObjectGroup.php
 * Copyright (c) 2020 james@firefly-iii.org
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
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ObjectGroup
 *
 * Representa um grupo de objetos para organizar entidades relacionadas.
 * Permite agrupar contas, faturas e cofrinhos em categorias logicas
 * para melhor organizacao e visualizacao.
 *
 * @property int                                 $id            Identificador unico do grupo
 * @property int                                 $user_id       ID do usuario proprietario
 * @property int                                 $user_group_id ID do grupo de usuarios
 * @property string                              $title         Titulo do grupo
 * @property int                                 $order         Ordem de exibicao
 * @property \Carbon\Carbon                      $created_at    Data de criacao
 * @property \Carbon\Carbon                      $updated_at    Data de atualizacao
 * @property \Carbon\Carbon|null                 $deleted_at    Data de exclusao (soft delete)
 * @property-read User                           $user          Usuario proprietario
 * @property-read \Illuminate\Support\Collection $accounts      Contas neste grupo
 * @property-read \Illuminate\Support\Collection $bills         Faturas neste grupo
 * @property-read \Illuminate\Support\Collection $piggyBanks    Cofrinhos neste grupo
 */
class ObjectGroup extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;
    protected $fillable = ['title', 'order', 'user_id', 'user_group_id'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $objectGroupId = (int) $value;

            /** @var null|ObjectGroup $objectGroup */
            $objectGroup   = self::where('object_groups.id', $objectGroupId)
                ->where('object_groups.user_id', auth()->user()->id)->first()
            ;
            if (null !== $objectGroup) {
                return $objectGroup;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o usuario proprietario deste grupo de objetos.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna todas as contas associadas a este grupo.
     *
     * @return MorphToMany Colecao polimorfica de Account
     */
    public function accounts(): MorphToMany
    {
        return $this->morphedByMany(Account::class, 'object_groupable');
    }

    /**
     * Retorna todas as faturas associadas a este grupo.
     *
     * @return MorphToMany Colecao polimorfica de Bill
     */
    public function bills(): MorphToMany
    {
        return $this->morphedByMany(Bill::class, 'object_groupable');
    }

    /**
     * Retorna todos os cofrinhos associados a este grupo.
     *
     * @return MorphToMany Colecao polimorfica de PiggyBank
     */
    public function piggyBanks(): MorphToMany
    {
        return $this->morphedByMany(PiggyBank::class, 'object_groupable');
    }

    /**
     * Accessor para garantir que a ordem seja retornada como inteiro.
     *
     * @return Attribute Atributo computado para a ordem de exibicao
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
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
            'user_id'       => 'integer',
            'user_group_id' => 'integer',
            'deleted_at'    => 'datetime',
        ];
    }
}

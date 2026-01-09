<?php

/*
 * GroupMembership.php
 * Copyright (c) 2021 james@firefly-iii.org
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

/**
 * Class GroupMembership
 *
 * Representa a associacao entre um usuario e um grupo de usuarios.
 * Define qual papel (role) o usuario tem dentro de um grupo especifico,
 * permitindo controle de acesso granular.
 *
 * @property int            $id            Identificador unico da associacao
 * @property int            $user_id       ID do usuario
 * @property int            $user_group_id ID do grupo de usuarios
 * @property int            $user_role_id  ID do papel do usuario no grupo
 * @property \Carbon\Carbon $created_at    Data de criacao
 * @property \Carbon\Carbon $updated_at    Data de atualizacao
 * @property-read User      $user          Usuario associado
 * @property-read UserGroup $userGroup     Grupo de usuarios
 * @property-read UserRole  $userRole      Papel do usuario no grupo
 */
class GroupMembership extends Model
{
    use ReturnsIntegerIdTrait;
    use ReturnsIntegerUserIdTrait;

    protected $fillable = ['user_id', 'user_group_id', 'user_role_id'];

    /**
     * Retorna o usuario associado a esta membresia.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Retorna o grupo de usuarios ao qual esta membresia pertence.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserGroup
     */
    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    /**
     * Retorna o papel do usuario neste grupo.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo UserRole
     */
    public function userRole(): BelongsTo
    {
        return $this->belongsTo(UserRole::class);
    }

    /**
     * Accessor para garantir que o ID do papel seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do papel
     */
    protected function userRoleId(): Attribute
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
        ];
    }
}

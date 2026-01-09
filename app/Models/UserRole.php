<?php

/*
 * UserRole.php
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
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class UserRole
 *
 * Representa um papel de usuario dentro de um grupo no sistema Firefly III.
 * Papeis definem permissoes e niveis de acesso dentro de grupos de usuarios,
 * como proprietario, membro completo, somente leitura, etc.
 *
 * @property int                                 $id               Identificador unico do papel
 * @property string                              $title            Titulo do papel
 * @property \Carbon\Carbon                      $created_at       Data de criacao
 * @property \Carbon\Carbon                      $updated_at       Data de atualizacao
 * @property-read \Illuminate\Support\Collection $groupMemberships Membros com este papel
 */
class UserRole extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['title'];

    /**
     * Retorna todas as associacoes de membros com este papel.
     *
     * @return HasMany Colecao de GroupMembership relacionadas
     */
    public function groupMemberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class);
    }
}

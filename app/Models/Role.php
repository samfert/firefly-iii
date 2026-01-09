<?php

/**
 * Role.php
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
use FireflyIII\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Role
 *
 * Representa um papel (role) de usuario no sistema Firefly III.
 * Papeis definem permissoes e niveis de acesso, como administrador,
 * usuario padrao, etc.
 *
 * @property int                                 $id           Identificador unico do papel
 * @property string                              $name         Nome do papel
 * @property string|null                         $display_name Nome de exibicao do papel
 * @property string|null                         $description  Descricao do papel
 * @property \Carbon\Carbon                      $created_at   Data de criacao
 * @property \Carbon\Carbon                      $updated_at   Data de atualizacao
 * @property-read \Illuminate\Support\Collection $users        Usuarios com este papel
 */
class Role extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['name', 'display_name', 'description'];

    /**
     * Retorna todos os usuarios que possuem este papel.
     *
     * @return BelongsToMany Colecao de User relacionados
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

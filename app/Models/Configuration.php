<?php

/**
 * Configuration.php
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

use Illuminate\Database\Eloquent\Casts\Attribute;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use function Safe\json_decode;
use function Safe\json_encode;

/**
 * Class Configuration
 *
 * Armazena configuracoes globais do sistema Firefly III.
 * Permite armazenar pares chave-valor para configuracoes que
 * afetam o comportamento geral da aplicacao.
 *
 * @property int             $id         Identificador unico da configuracao
 * @property string          $name       Nome/chave da configuracao
 * @property mixed           $data       Dados da configuracao (armazenados como JSON)
 * @property \Carbon\Carbon  $created_at Data de criacao
 * @property \Carbon\Carbon  $updated_at Data de atualizacao
 * @property \Carbon\Carbon|null $deleted_at Data de exclusao (soft delete)
 */
class Configuration extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $table = 'configuration';

    /**
     * Accessor e mutator para o campo data.
     * Converte automaticamente entre JSON e objetos PHP.
     *
     * @return Attribute Atributo computado para os dados da configuracao
     */
    protected function data(): Attribute
    {
        return Attribute::make(get: fn ($value) => json_decode((string) $value), set: fn ($value) => ['data' => json_encode($value)]);
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
            'deleted_at' => 'datetime',
        ];
    }
}

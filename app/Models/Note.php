<?php

/**
 * Note.php
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
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Note
 *
 * Representa uma nota de texto associada a entidades do sistema.
 * Notas podem ser anexadas a transacoes, contas, orcamentos e outras
 * entidades para adicionar informacoes contextuais.
 *
 * @property int                 $id            Identificador unico da nota
 * @property int                 $noteable_id   ID da entidade associada
 * @property string              $noteable_type Tipo da entidade associada
 * @property string|null         $title         Titulo da nota
 * @property string|null         $text          Conteudo da nota
 * @property \Carbon\Carbon      $created_at    Data de criacao
 * @property \Carbon\Carbon      $updated_at    Data de atualizacao
 * @property \Carbon\Carbon|null $deleted_at    Data de exclusao (soft delete)
 * @property-read Model          $noteable      Entidade associada
 */
class Note extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['title', 'text', 'noteable_id', 'noteable_type'];

    /**
     * Retorna a entidade proprietaria desta nota.
     *
     * @return MorphTo Relacionamento polimorfico com a entidade proprietaria
     */
    public function noteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor para garantir que o ID da entidade seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da entidade
     */
    protected function noteableId(): Attribute
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
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

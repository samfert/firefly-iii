<?php

/*
 * AuditLogEntry.php
 * Copyright (c) 2022 james@firefly-iii.org
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
 * Class AuditLogEntry
 *
 * Registra alteracoes feitas em entidades do sistema para fins de auditoria.
 * Armazena o estado anterior e posterior de cada modificacao, permitindo
 * rastrear quem fez a alteracao e quando.
 *
 * @property int                 $id            Identificador unico do registro de auditoria
 * @property int                 $auditable_id  ID da entidade auditada
 * @property string              $auditable_type Tipo da entidade auditada
 * @property int                 $changer_id    ID de quem fez a alteracao
 * @property string              $changer_type  Tipo de quem fez a alteracao
 * @property string              $action        Acao realizada (create, update, delete)
 * @property array               $before        Estado anterior da entidade
 * @property array               $after         Estado posterior da entidade
 * @property \Carbon\Carbon      $created_at    Data de criacao
 * @property \Carbon\Carbon      $updated_at    Data de atualizacao
 * @property \Carbon\Carbon|null $deleted_at    Data de exclusao (soft delete)
 * @property-read Model          $auditable     Entidade auditada
 * @property-read Model          $changer       Quem fez a alteracao
 */
class AuditLogEntry extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    /**
     * Retorna a entidade que foi auditada.
     *
     * @return MorphTo Relacionamento polimorfico com a entidade auditada
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Retorna quem realizou a alteracao (usuario ou sistema).
     *
     * @return MorphTo Relacionamento polimorfico com o autor da alteracao
     */
    public function changer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Accessor para garantir que o ID da entidade auditada seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da entidade auditada
     */
    protected function auditableId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do autor da alteracao seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do autor
     */
    protected function changerId(): Attribute
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
            'before'     => 'array',
            'after'      => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }
}

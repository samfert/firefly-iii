<?php

/**
 * RecurrenceTransactionMeta.php
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
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecurrenceTransactionMeta
 *
 * Armazena metadados adicionais para transacoes de recorrencias.
 * Permite armazenar informacoes extras como categorias, orcamentos,
 * tags e outras configuracoes especificas da transacao recorrente.
 *
 * @property int                      $id         Identificador unico do metadado
 * @property int                      $rt_id      ID da transacao recorrente associada
 * @property string                   $name       Nome/chave do metadado
 * @property string                   $value      Valor do metadado
 * @property \Carbon\Carbon           $created_at Data de criacao
 * @property \Carbon\Carbon           $updated_at Data de atualizacao
 * @property \Carbon\Carbon|null      $deleted_at Data de exclusao (soft delete)
 * @property-read RecurrenceTransaction $recurrenceTransaction Transacao recorrente associada
 */
class RecurrenceTransactionMeta extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    protected $fillable = ['rt_id', 'name', 'value'];

    protected $table    = 'rt_meta';

    /**
     * Retorna a transacao recorrente associada a este metadado.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo RecurrenceTransaction
     */
    public function recurrenceTransaction(): BelongsTo
    {
        return $this->belongsTo(RecurrenceTransaction::class, 'rt_id');
    }

    /**
     * Accessor para garantir que o ID da transacao recorrente seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da transacao recorrente
     */
    protected function rtId(): Attribute
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
            'name'       => 'string',
            'value'      => 'string',
        ];
    }
}

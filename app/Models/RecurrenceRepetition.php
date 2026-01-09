<?php

/**
 * RecurrenceRepetition.php
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

use Deprecated;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class RecurrenceRepetition
 *
 * Define como uma transacao recorrente se repete.
 * Especifica o tipo de repeticao (diaria, semanal, mensal, etc.),
 * o momento da repeticao e como lidar com fins de semana.
 *
 * @property int                 $id                Identificador unico da repeticao
 * @property int                 $recurrence_id     ID da recorrencia associada
 * @property string              $repetition_type   Tipo de repeticao (daily, weekly, monthly, etc.)
 * @property string              $repetition_moment Momento da repeticao (dia da semana, dia do mes, etc.)
 * @property int                 $repetition_skip   Numero de periodos a pular
 * @property int                 $weekend           Como lidar com fins de semana
 * @property \Carbon\Carbon      $created_at        Data de criacao
 * @property \Carbon\Carbon      $updated_at        Data de atualizacao
 * @property \Carbon\Carbon|null $deleted_at        Data de exclusao (soft delete)
 * @property-read Recurrence     $recurrence        Recorrencia associada
 */
class RecurrenceRepetition extends Model
{
    use ReturnsIntegerIdTrait;
    use SoftDeletes;

    #[Deprecated] /** @deprecated */
    public const int WEEKEND_DO_NOTHING    = 1;

    #[Deprecated] /** @deprecated */
    public const int WEEKEND_SKIP_CREATION = 2;

    #[Deprecated] /** @deprecated */
    public const int WEEKEND_TO_FRIDAY     = 3;

    #[Deprecated] /** @deprecated */
    public const int WEEKEND_TO_MONDAY     = 4;

    protected $casts
                                           = [
            'created_at'        => 'datetime',
            'updated_at'        => 'datetime',
            'deleted_at'        => 'datetime',
            'repetition_type'   => 'string',
            'repetition_moment' => 'string',
            'repetition_skip'   => 'int',
            'weekend'           => 'int',
        ];

    protected $fillable                    = ['recurrence_id', 'weekend', 'repetition_type', 'repetition_moment', 'repetition_skip'];

    protected $table                       = 'recurrences_repetitions';

    /**
     * Retorna a recorrencia associada a esta repeticao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Recurrence
     */
    public function recurrence(): BelongsTo
    {
        return $this->belongsTo(Recurrence::class);
    }

    /**
     * Define os casts de atributos do modelo.
     *
     * @return array<string, string> Array de casts de atributos
     */
    protected function casts(): array
    {
        return [
            // 'weekend' => RecurrenceRepetitionWeekend::class,
        ];
    }

    /**
     * Accessor para garantir que o ID da recorrencia seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da recorrencia
     */
    protected function recurrenceId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o numero de periodos a pular seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o numero de periodos a pular
     */
    protected function repetitionSkip(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o tratamento de fim de semana seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o tratamento de fim de semana
     */
    protected function weekend(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }
}

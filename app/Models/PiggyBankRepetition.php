<?php

/**
 * PiggyBankRepetition.php
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

use Illuminate\Database\Eloquent\Attributes\Scope;
use Carbon\Carbon;
use FireflyIII\Casts\SeparateTimezoneCaster;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class PiggyBankRepetition
 *
 * Representa uma repeticao ou ciclo de um cofrinho.
 * Permite rastrear o progresso de economia em diferentes periodos,
 * armazenando o valor atual economizado para cada ciclo.
 *
 * @property int             $id             Identificador unico da repeticao
 * @property int             $piggy_bank_id  ID do cofrinho associado
 * @property \Carbon\Carbon|null $start_date Data de inicio do ciclo
 * @property \Carbon\Carbon|null $target_date Data alvo do ciclo
 * @property string          $current_amount Valor atual economizado
 * @property \Carbon\Carbon  $created_at     Data de criacao
 * @property \Carbon\Carbon  $updated_at     Data de atualizacao
 * @property-read PiggyBank  $piggyBank      Cofrinho associado
 */
class PiggyBankRepetition extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['piggy_bank_id', 'start_date', 'start_date_tz', 'target_date', 'target_date_tz', 'current_amount'];

    /**
     * Retorna o cofrinho associado a esta repeticao.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo PiggyBank
     */
    public function piggyBank(): BelongsTo
    {
        return $this->belongsTo(PiggyBank::class);
    }

    /**
     * Scope para filtrar repeticoes por datas especificas de inicio e fim.
     *
     * @param EloquentBuilder $query  Query builder
     * @param Carbon          $start  Data de inicio
     * @param Carbon          $target Data alvo
     *
     * @return EloquentBuilder Query builder filtrada
     */
    #[Scope]
    protected function onDates(EloquentBuilder $query, Carbon $start, Carbon $target): EloquentBuilder
    {
        return $query->where('start_date', $start->format('Y-m-d'))->where('target_date', $target->format('Y-m-d'));
    }

    /**
     * Scope para filtrar repeticoes relevantes em uma data especifica.
     * Retorna repeticoes onde a data esta entre start_date e target_date.
     *
     * @param EloquentBuilder $query Query builder
     * @param Carbon          $date  Data de referencia
     *
     * @return EloquentBuilder Query builder filtrada
     */
    #[Scope]
    protected function relevantOnDate(EloquentBuilder $query, Carbon $date): EloquentBuilder
    {
        return $query->where(
            static function (EloquentBuilder $q) use ($date): void {
                $q->where('start_date', '<=', $date->format('Y-m-d 00:00:00'));
                $q->orWhereNull('start_date');
            }
        )
            ->where(
                static function (EloquentBuilder $q) use ($date): void {
                    $q->where('target_date', '>=', $date->format('Y-m-d 00:00:00'));
                    $q->orWhereNull('target_date');
                }
            )
        ;
    }

    /**
     * Define o valor atual economizado como string.
     *
     * @param mixed $value Valor a ser definido
     *
     * @return void
     */
    public function setCurrentAmountAttribute($value): void
    {
        $this->attributes['current_amount'] = (string) $value;
    }

    /**
     * Accessor para garantir que o valor atual seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor atual
     */
    protected function currentAmount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do cofrinho seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do cofrinho
     */
    protected function piggyBankId(): Attribute
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
            'start_date'      => SeparateTimezoneCaster::class,
            'target_date'     => SeparateTimezoneCaster::class,
            'virtual_balance' => 'string',
        ];
    }
}

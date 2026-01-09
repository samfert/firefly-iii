<?php

/**
 * BudgetLimit.php
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

use FireflyIII\Casts\SeparateTimezoneCaster;
use FireflyIII\Support\Models\ReturnsIntegerIdTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class BudgetLimit
 *
 * Define um limite de gastos para um orcamento em um periodo especifico.
 * Permite ao usuario definir quanto pode gastar em um orcamento durante
 * um intervalo de datas, com suporte a diferentes moedas.
 *
 * @property int                      $id                      Identificador unico do limite
 * @property int                      $budget_id               ID do orcamento associado
 * @property int                      $transaction_currency_id ID da moeda
 * @property string                   $amount                  Valor limite
 * @property string                   $native_amount           Valor na moeda nativa
 * @property \Carbon\Carbon           $start_date              Data de inicio do periodo
 * @property \Carbon\Carbon           $end_date                Data de fim do periodo
 * @property \Carbon\Carbon           $created_at              Data de criacao
 * @property \Carbon\Carbon           $updated_at              Data de atualizacao
 * @property-read Budget              $budget                  Orcamento associado
 * @property-read TransactionCurrency $transactionCurrency     Moeda do limite
 * @property-read \Illuminate\Support\Collection $notes        Notas associadas
 */
class BudgetLimit extends Model
{
    use ReturnsIntegerIdTrait;

    protected $fillable = ['budget_id', 'start_date', 'end_date', 'start_date_tz', 'end_date_tz', 'amount', 'transaction_currency_id', 'native_amount'];

    /**
     * Route binder. Converts the key in the URL to the specified object (or throw 404).
     *
     * @throws NotFoundHttpException
     */
    public static function routeBinder(string $value): self
    {
        if (auth()->check()) {
            $budgetLimitId = (int) $value;
            $budgetLimit   = self::where('budget_limits.id', $budgetLimitId)
                ->leftJoin('budgets', 'budgets.id', '=', 'budget_limits.budget_id')
                ->where('budgets.user_id', auth()->user()->id)
                ->first(['budget_limits.*'])
            ;
            if (null !== $budgetLimit) {
                return $budgetLimit;
            }
        }

        throw new NotFoundHttpException();
    }

    /**
     * Retorna o orcamento associado a este limite.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo Budget
     */
    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    /**
     * Retorna todas as notas associadas a este limite de orcamento.
     *
     * @return MorphMany Colecao polimorfica de Note
     */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'noteable');
    }

    /**
     * Retorna a moeda associada a este limite de orcamento.
     *
     * @return BelongsTo Relacionamento BelongsTo com o modelo TransactionCurrency
     */
    public function transactionCurrency(): BelongsTo
    {
        return $this->belongsTo(TransactionCurrency::class);
    }

    /**
     * Accessor para garantir que o valor limite seja retornado como string.
     *
     * @return Attribute Atributo computado para o valor limite
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (string) $value,
        );
    }

    /**
     * Accessor para garantir que o ID do orcamento seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID do orcamento
     */
    protected function budgetId(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => (int) $value,
        );
    }

    /**
     * Accessor para garantir que o ID da moeda seja retornado como inteiro.
     *
     * @return Attribute Atributo computado para o ID da moeda
     */
    protected function transactionCurrencyId(): Attribute
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
            'start_date'    => SeparateTimezoneCaster::class,
            'end_date'      => SeparateTimezoneCaster::class,
            'auto_budget'   => 'boolean',
            'amount'        => 'string',
            'native_amount' => 'string',
        ];
    }
}
